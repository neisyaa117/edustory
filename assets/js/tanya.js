/* Ketuk ikon "?" untuk membuka/menutup penjelasan singkat di bawahnya. */
document.addEventListener('click', function (ev) {
    var tombol = ev.target.closest('.tanya');
    if (!tombol) return;
    var blok = tombol.closest('.form-group, div');
    var isi = null, el = tombol.closest('.label-baris');
    while (el && (el = el.nextElementSibling)) {
        if (el.classList.contains('tanya-isi')) { isi = el; break; }
    }
    if (!isi) return;
    var buka = isi.hasAttribute('hidden');
    isi.toggleAttribute('hidden', !buka);
    tombol.setAttribute('aria-expanded', buka ? 'true' : 'false');
});
