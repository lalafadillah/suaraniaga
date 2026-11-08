(() => {
    const out = {};
    const ringkasan = document.getElementById('ringkasan-galat');

    out.url = location.pathname;
    out.ringkasanAda = !!ringkasan;
    out.role = ringkasan?.getAttribute('role') ?? null;
    out.tabindex = ringkasan?.getAttribute('tabindex') ?? null;
    out.fokusDiRingkasan = document.activeElement === ringkasan;
    out.isiRingkasan = ringkasan?.innerText.replace(/\s+/g, ' ').trim() ?? null;
    out.errorField = document.getElementById('email')?.getAttribute('aria-invalid') ?? null;
    out.deskripsiField = document.getElementById('email')?.getAttribute('aria-describedby') ?? null;
    out.nilaiEmailDipertahankan = document.getElementById('email')?.value ?? null;
    out.h1 = [...document.querySelectorAll('h1')].filter((h) => h.offsetParent !== null).map((h) => h.textContent.trim());

    return JSON.stringify(out, null, 2);
})();
