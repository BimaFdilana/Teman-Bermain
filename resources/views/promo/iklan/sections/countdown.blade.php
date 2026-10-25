{{-- Section: Hitungan mundur --}}
<div class="mb-10 text-center">
    <p class="inline-block px-5 py-2 rounded-full text-sm font-bold countdown" style="background:#FDF0EC;color:#A63D2F;border:1.5px solid #E8C4BC">⏳ Berakhir dalam <span class="cd-text" style="font-variant-numeric:tabular-nums">…</span></p>
    <p class="text-xs mt-2" style="color:#a39e93">@if($kampanye->mulai_at){{ $kampanye->mulai_at->format('d M Y') }} s/d @else s/d @endif{{ $kampanye->berakhir_at->format('d M Y, H:i') }} WIB</p>
</div>
