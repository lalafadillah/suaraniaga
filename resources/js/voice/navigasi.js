/**
 * Navigasi suara global.
 *
 * Setiap elemen interaktif yang ditunjuk pointer atau dipilih keyboard
 * dibacakan otomatis dalam Bahasa Indonesia: nama elemen, jenisnya, lalu
 * informasi tambahan untuk kolom isian (wajib diisi, pesan galat).
 *
 * Modul ini memakai delegasi peristiwa pada dokumen, jadi elemen yang dibuat
 * setelah halaman dimuat ikut terbaca tanpa pendaftaran satu per satu.
 * Semua pidato dibatalkan sebelum yang baru dimulai supaya suara tidak
 * bertumpuk.
 */

const LANG = 'id-ID';

/** Jeda minimum sebelum elemen yang sama boleh dibacakan ulang. */
const JEDA_SAMA_MS = 1000;

/** Tunggu sebentar, agar klik yang menyusul (misal tombol dengarkan) menang dulu. */
const JEDA_UCAP_MS = 180;

/** Elemen yang dibaca namanya saat pointer melewati atau fokus berpindah. */
const SELEKTOR_INTERAKTIF = [
    'a[href]',
    'button',
    'input:not([type="hidden"])',
    'select',
    'textarea',
    'summary',
    '[tabindex]:not([tabindex="-1"])',
    '[role="button"]',
    '[role="link"]',
    '[role="checkbox"]',
    '[role="radio"]',
    '[role="tab"]',
    '[role="menuitem"]',
    '[role="option"]',
    '[role="switch"]',
    '[contenteditable="true"]',
].join(', ');

const JENIS_INPUT = {
    text: 'kolom input teks',
    email: 'kolom input email',
    password: 'kolom input kata sandi',
    number: 'kolom input angka',
    tel: 'kolom input telepon',
    url: 'kolom input tautan',
    search: 'kolom input pencarian',
    date: 'kolom input tanggal',
    'datetime-local': 'kolom input tanggal dan waktu',
    month: 'kolom input bulan',
    week: 'kolom input minggu',
    time: 'kolom input waktu',
    file: 'pemilih berkas',
    color: 'pemilih warna',
    range: 'penggeser',
    checkbox: 'kotak centang',
    radio: 'tombol radio',
    submit: 'tombol kirim',
    reset: 'tombol atur ulang',
    button: 'tombol',
    image: 'tombol gambar',
};

const JENIS_PERAN = {
    button: 'tombol',
    link: 'tautan',
    checkbox: 'kotak centang',
    radio: 'tombol radio',
    tab: 'tab',
    menuitem: 'item menu',
    option: 'pilihan',
    switch: 'sakelar',
    textbox: 'kolom input teks',
    searchbox: 'kolom input pencarian',
    combobox: 'kotak isian gabungan',
};

/**
 * Teks yang bisa dibaca pengguna, tanpa bagian khusus pembaca layar.
 *
 * Label di SuaraNiaga memuat penanda wajib diisi yang hanya dibaca pembaca
 * layar. Penanda itu tidak ikut dibacakan sebagai bagian nama, karena
 * keterbacaan "wajib diisi" sudah dicatat terpisah.
 */
function teksTerbaca(node) {
    if (!node) {
        return '';
    }

    const salinan = node.cloneNode(true);

    salinan.querySelectorAll('.sr-only').forEach((bagian) => bagian.remove());

    salinan.querySelectorAll('img[alt]').forEach((gambar) => {
        gambar.replaceWith(document.createTextNode(gambar.getAttribute('alt')));
    });

    return (salinan.textContent || '').replace(/\s+/g, ' ').trim();
}

/**
 * Rapikan nama: buang penanda wajib isi dan bintang wajib isi.
 */
function rapikan(teks) {
    return teks
        .replace(/\(\s*wajib diisi\s*\)/gi, '')
        .replace(/\s*\*+\s*$/, '')
        .replace(/\s{2,}/g, ' ')
        .trim();
}

/**
 * Label yang menempel pada kolom isian.
 */
function labelUntuk(elemen) {
    if (elemen.labels && elemen.labels.length > 0) {
        return elemen.labels[0];
    }

    const id = elemen.getAttribute('id');

    if (id) {
        const label = document.querySelector(`label[for="${CSS.escape(id)}"]`);

        if (label) {
            return label;
        }
    }

    return elemen.closest('label');
}

/**
 * Nama yang bisa diakses elemen: nama yang announce pembaca layar.
 */
