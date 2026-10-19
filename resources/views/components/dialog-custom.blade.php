{{-- Dialog konfirmasi & info custom pengganti confirm()/alert() bawaan browser --}}
<div id="dialog-custom" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.5);align-items:center;justify-content:center;padding:1rem">
    <div style="max-width:24rem;width:100%;padding:1.5rem;text-align:center;background:#fff;border-radius:1rem;box-shadow:0 10px 40px rgba(0,0,0,.25)">
        <div id="dialog-icon" style="font-size:2.5rem;margin-bottom:.5rem">❓</div>
        <p id="dialog-pesan" style="font-weight:700;margin-bottom:1.25rem;font-size:1rem;line-height:1.5"></p>
        <div id="dialog-tombol" style="display:flex;gap:.75rem;justify-content:center">
            <button id="dialog-batal" style="padding:.6rem 1.5rem;border-radius:.75rem;font-weight:700;background:#e5e5e5;border:2px solid #1a1a1a;cursor:pointer">Batal</button>
            <button id="dialog-ya" style="padding:.6rem 1.5rem;border-radius:.75rem;font-weight:700;background:#167941;color:#fff;border:2px solid #1a1a1a;cursor:pointer">Ya, lanjutkan</button>
        </div>
    </div>
</div>

<script>
/* Dialog custom global (pengganti confirm()/alert()) */
(function () {
    const wrap = document.getElementById('dialog-custom');
    const pesan = document.getElementById('dialog-pesan');
    const icon = document.getElementById('dialog-icon');
    const btnYa = document.getElementById('dialog-ya');
    const btnBatal = document.getElementById('dialog-batal');
    const tombolWrap = document.getElementById('dialog-tombol');
    let aksiYa = null;

    function tutup() { wrap.style.display = 'none'; aksiYa = null; }
    btnBatal.addEventListener('click', tutup);
    wrap.addEventListener('click', (e) => { if (e.target === wrap) tutup(); });

    // Konfirmasi dengan callback: tampilDialog('Hapus?', () => form.submit(), '🗑️')
    window.tampilDialog = function (teks, onYa, ikon = '❓', labelYa = 'Ya, lanjutkan') {
        pesan.textContent = teks;
        icon.textContent = ikon;
        btnYa.textContent = labelYa;
        tombolWrap.style.display = 'flex';
        aksiYa = onYa;
        wrap.style.display = 'flex';
    };

    // Info satu tombol (pengganti alert): infoDialog('Pesan...')
    window.infoDialog = function (teks, ikon = 'ℹ️') {
        pesan.textContent = teks;
        icon.textContent = ikon;
        btnBatal.style.display = 'none';
        btnYa.textContent = 'OK';
        aksiYa = null; // tutup saja
        wrap.style.display = 'flex';
    };

    btnYa.addEventListener('click', () => {
        const fn = aksiYa;
        tutup();
        // kembalikan tampilan tombol ke mode konfirmasi
        btnBatal.style.display = '';
        btnYa.textContent = 'Ya, lanjutkan';
        if (fn) fn();
    });

    // Helper untuk form: onsubmit="return tanyaForm(this, 'Hapus?')"
    window.tanyaForm = function (form, teks, ikon = '❓') {
        tampilDialog(teks, () => form.submit(), ikon);
        return false;
    };
})();
</script>
