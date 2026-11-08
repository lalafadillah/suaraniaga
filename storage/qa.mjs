import { spawn } from 'node:child_process';
import { setTimeout as sleep } from 'node:timers/promises';
import fs from 'node:fs';

const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const PORT = 9333;
const BASE = 'http://127.0.0.1:8123';
const PROFILE = 'C:/laragon/www/hekton/new/suaraniaga/storage/chrome-qa';

const chrome = spawn(CHROME, [
    '--headless=new',
    `--remote-debugging-port=${PORT}`,
    `--user-data-dir=${PROFILE}`,
    '--no-first-run',
    '--no-default-browser-check',
    '--disable-gpu',
    '--window-size=1280,900',
], { stdio: 'ignore' });

async function waitForDevtools() {
    for (let i = 0; i < 60; i++) {
        try {
            const res = await fetch(`http://127.0.0.1:${PORT}/json/version`);
            if (res.ok) return await res.json();
        } catch {}
        await sleep(300);
    }
    throw new Error('devtools tidak siap');
}

class CDP {
    constructor(ws) {
        this.ws = ws;
        this.id = 0;
        this.pending = new Map();
        this.listeners = [];
        ws.addEventListener('message', (ev) => {
            const msg = JSON.parse(ev.data);
            if (msg.id && this.pending.has(msg.id)) {
                const { resolve, reject } = this.pending.get(msg.id);
                this.pending.delete(msg.id);
                msg.error ? reject(new Error(JSON.stringify(msg.error))) : resolve(msg.result);
            } else if (msg.method) {
                this.listeners.forEach((l) => l(msg));
            }
        });
    }

    static async connect(url) {
        const ws = new WebSocket(url);
        await new Promise((res, rej) => {
            ws.addEventListener('open', res, { once: true });
            ws.addEventListener('error', rej, { once: true });
        });
        return new CDP(ws);
    }

    send(method, params = {}, sessionId) {
        const id = ++this.id;
        const payload = { id, method, params };
        if (sessionId) payload.sessionId = sessionId;
        this.ws.send(JSON.stringify(payload));
        return new Promise((resolve, reject) => this.pending.set(id, { resolve, reject }));
    }
}

const browser = await CDP.connect((await waitForDevtools()).webSocketDebuggerUrl);

async function newPage() {
    const { browserContextId } = await browser.send('Target.createBrowserContext', {});
    const { targetId } = await browser.send('Target.createTarget', { url: 'about:blank', browserContextId });
    const { sessionId } = await browser.send('Target.attachToTarget', { targetId, flatten: true });

    const page = {
        sessionId,
        browserContextId,
        targetId,
        errors: [],
        async goto(url) {
            const done = new Promise((res) => {
                const l = (msg) => {
                    if (msg.sessionId === sessionId && msg.method === 'Page.loadEventFired') {
                        browser.listeners.splice(browser.listeners.indexOf(l), 1);
                        res();
                    }
                };
                browser.listeners.push(l);
            });
            await browser.send('Page.enable', {}, sessionId);
            try {
                await browser.send('Page.navigate', { url }, sessionId);
            } catch (e) {
                throw new Error('navigasi gagal untuk ' + JSON.stringify(url) + ': ' + e.message);
            }
            await done;
            await sleep(250);
        },
        async evaluate(expression) {
            const res = await browser.send('Runtime.evaluate', {
                expression,
                awaitPromise: true,
                returnByValue: true,
            }, sessionId);
            if (res.exceptionDetails) {
                throw new Error(res.exceptionDetails.exception?.description ?? 'eval gagal');
            }
            return res.result.value;
        },
    };

    await browser.send('Page.enable', {}, sessionId);
    await browser.send('Runtime.enable', {}, sessionId);
    await browser.send('Log.enable', {}, sessionId);
    browser.listeners.push((msg) => {
        if (msg.sessionId !== sessionId) return;
        if (msg.method === 'Log.entryAdded' && msg.params.entry.level === 'error') {
            page.errors.push(msg.params.entry.text);
        }
        if (msg.method === 'Runtime.exceptionThrown') {
            page.errors.push(msg.params.exceptionDetails.exception?.description ?? 'exception');
        }
    });

    return page;
}

