/*
| Tambah tombol ikon mata pada setiap input password,
| supaya isi password bisa dilihat dan dicek dari salah ketik.
*/
(function () {
    var EYE =
        '<svg class="pw-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
        '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>';
    var EYE_OFF =
        '<svg class="pw-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
        '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 19C5 19 1 12 1 12a19.8 19.8 0 0 1 5.06-5.94"/>' +
        '<path d="M9.9 4.24A10.9 10.9 0 0 1 12 4c7 0 11 8 11 8a19.7 19.7 0 0 1-3.17 4.19"/>' +
        '<path d="M14.12 14.12A3 3 0 1 1 9.88 9.88"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

    function pasang(input) {
        if (input.dataset.pwReady) return;
        input.dataset.pwReady = '1';

        var wrap = document.createElement('div');
        wrap.className = 'pw-wrap';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'pw-toggle';
        btn.setAttribute('aria-label', 'Tampilkan password');
        btn.setAttribute('aria-pressed', 'false');
        btn.innerHTML = EYE + EYE_OFF;
        wrap.appendChild(btn);

        btn.addEventListener('click', function () {
            var tampil = input.type === 'password';
            input.type = tampil ? 'text' : 'password';
            btn.classList.toggle('is-shown', tampil);
            btn.setAttribute('aria-pressed', tampil ? 'true' : 'false');
            btn.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Tampilkan password');
            input.focus();
        });
    }

    function mulai() {
        document.querySelectorAll('input[type="password"]').forEach(pasang);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mulai);
    } else {
        mulai();
    }
})();
