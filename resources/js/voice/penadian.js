/**
 * Input suara dengan jeda diam.
 *
 * Modul ini dipakai Mode Aksesibilitas untuk mengisi satu kolom isian dengan
 * suara. Alurnya:
 *
 * 1. Tunggu pidato yang sedang berjalan selesai, supaya suara pidato tidak
 *    ikut tertangkap mikrofon.
 * 2. Dengarkan mikrofon secara terus-menerus.
 * 3. Kalau pengguna diam selama dua detik, ucapan itu dianggap selesai: hasil
 *    ditaruh ke kolom isian lalu dibacakan ulang sebagai konfirmasi.
 *
 * Semua langkah punya alternatif ketik biasa, dan modul ini tidak melempar
 * galat ketika SpeechRecognition tidak didukung.
 */

/** Berapa lama pengguna harus diam sebelum hasil dianggap final. */
export const DIAM_MS = 2000;

/** Batas menunggu pidato yang sedang berjalan selesai. */
export const MAKS_TUNGGU_SUARA_MS = 5000;

/**
 * Tunggu sampai tidak ada pidato yang berjalan atau yang antre.
 *
 * @param {SpeechSynthesis} synth
 * @param {number} maksMs
 * @returns {Promise<void>}
 */
export function tungguSuaraSelesai(synth = window.speechSynthesis, maksMs = MAKS_TUNGGU_SUARA_MS) {
    return new Promise((selesai) => {
        if (!synth) {
            selesai();
            return;
        }

        const mulai = Date.now();

        const cek = () => {
            const sibuk = Boolean(synth.speaking) || Boolean(synth.pending);

            if (!sibuk || Date.now() - mulai > maksMs) {
                selesai();
                return;
            }

            window.setTimeout(cek, 100);
        };

        cek();
    });
}

/**
 * Dengarkan mikrofon sampai pengguna diam dua detik.
 *
 * @param {object} opsi
 * @param {number} [opsi.diamMs]       Lama diam yang dianggap selesai.
 * @param {(hasil: string) => void} [opsi.onUcap]  Dipanggil tiap ada suara baru.
 * @param {(hasil: string) => void} [opsi.onHasil] Dipanggil sekali saat selesai.
 * @param {(pesan: string) => void} [opsi.onGalat]
 * @returns {{final: () => void, batal: () => void}} Pengendali pengenalan suara.
 */
export function dengarSampaiDiam({
    diamMs = DIAM_MS,
    onUcap = () => {},
    onHasil = () => {},
    onGalat = () => {},
} = {}) {
    const Recognition = window.SpeechRecognition ?? window.webkitSpeechRecognition;

    if (!Recognition) {
        onGalat('Mikrofon tidak didukung browser ini. Gunakan kolom input teks.');
        return { final: () => {}, batal: () => {} };
    }

    const pengenalan = new Recognition();

    pengenalan.lang = 'id-ID';
    pengenalan.continuous = true;
    pengenalan.interimResults = true;

    let hasilFinal = '';
    let hasilTerakhir = '';
    let timerDiam = null;
    let berhenti = false;

    const timerKosong = (fn) => {
        if (timerDiam !== null) {
            window.clearTimeout(timerDiam);
        }

        timerDiam = window.setTimeout(fn, diamMs);
    };

    const selesaikan = () => {
        if (berhenti) {
            return;
        }

        berhenti = true;

        if (timerDiam !== null) {
            window.clearTimeout(timerDiam);
            timerDiam = null;
        }

        try {
            pengenalan.stop();
        } catch {
            // Pengenalan sudah berhenti, tidak ada yang perlu dibersihkan.
        }

        const gabungan = `${hasilFinal} ${hasilTerakhir}`.replace(/\s+/g, ' ').trim();

        onHasil(gabungan);
    };

    pengenalan.onstart = () => {
        timerKosong(selesaikan);
    };

    pengenalan.onresult = (acara) => {
        let akhir = '';
        let sementara = '';

        for (let i = acara.resultIndex; i < acara.results.length; i++) {
            const potongan = acara.results[i][0].transcript;

            if (acara.results[i].isFinal) {
                akhir += potongan;
            } else {
                sementara += potongan;
            }
        }

        if (akhir) {
            hasilFinal = `${hasilFinal} ${akhir}`.replace(/\s+/g, ' ').trim();
        }

        hasilTerakhir = sementara.trim();

        const gabungan = `${hasilFinal} ${hasilTerakhir}`.replace(/\s+/g, ' ').trim();

        if (gabungan !== '') {
            onUcap(gabungan);
        }

        // Setiap suara baru menunda Hitungan diam.
        timerKosong(selesaikan);
    };

    pengenalan.onerror = (acara) => {
        if (berhenti) {
            return;
        }

        berhenti = true;

        if (timerDiam !== null) {
            window.clearTimeout(timerDiam);
            timerDiam = null;
        }

        if (acara.error === 'no-speech') {
            onHasil('');
            return;
        }

        onGalat(
            acara.error === 'not-allowed'
                ? 'Izin mikrofon ditolak. Gunakan kolom input teks sebagai alternatif.'
                : 'Pengenalan suara gagal. Coba lagi atau ketik manual.'
        );
    };

    pengenalan.onend = () => {
        // Chrome menutup mikrofon sendiri setelah hening; perlakukan itu
        // sebagai selesai supaya hasilnya tidak menggantung.
        if (!berhenti) {
            selesaikan();
        }
    };

    try {
        pengenalan.start();
    } catch {
        berhenti = true;
        onGalat('Mikrofon tidak dapat dibuka. Gunakan kolom input teks.');
    }

    return {
        // Berhenti sekarang dan kirim hasil yang sudah terdengar.
        final: () => selesaikan(),
        // Tutup mikrofon tanpa mengirim apa pun.
        batal: () => {
            berhenti = true;

            if (timerDiam !== null) {
                window.clearTimeout(timerDiam);
                timerDiam = null;
            }

            try {
                pengenalan.abort();
            } catch {
                // Mikrofon sudah tertutup.
            }
        },
    };
}