async function login(page, email, password) {
    await page.goto(`${BASE}/masuk`);
    await page.evaluate(`(async () => {
        const token = document.querySelector('input[name="_token"]')?.value;
        const body = new URLSearchParams();
        body.set('_token', token);
        body.set('email', ${JSON.stringify(email)});
        body.set('password', ${JSON.stringify(password)});
        const res = await fetch('/masuk', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/x-www-form-urlencoded' },
            body,
            redirect: 'follow',
        });
        return res.status;
    })()`);
}

async function setMode(page, mode) {
    await page.evaluate(`(async () => {
        const res = await fetch('/pengaturan');
        const html = await res.text();
        const token = (html.match(/name="_token" value="([^"]+)"/) || [])[1];
        await fetch('/mode-aksesibilitas/${mode}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/x-www-form-urlencoded' },
            body: '_token=' + encodeURIComponent(token),
        });
        return true;
    })()`);
}

const AUDIT = `(() => {
    const problems = [];
    const push = (m) => problems.push(m);

    const h1 = document.querySelectorAll('h1');
    if (h1.length !== 1) push('jumlah h1 = ' + h1.length);

    let level = 0;
    for (const h of document.querySelectorAll('h1,h2,h3,h4,h5,h6')) {
        const l = Number(h.tagName[1]);
        if (level && l > level + 1) push('heading h' + level + ' langsung ke h' + l + ': "' + h.textContent.trim().slice(0, 40) + '"');
        level = l;
    }

    const skip = document.querySelector('body > a.skip-link');
    if (!skip) push('skip link tidak ada di anak pertama body');
    else if (!/konten-utama/.test(skip.getAttribute('href') || '')) push('skip link salah tujuan: ' + skip.getAttribute('href'));
    if (!document.getElementById('konten-utama')) push('target #konten-utama tidak ada');
    if (!document.querySelector('main')) push('tidak ada elemen main');

    for (const el of document.querySelectorAll('input:not([type=hidden]), select, textarea')) {
        const id = el.getAttribute('id');
        const label = id ? document.querySelector('label[for="' + CSS.escape(id) + '"]') : null;
        if (!label && !el.getAttribute('aria-label') && !el.getAttribute('aria-labelledby')) {
            push('input tanpa label: ' + (el.getAttribute('name') || el.tagName));
        }
    }

    for (const el of document.querySelectorAll('button, a')) {
        if (el.closest('[hidden]')) continue;
        const nama = (el.getAttribute('aria-label') || el.textContent || '').trim();
        if (!nama) push((el.tagName === 'BUTTON' ? 'tombol' : 'tautan') + ' tanpa nama: ' + (el.getAttribute('href') || el.className).slice(0, 60));
        if (nama.length > 0 && /klik di sini|click here|klik sini/i.test(nama)) push('nama tidak bermakna: ' + nama);
    }

    const tombol = [...document.querySelectorAll('button')].filter((b) => b.type !== 'submit');
    for (const b of tombol) {
        const nama = (b.getAttribute('aria-label') || b.textContent || '').trim();
        if (!nama) push('tombol ikon tanpa aria-label');
    }

    const live = document.querySelectorAll('[aria-live], [role=status], [role=alert]');
    if (live.length === 0) push('tidak ada wilayah live di halaman ini');

    const tautan = [...document.querySelectorAll('a[href]')].map((a) => a.getAttribute('href'));
    const adaRumah = tautan.some((h) => /\\/(penjual)?$/.test(new URL(h, location.href).pathname) || /\\/penjual$/.test(new URL(h, location.href).pathname));

    return {
        problems,
        h1: [...h1].map((h) => h.textContent.trim()),
        tautanRumah: tautan.filter((h) => /^\\//.test(h)),
        adaRumah,
        judul: document.title,
    };
})()`;

