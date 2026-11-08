(() => {
    const form = document.querySelector('[data-auth-form]');
    document.getElementById('email').value = 'salah@contoh.com';
    document.getElementById('password').value = 'salahsalah';
    form?.requestSubmit();
})();
