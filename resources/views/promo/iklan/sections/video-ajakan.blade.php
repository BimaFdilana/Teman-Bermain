{{-- Section: Video + Kalimat Ajakan --}}
@if($videoLink)
    <div class="mb-10">
        <p class="font-extrabold text-center mb-3" style="color:#14532D">Tonton Dulu Videonya</p>
        @if($videoEmbed)
            <div class="rounded-2xl overflow-hidden mb-3" style="aspect-ratio:16/9">
                <iframe src="{{ $videoEmbed }}" class="w-full h-full" style="border:0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen title="Video promo"></iframe>
            </div>
        @else
            <a href="{{ $videoLink }}" target="_blank" rel="noopener"
               class="kidza-btn w-full text-center mb-3" style="background:#fff;border:2px solid #F0E7CC;color:#14532D">▶ Tonton Videonya</a>
        @endif
        @if($kampanye->ajakan)
            <p class="text-sm" style="color:#5B5344;line-height:1.6;overflow-wrap:anywhere">{{ $kampanye->ajakan }}</p>
        @endif
    </div>
@elseif($kampanye->ajakan)
    <div class="mb-10">
        <p class="text-center text-sm font-bold italic px-4" style="color:#1E7B34;line-height:1.6">"{{ $kampanye->ajakan }}"</p>
    </div>
@endif
