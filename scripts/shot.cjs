const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');
const http = require('http');

const chrome = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const outDir = path.join(__dirname, 'shots');
const url = process.argv[2] || 'http://127.0.0.1:8123/';
const width = Number(process.argv[3] || 390);
const height = Number(process.argv[4] || 844);
const name = process.argv[5] || 'shot';
const port = 9400 + (width % 13);

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
    `--user-data-dir=${path.join(outDir, '_cdp' + width)}`,
    'about:blank',
], { stdio: 'ignore' });

(async () => {
    await new Promise((r) => setTimeout(r, 1500));
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

    await new Promise((r) => ws.addEventListener('open', r));
    await send('Page.enable');
    await send('Runtime.enable');
    await send('Emulation.setDeviceMetricsOverride', {
        width,
        height,
        deviceScaleFactor: 1,
        mobile: width < 768,
    });
    await send('Page.navigate', { url });
    await new Promise((r) => setTimeout(r, 2500));

    const shot = await send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync(path.join(outDir, `${name}.png`), Buffer.from(shot.result.data, 'base64'));
    console.log(`saved ${name}.png (${width}x${height})`);

    ws.close();
    child.kill();
    process.exit(0);
})();
