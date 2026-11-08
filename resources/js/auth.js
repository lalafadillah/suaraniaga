/**
 * Perilaku form autentikasi — JavaScript murni, tanpa framework.
 *
 * Tanggung jawabnya sempit: menampilkan kata sandi, memindahkan fokus ke
 * ringkasan error validasi, dan menandai proses kirim sebagai berjalan.
 */

function initPasswordToggles() {
    document.querySelectorAll('[data-toggle-password]').forEach((button) => {
        const input = document.getElementById(button.dataset.togglePassword);

        if (!input) {
            return;
        }

        button.addEventListener('click', () => {
            const sedangTampil = input.type === 'text';

            input.type = sedangTampil ? 'password' : 'text';
            button.textContent = sedangTampil ? 'Lihat' : 'Sembunyikan';
            input.focus();
        });
    });
}

function initKirimForm() {
    document.querySelectorAll('[data-auth-form]').forEach((form) => {
        form.addEventListener('submit', () => {
            const tombol = form.querySelector('button[type="submit"]');
            const status = form.querySelector('[data-auth-status]');

            if (tombol) {
                tombol.disabled = true;
                tombol.textContent = 'Memproses…';
            }

            if (status) {
                status.textContent = 'Mengirim data Anda, mohon tunggu.';
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initPasswordToggles();
    initKirimForm();
});
