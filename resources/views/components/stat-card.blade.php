@props(['judul', 'nilai', 'sub' => null, 'warna' => '#167941'])
<div class="kidza-card p-5" style="border-top:6px solid {{ $warna }}">
    <p class="text-sm font-bold" style="color:#6b7280">{{ $judul }}</p>
    <p class="text-3xl font-extrabold mt-1" style="font-family:'Quicksand';color:{{ $warna }}">{{ $nilai }}</p>
    @if($sub)
        <p class="text-sm mt-1" style="color:#6b7280">{{ $sub }}</p>
    @endif
</div>
