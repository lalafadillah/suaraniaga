const { spawn } = require('child_process');
const path = require('path');
const http = require('http');

const chrome = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const outDir = path.join(__dirname, 'shots');
const base = process.argv[2] || 'http://127.0.0.1:8123';
const port = 9411;

function get(pathname) {
    return new Promise((resolve, reject) => {
        http.get({ host: '127.0.0.1', port, path: pathname }, (res) => {
            let data = '';
            res.on('data', (c) => (data += c));
            res.on('end', () => resolve(JSON.parse(data)));
        }).on('error', reject);
    });
}

const child = spawn(chrome, [
    '--headless=new',
    '--disable-gpu',
    '--hide-scrollbars',
    `--remote-debugging-port=${port}`,
    `--user-data-dir=${path.join(outDir, '_cdpflow')}`,
    'about:blank',
], { stdio: 'ignore' });

const wait = (ms) => new Promise((r) => setTimeout(r, ms));

(async () => {
    await wait(1500);
    const targets = await get('/json/list');
    const page = targets.find((t) => t.type === 'page');

    const ws = new WebSocket(page.webSocketDebuggerUrl);
    let id = 0;
    const pending = new Map();
    ws.addEventListener('message', (ev) => {
        const msg = JSON.parse(ev.data);
        if (msg.id && pending.has(msg.id)) {
            pending.get(msg.id)(msg);
            pending.delete(msg.id);
        }
    });
    const send = (method, params) =>
        new Promise((resolve) => {
            const myId = ++id;
            pending.set(myId, resolve);
            ws.send(JSON.stringify({ id: myId, method, params }));
        });

    const evaluate = async (expression) => {
        const res = await send('Runtime.evaluate', { expression, returnByValue: true });
        return res.result?.result?.value;
    };

    const goto = async (url) => {
        await send('Page.navigate', { url });
        await wait(1600);
    };

    await new Promise((r) => ws.addEventListener('open', r));
    await send('Page.enable');
    await send('Runtime.enable');

    const hasil = {};

    // 1. Daftar akun baru
    await goto(`${base}/daftar`);
    await evaluate(`(() => {
        document.getElementById('name').value = 'Bagja Fadillah';
        document.getElementById('email').value = 'bagja@suaraniaga.test';
        document.getElementById('password').value = 'rahasia123';
        document.getElementById('password_confirmation').value = 'rahasia123';
        document.querySelector('[data-auth-form]').requestSubmit();
    })()`);
    await wait(2000);
    hasil.daftar = await evaluate(`JSON.stringify({
        url: location.pathname,
        tombolKeluarDiTopnav: [...document.querySelectorAll('button, a')].some((el) => el.textContent.trim() === 'Keluar'),
        pesanGalat: document.getElementById('ringkasan-galat')?.innerText.replace(/\\s+/g, ' ').trim() ?? null,
    })`);

    // 2. Halaman profil menampilkan data akun
    await goto(`${base}/profil`);
    hasil.profil = await evaluate(`JSON.stringify({
        url: location.pathname,
        nama: [...document.querySelectorAll('dd')].map((d) => d.textContent.trim()).slice(0, 3),
        adaTombolKeluar: [...document.querySelectorAll('button')].some((b) => b.textContent.trim() === 'Keluar dari akun'),
    })`);

    // 3. Keluar
    await evaluate(`(() => {
        const tombol = [...document.querySelectorAll('button')].find((b) => b.textContent.trim() === 'Keluar dari akun');
        tombol?.click();
    })()`);
    await wait(2000);
    hasil.keluar = await evaluate(`JSON.stringify({
        url: location.pathname,
        adaMasuk: [...document.querySelectorAll('a')].some((a) => a.textContent.trim() === 'Masuk'),
        masihAdaKeluar: [...document.querySelectorAll('button, a')].some((el) => el.textContent.trim() === 'Keluar'),
    })`);

    // 4. Halaman masuk hanya untuk tamu
    await goto(`${base}/masuk`);
    hasil.halamanMasuk = await evaluate(`JSON.stringify({
        url: location.pathname,
        adaForm: !!document.querySelector('[data-auth-form]'),
    })`);

    // 5. Masuk lagi dengan akun tadi
    await evaluate(`(() => {
        document.getElementById('email').value = 'bagja@suaraniaga.test';
        document.getElementById('password').value = 'rahasia123';
        document.querySelector('[data-auth-form]').requestSubmit();
    })()`);
    await wait(2000);
    hasil.masuk = await evaluate(`JSON.stringify({
        url: location.pathname,
        tombolKeluar: [...document.querySelectorAll('button, a')].some((el) => el.textContent.trim() === 'Keluar'),
        pesanGalat: document.getElementById('ringkasan-galat')?.innerText.replace(/\\s+/g, ' ').trim() ?? null,
    })`);

    console.log(hasil);
    ws.close();
    child.kill();
    process.exit(0);
})();
