(() => {
    const out = { steps: [] };
    const log = (name, data) => out.steps.push({ name, ...data });
    const wait = (ms) => new Promise((r) => setTimeout(r, ms));

    (async () => {
        const errors = [];
        window.addEventListener('error', (e) => errors.push(String(e.message)));

        log('dokumen', {
            lang: document.documentElement.lang,
            h1: [...document.querySelectorAll('h1')].filter((h) => h.offsetParent !== null).map((h) => h.textContent.trim()),
            skip: document.querySelector('.skip-link')?.getAttribute('href'),
            liveRegion: !!document.querySelector('[aria-live="polite"]'),
        });

        // Setiap input punya label terlihat
        const inputs = [...document.querySelectorAll('input')];
        log('label', {
            jumlah: inputs.length,
            tanpaLabel: inputs
                .filter((i) => {
                    if (i.getAttribute('type') === 'hidden') return false;
                    const label = i.id ? document.querySelector(`label[for="${i.id}"]`) : null;
                    return !label && !i.getAttribute('aria-label') && !i.closest('label');
                })
                .map((i) => i.name || i.id),
        });

        // Tombol lihat kata sandi
        const toggle = document.querySelector('[data-toggle-password]');
        const sandi = document.getElementById('password');
        const sebelum = sandi?.type;
        toggle?.click();
        await wait(150);
        log('toggleSandi', {
            ada: !!toggle,
            labelTombol: toggle?.textContent.trim(),
            tipeSebelum: sebelum,
            tipeSesudah: sandi?.type,
            fokusDiInput: document.activeElement === sandi,
        });

        // Kirim data salah -> harus muncul ringkasan error dan fokus ke sana
        const form = document.querySelector('[data-auth-form]');
        document.getElementById('email').value = 'salah@contoh.com';
        document.getElementById('password').value = 'salahsalah';
        form?.requestSubmit();

        await wait(1200);
        const ringkasan = document.getElementById('ringkasan-galat');
        log('validasi', {
            url: location.pathname,
            ringkasanAda: !!ringkasan,
            role: ringkasan?.getAttribute('role'),
            tabindex: ringkasan?.getAttribute('tabindex'),
            fokusDiRingkasan: document.activeElement === ringkasan,
            isiRingkasan: ringkasan?.innerText.replace(/\s+/g, ' ').trim(),
            errorField: document.getElementById('email')?.getAttribute('aria-invalid'),
            deskripsiField: document.getElementById('email')?.getAttribute('aria-describedby'),
        });

        log('jsError', { daftar: errors });
        window.__probeResult = out;
    })();
})();
