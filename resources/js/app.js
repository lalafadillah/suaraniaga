import Alpine from 'alpinejs';
import { initNavigasiSuara } from './voice/navigasi.js';

/**
 * Mesin suara SuaraNiaga.
 *
 * Prinsip: NVDA / Narrator / VoiceOver tetap pembaca layar utama. SuaraNiaga
 * menambah input suara (SpeechRecognition) dan pemutaran deskripsi audio
 * (SpeechSynthesis) atas permintaan eksplisit pengguna.
 *
 * Navigasi suara global (resources/js/voice/navigasi.js) membaca nama dan
 * jenis elemen yang ditunjuk pointer atau dipilih keyboard. Pembacaan itu
 * mengikuti mode aksesibilitas dan bisa dimatikan lewat pengaturan, dan tidak
 * pernah memotong suara deskripsi yang sedang diputar.
 */

const RECOGNITION_LANG = 'id-ID';

const SPEECH_RATES = {
    lambat: 0.7,
    normal: 1,
    cepat: 1.4,
};

const prefStorageKey = 'suaraniaga.prefs';

const defaultPrefs = {
    textScale: 'normal',
    contrast: 'standar',
    screenReader: 'nvda',
    voiceAssistant: 'aktif',
    voiceNavigation: 'auto',
    speechRate: 'normal',
    motion: 'normal',
};

function readPrefs() {
    try {
        return { ...defaultPrefs, ...JSON.parse(localStorage.getItem(prefStorageKey) ?? '{}') };
    } catch {
        return { ...defaultPrefs };
    }
}

function applyPrefs(prefs) {
    const root = document.documentElement;
    root.dataset.textScale = prefs.textScale;
    root.dataset.contrast = prefs.contrast;
    root.dataset.motion = prefs.motion === 'kurangi' ? 'kurangi' : 'normal';
}

Alpine.store('suara', {
    state: 'idle', // idle | listening | processing | speaking
    transcript: '',
    interim: '',
    supported: 'SpeechRecognition' in window || 'webkitSpeechRecognition' in window,
    synthesisSupported: 'speechSynthesis' in window,
    error: '',
    onResult: null,
    suarAktif: false,

    _recognition: null,

    init() {
        this.supported = 'SpeechRecognition' in window || 'webkitSpeechRecognition' in window;
        this.synthesisSupported = 'speechSynthesis' in window;
    },

    get isBusy() {
        return this.state !== 'idle';
    },

    start(onResult = null) {
        if (!this.supported) {
            this.error = 'Mikrofon tidak didukung browser ini. Gunakan kolom input teks.';
            return;
        }

        this.onResult = onResult;
        this.transcript = '';
        this.interim = '';
        this.error = '';

        const Recognition = window.SpeechRecognition ?? window.webkitSpeechRecognition;
        const recognition = new Recognition();
        recognition.lang = RECOGNITION_LANG;
        recognition.continuous = false;
        recognition.interimResults = true;

        recognition.onstart = () => {
            this.state = 'listening';
        };

        recognition.onresult = (event) => {
            let final = '';
            let interim = '';

            for (let i = event.resultIndex; i < event.results.length; i++) {
                const chunk = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    final += chunk;
                } else {
                    interim += chunk;
                }
            }

            this.interim = interim;

            if (final) {
                this.transcript = final.trim();
                this.interim = '';
                this.state = 'processing';

                const nilai = this.transcript;
                const handler = this.onResult;
                this.onResult = null;

                if (nilai && handler) {
                    handler(nilai);
                }
            }
        };

        recognition.onerror = (event) => {
            this.state = 'idle';
            this.error = event.error === 'not-allowed'
                ? 'Izin mikrofon ditolak. Gunakan kolom input teks sebagai alternatif.'
                : 'Pengenalan suara gagal. Coba lagi atau ketik manual.';
        };

        recognition.onend = () => {
            if (this.state === 'listening' || this.state === 'processing') {
                this.state = 'idle';
            }
        };

        this._recognition = recognition;

        try {
            recognition.start();
        } catch {
            this.state = 'idle';
            this.error = 'Mikrofon tidak dapat dibuka. Gunakan kolom input teks.';
        }
    },

    stop() {
        this._recognition?.stop();
    },

    cancel() {
        this._recognition?.stop();
        this._recognition = null;
        this.state = 'idle';
        this.transcript = '';
        this.interim = '';
        this.onResult = null;
    },

    speak(text, prefs = readPrefs()) {
        if (!this.synthesisSupported) {
            this.error = 'Browser ini tidak mendukung pemutaran suara.';
            return;
        }

        window.speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = RECOGNITION_LANG;
        utterance.rate = SPEECH_RATES[prefs.speechRate] ?? 1;

        this.suarAktif = true;

        utterance.onstart = () => {
            this.state = 'speaking';
        };

        utterance.onend = () => {
            this.state = 'idle';
            this.suarAktif = false;
        };

        utterance.onerror = () => {
            this.state = 'idle';
            this.suarAktif = false;
        };

        window.speechSynthesis.speak(utterance);
    },

    stopSpeaking() {
        window.speechSynthesis.cancel();
        this.state = 'idle';
        this.suarAktif = false;
    },
});

Alpine.store('prefs', {
    values: readPrefs(),

    init() {
        applyPrefs(this.values);
    },

    save() {
        localStorage.setItem(prefStorageKey, JSON.stringify(this.values));
        applyPrefs(this.values);
    },

    set(key, value) {
        this.values = { ...this.values, [key]: value };
        this.save();
    },

    get speechRate() {
        return SPEECH_RATES[this.values.speechRate] ?? 1;
    },
});

/**
 * Live region: mengumumkan perubahan status tanpa membaca ulang seluruh halaman.
 */
Alpine.store('announcer', {
    message: '',

    say(text) {
        // Kosongkan dulu agar pesan berulang tetap terbaca.
        this.message = '';
        queueMicrotask(() => {
            this.message = text;
        });
    },
});

window.Alpine = Alpine;
Alpine.start();

/**
 * Navigasi suara global: nama dan jenis elemen dibacakan otomatis saat
 * pointer melewati atau fokus berpindah.
 *
 * Pembacaan mengikuti mode aksesibilitas dari server. Pengguna bisa
 * menyalakan atau mematikannya sendiri lewat pengaturan.
 */
initNavigasiSuara({
    modeServer: () => document.documentElement.dataset.modeAksesibilitas === '1',
    prefs: () => Alpine.store('prefs').values,
    laju: () => Alpine.store('prefs').speechRate,
    suarAktif: () => Alpine.store('suara').suarAktif,
});

/**
 * Validasi formulir: fokus pindah ke ringkasan error, lalu tombol kirim
 * dinonaktifkan supaya tidak terkirim dua kali.
 */
function initValidasiFokus() {
    const ringkasan = document.getElementById('ringkasan-galat');

    if (ringkasan) {
        ringkasan.focus();
        return;
    }

    const fieldSalah = document.querySelector('[aria-invalid="true"]');

    if (fieldSalah) {
        fieldSalah.focus();
    }
}

function initKirimForm() {
    document.querySelectorAll('[data-form-siap]').forEach((form) => {
        form.addEventListener('submit', () => {
            const tombol = form.querySelector('button[type="submit"]');

            if (tombol) {
                tombol.disabled = true;
                tombol.textContent = 'Memproses…';
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initValidasiFokus();
    initKirimForm();
});
