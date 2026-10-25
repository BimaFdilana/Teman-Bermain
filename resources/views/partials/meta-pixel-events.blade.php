{{-- Meta Pixel: event browser di luar PageView. Disertakan tepat setelah meta-pixel-script. --}}
@php($metaAdd = session()->pull('meta_add_to_cart'))
@if($metaAdd)
<script>
fbq('track', 'AddToCart', @json($metaAdd));
</script>
@endif
<script>
/* Klik tombol CTA (.js-meta-lead) -> event Lead, lalu lanjut navigasi. */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-meta-lead').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (typeof fbq !== 'function') return; // pixel diblokir: langsung lanjut
            e.preventDefault();
            var href = el.getAttribute('href');
            try {
                fbq('track', 'Lead', {
                    content_name: el.dataset.metaName || undefined,
                    value: parseFloat(el.dataset.metaValue || '0') || undefined,
                    currency: 'IDR',
                });
            } catch (err) {}
            setTimeout(function () { window.location.href = href; }, 250);
        });
    });
});
</script>
