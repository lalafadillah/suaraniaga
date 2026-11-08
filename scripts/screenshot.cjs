const { execFileSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const chrome = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const outDir = path.join(__dirname, 'shots');

if (!fs.existsSync(outDir)) fs.mkdirSync(outDir);

const args = process.argv.slice(2);
const mobile = args.includes('--mobile');
const routes = args.filter((a) => !a.startsWith('--'));

for (const route of routes) {
    const name = route === '/' ? 'beranda' : route.replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '');
    const file = path.join(outDir, `${mobile ? 'mobile-' : ''}${name}.png`);

    execFileSync(chrome, [
        '--headless=new',
        '--disable-gpu',
        '--hide-scrollbars',
        mobile ? '--window-size=390,844' : '--window-size=1440,920',
        '--virtual-time-budget=3000',
        `--screenshot=${file}`,
        `http://127.0.0.1:8123${route}`,
    ], { stdio: 'ignore' });

    console.log(`${route} -> ${file}`);
}
