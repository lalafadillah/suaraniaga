(() => {
    const out = { steps: [] };
    const log = (name, data) => out.steps.push({ name, ...data });
    const wait = (ms) => new Promise((r) => setTimeout(r, ms));

    const byText = (sel, text) =>
        [...document.querySelectorAll(sel)].find((el) => el.textContent.trim().toLowerCase().includes(text.toLowerCase()));

    (async () => {
        const live = document.querySelector('[aria-live="polite"]');

        // Buka disclosure "Ubah" pada baris Ukuran teks
        const ubahButtons = [...document.querySelectorAll('button')].filter((b) => b.textContent.trim() === 'Ubah');
        log('ubahCount', { count: ubahButtons.length });

        ubahButtons[0].click();
        await wait(250);
        const panel = document.querySelector('#grup-textScale');
        log('open', {
            expanded: ubahButtons[0].getAttribute('aria-expanded'),
            panelVisible: panel ? getComputedStyle(panel).display !== 'none' : null,
        });

        // Pilih "Sangat besar"
        const big = [...panel.querySelectorAll('button')].find((b) => b.textContent.trim() === 'Sangat besar');
        big.click();
        await wait(300);
        log('pick', {
            textScale: document.documentElement.dataset.textScale,
            rootFont: getComputedStyle(document.documentElement).fontSize,
            announced: live.textContent.trim(),
            panelClosed: getComputedStyle(panel).display === 'none',
        });

        // Panel asisten suara
        const voicePanel = byText('button', 'Putar') || byText('button', 'Mulai');
        log('voicePanel', {
            found: !!voicePanel,
            text: voicePanel ? voicePanel.textContent.trim() : null,
            fokus: document.body.innerText.includes('FOKUS:'),
        });

        window.__probeResult = out;
    })();
})()