const CEK_LINK = `(async (hrefs) => {
    const hasil = [];
    for (const h of hrefs) {
        if (!/^\\//.test(h)) continue;
        const res = await fetch(h, { redirect: 'manual' });
        hasil.push({ href: h, status: res.status });
    }
    return hasil;
})()`;

const hasil = [];

async function auditHalaman(page, label, url) {
    await page.goto(url.startsWith('http') ? url : `${BASE}${url}`);
    const audit = await page.evaluate(AUDIT);
    const cek = await page.evaluate(`(() => {
        const hrefs = [...document.querySelectorAll('a[href]')].map((a) => a.getAttribute('href')).filter((h) => h && h.startsWith('/'));
        return hrefs;
    })()`);
    const statusLink = await page.evaluate(`${CEK_LINK}(${JSON.stringify(cek)})`);
    const rusak = statusLink.filter((r) => r.status >= 400);
    const problems = [...audit.problems];
    if (rusak.length) problems.push('tautan rusak: ' + JSON.stringify(rusak));
    if (page.errors.length) problems.push('error konsol: ' + page.errors.slice(0, 3).join(' | '));

    hasil.push({ label, url, ok: problems.length === 0, problems, judul: audit.judul, h1: audit.h1, tautan: audit.tautanRumah });
    console.log(`\n=== ${label} (${url})`);
    console.log('h1:', JSON.stringify(audit.h1));
    if (problems.length === 0) {
        console.log('  LULUS');
    } else {
        problems.forEach((p) => console.log('  - ' + p));
    }
}

const mode = process.argv[2] ?? 'aksesibilitas';
const peran = process.argv[3] ?? 'pembeli';

const buyer = await newPage();
const seller = await newPage();

await login(buyer, 'buyer@suaraniaga.test', 'password123');
await login(seller, 'seller@suaraniaga.test', 'password123');

const halamanPembeli = [
    ['beranda', '/'],
    ['cari', '/cari'],
    ['daftar produk', '/daftar-produk'],
    ['keranjang', '/keranjang'],
    ['checkout', '/checkout'],
    ['pesanan', '/pesanan'],
    ['favorit', '/favorit'],
    ['profil', '/profil'],
    ['pengaturan', '/pengaturan'],
    ['masuk', '/masuk'],
];

const halamanPenjual = [
    ['beranda penjual', '/penjual'],
    ['produk saya', '/penjual/produk'],
    ['tambah produk', '/penjual/produk/tambah'],
    ['pesanan masuk', '/penjual/pesanan'],
    ['status pesanan', '/penjual/status'],
    ['toko saya', '/penjual/toko'],
    ['profil', '/profil'],
];

const akun = peran === 'penjual' ? seller : buyer;
const daftar = peran === 'penjual' ? halamanPenjual : halamanPembeli;

await setMode(akun, mode);

for (const [label, url] of daftar) {
    await auditHalaman(akun, label, url);
}

// Cek produk detail dan alur tambah produk (langkah pertama)
if (peran === 'pembeli') {
    await auditHalaman(akun, 'detail produk', '/daftar-produk');
} else {
    await auditHalaman(akun, 'tambah produk langkah', '/penjual/produk/tambah');
}

const gagal = hasil.filter((h) => !h.ok);
console.log('\n================ RINGKASAN ================');
console.log(`Total halaman: ${hasil.length}, lulus: ${hasil.length - gagal.length}, bermasalah: ${gagal.length}`);
gagal.forEach((g) => console.log(`- ${g.label}: ${g.problems.length} masalah`));

fs.writeFileSync('storage/qa-hasil.json', JSON.stringify(hasil, null, 2));

await browser.send('Target.closeTarget', { targetId: buyer.targetId });
await browser.send('Target.closeTarget', { targetId: seller.targetId });
chrome.kill();
process.exit(gagal.length ? 1 : 0);