function namaAksesibel(elemen) {
    const ditunjuk = elemen.getAttribute('aria-labelledby');

    if (ditunjuk) {
        const teks = ditunjuk
            .split(/\s+/)
            .map((id) => teksTerbaca(document.getElementById(id)))
            .filter(Boolean)
            .join(' ');

        if (teks) {
            return rapikan(teks);
        }
    }

    const labelAria = (elemen.getAttribute('aria-label') || '').trim();

    if (labelAria) {
        return rapikan(labelAria);
    }

    const label = labelUntuk(elemen);

    if (label) {
        const teks = teksTerbaca(label);

        if (teks) {
            return rapikan(teks);
        }
    }

    const tipe = (elemen.getAttribute('type') || '').toLowerCase();

    if (tipe === 'submit' || tipe === 'reset' || tipe === 'button') {
        const nilai = (elemen.getAttribute('value') || '').trim();

        if (nilai) {
            return rapikan(nilai);
        }
    }

    const judul = (elemen.getAttribute('title') || '').trim();

    if (judul) {
        return rapikan(judul);
    }

    const isi = teksTerbaca(elemen);

    if (isi) {
        return rapikan(isi);
    }

    const placeholder = (elemen.getAttribute('placeholder') || '').trim();

    if (placeholder) {
        return rapikan(placeholder);
    }

    return '';
}

/**
 * Jenis elemen dalam Bahasa Indonesia, supaya pengguna tahu bentuknya.
 */
function jenisElemen(elemen) {
    const tag = elemen.tagName.toLowerCase();

    if (tag === 'a') {
        return 'tautan';
    }

    if (tag === 'button' || tag === 'summary') {
        return 'tombol';
    }

    if (tag === 'textarea') {
        return 'area teks';
    }

    if (tag === 'select') {
        return elemen.multiple || (elemen.size && elemen.size > 1) ? 'daftar pilihan' : 'pilihan';
    }

    if (tag === 'input') {
        const tipe = (elemen.getAttribute('type') || 'text').toLowerCase();

        return JENIS_INPUT[tipe] || 'kolom input teks';
    }

    if (elemen.isContentEditable) {
        return 'area teks';
    }

    const peran = (elemen.getAttribute('role') || '').toLowerCase();

    return JENIS_PERAN[peran] || 'elemen interaktif';
}

/**
 * Pesan galat validasi yang menempel pada kolom isian.
 */
function pesanGalat(elemen) {
    if (elemen.getAttribute('aria-invalid') !== 'true') {
        return '';
    }

    const dirujuk = (elemen.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);

    for (const id of dirujuk) {
        const node = document.getElementById(id);

        if (!node) {
            continue;
        }

        const teks = teksTerbaca(node);

        if (teks && /galat|error|salah|tidak valid/i.test(`${id} ${node.className || ''}`)) {
            return teks;
        }
    }

    const induk = elemen.closest('div, fieldset, form');

    if (induk) {
        const galat = induk.querySelector('.text-danger, [data-galat]');

        if (galat) {
            return teksTerbaca(galat);
        }
    }

    return '';
}

/**
 * Susun kalimat yang dibacakan untuk satu elemen.
 */
export function kalimat(elemen) {
    const nama = namaAksesibel(elemen);
    const jenis = jenisElemen(elemen);
    const bagian = [];

    bagian.push(nama !== '' ? nama : jenis);

    if (nama !== '') {
        bagian.push(jenis);
    }

    const tag = elemen.tagName.toLowerCase();

    if (tag === 'input' || tag === 'textarea' || tag === 'select') {
        const placeholder = (elemen.getAttribute('placeholder') || '').trim();

        if (placeholder !== '' && placeholder !== nama) {
            bagian.push(placeholder);
        }

        if (elemen.required || elemen.getAttribute('aria-required') === 'true') {
            bagian.push('wajib diisi');
        }

        const galat = pesanGalat(elemen);

        if (galat !== '') {
            bagian.push(`Galat: ${galat}`);
        }
    }

    return bagian.join(', ');
}

/**
 * Jalankan navigasi suara global.
 *
 * @param {object} konfigurasi
 * @param {() => boolean} konfigurasi.modeServer  Mode aksesibilitas aktif di server.
 * @param {() => object}  konfigurasi.prefs       Pengaturan pengguna di peramban.
 * @param {() => number}  konfigurasi.laju        Kecepatan bicara yang dipilih pengguna.
 * @param {() => boolean} konfigurasi.suarAktif   Suara eksplisit sedang diputar.
 */
export function initNavigasiSuara(konfigurasi = {}) {
    if (window.__suaraNavigasi) {
        return;
    }

    window.__suaraNavigasi = true;

    const modeServer = konfigurasi.modeServer || (() => false);
    const ambilPrefs = konfigurasi.prefs || (() => ({}));
    const lajuSuara = konfigurasi.laju || (() => 1);
    const suarAktif = konfigurasi.suarAktif || (() => false);

    let elemenTerakhir = null;
    let waktuTerakhir = 0;
    let hoverTerakhir = null;
    let fokusTerakhir = null;
    let timerUcap = null;
    let daftarSuara = [];

    function segarkanSuara() {
        try {
            daftarSuara = window.speechSynthesis.getVoices() || [];
        } catch {
            daftarSuara = [];
        }
    }

    /**
     * Suara Bahasa Indonesia: Google bila ada, kalau tidak suara id lain.
     */
    function pilihSuara() {
        if (daftarSuara.length === 0) {
            segarkanSuara();
        }

        const bahasaIndonesia = daftarSuara.filter((suara) => {
            return typeof suara.lang === 'string' && suara.lang.toLowerCase().startsWith('id');
        });

        const google = bahasaIndonesia.find((suara) => /google/i.test(suara.name || ''));

        return google || bahasaIndonesia[0] || null;
    }

    /**
     * Pembacaan otomatis mengikuti mode aksesibilitas, kecuali pengguna
     * menyalakannya atau mematikannya sendiri lewat pengaturan.
     */
    function otomatisAktif() {
        if (!('speechSynthesis' in window)) {
            return false;
        }

        const prefs = ambilPrefs() || {};
        const setelan = prefs.voiceNavigation || 'auto';

        if (setelan === 'nonaktif') {
            return false;
        }

        if (setelan === 'aktif') {
            return true;
        }

        return modeServer();
    }

    /**
     * Batalkan pidato sebelumnya, lalu ucapkan yang baru.
     */
    function ucapkan(teks) {
        if (!('speechSynthesis' in window) || !('SpeechSynthesisUtterance' in window)) {
            return;
        }

        window.speechSynthesis.cancel();

        if (window.speechSynthesis.paused) {
            window.speechSynthesis.resume();
        }

        timerUcap = window.setTimeout(() => {
            timerUcap = null;

            const utterance = new SpeechSynthesisUtterance(teks);
            utterance.lang = LANG;
            utterance.rate = lajuSuara() || 1;
            utterance.pitch = 1;
            utterance.volume = 1;

            const suara = pilihSuara();

            if (suara) {
                utterance.voice = suara;
            }

            window.speechSynthesis.speak(utterance);
        }, JEDA_UCAP_MS);
    }

    /**
     * Bacakan satu elemen, dengan jeda anti-ganda.
     */
    function bacakan(elemen) {
        if (!otomatisAktif()) {
            return;
        }

        // Suara yang diminta pengguna (misal "Dengarkan deskripsi") tidak
        // boleh dipotong oleh pembacaan otomatis.
        if (suarAktif()) {
            return;
        }

        const teks = kalimat(elemen);

        if (teks === '') {
            return;
        }

        const sekarang = Date.now();

        if (elemen === elemenTerakhir && sekarang - waktuTerakhir < JEDA_SAMA_MS) {
            return;
        }

        elemenTerakhir = elemen;
        waktuTerakhir = sekarang;

        if (timerUcap !== null) {
            window.clearTimeout(timerUcap);
        }

        ucapkan(teks);
    }

    /**
     * Cari elemen interaktif terdekat dari target peristiwa.
     */
    function elemenTerdekat(target) {
        if (!(target instanceof Element)) {
            return null;
        }

        const elemen = target.closest(SELEKTOR_INTERAKTIF);

        if (!elemen) {
            return null;
        }

        if (elemen.hasAttribute('aria-hidden') || elemen.closest('[aria-hidden="true"]')) {
            return null;
        }

        if (elemen.matches(':disabled')) {
            return null;
        }

        return elemen;
    }

    document.addEventListener('mouseover', (acara) => {
        const elemen = elemenTerdekat(acara.target);

        if (!elemen) {
            return;
        }

        // Pointer masih di elemen yang sama: jangan diulang.
        if (elemen === hoverTerakhir) {
            return;
        }

        hoverTerakhir = elemen;
        bacakan(elemen);
    });

    document.addEventListener('mouseout', (acara) => {
        const tujuan = acara.relatedTarget;

        if (hoverTerakhir && tujuan instanceof Node && hoverTerakhir.contains(tujuan)) {
            return;
        }

        hoverTerakhir = null;
    });

    document.addEventListener('focusin', (acara) => {
        const elemen = elemenTerdekat(acara.target);

        if (!elemen) {
            return;
        }

        // Fokus tidak berpindah: jangan diulang.
        if (elemen === fokusTerakhir) {
            return;
        }

        fokusTerakhir = elemen;
        bacakan(elemen);
    });

    document.addEventListener('focusout', () => {
        fokusTerakhir = null;
    });

    if ('speechSynthesis' in window) {
        segarkanSuara();
        window.speechSynthesis.addEventListener?.('voiceschanged', segarkanSuara);
    }
}
