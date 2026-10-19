{{-- Section: Testimoni (slider SS WA) --}}
@if(!empty($kampanye->testimoni_ss))
    <div class="mt-12 mb-8">
        <p class="font-extrabold text-center mb-1" style="color:#14532D">💬 Kata Mereka</p>
        <p class="text-center text-xs mb-4" style="color:#a39e93">Geser untuk melihat testimoni pelanggan</p>
        <div class="ss-slider" id="ssSlider">
            @foreach($kampanye->testimoniSsUrls() as $i => $url)
                <div class="ss-slide">
                    <img src="{{ $url }}" alt="Testimoni {{ $i+1 }}" loading="lazy">
                </div>
            @endforeach
        </div>
        <div class="ss-dots" id="ssDots">
            @foreach($kampanye->testimoniSsUrls() as $i => $url)
                <button type="button" data-i="{{ $i }}" aria-label="Slide {{ $i+1 }}"></button>
            @endforeach
        </div>
    </div>
    <style>
    .ss-slider{display:flex;gap:12px;overflow-x:auto;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;scrollbar-width:none;padding:4px 2px;scroll-behavior:smooth}
    .ss-slider::-webkit-scrollbar{display:none}
    .ss-slide{flex:0 0 auto;width:min(240px,68vw);scroll-snap-align:center;border-radius:16px;overflow:hidden;background:#fff;border:2px solid #F0E7CC;box-shadow:0 2px 8px rgba(0,0,0,.06)}
    .ss-slide img{width:100%;height:auto;display:block}
    .ss-dots{display:flex;justify-content:center;gap:8px;margin-top:12px}
    .ss-dots button{width:10px;height:10px;border-radius:999px;border:none;background:#E5DCC3;cursor:pointer;padding:0;transition:background .3s}
    .ss-dots button.aktif{background:#1E7B34;transform:scale(1.2)}
    </style>
    <script>
    (function(){
        var slider=document.getElementById('ssSlider'),dots=document.getElementById('ssDots');
        if(!slider||!dots)return;
        var btns=dots.querySelectorAll('button');
        function tandai(){
            var tengah=slider.scrollLeft+slider.clientWidth/2,idx=0,jarakMin=1e9;
            slider.querySelectorAll('.ss-slide').forEach(function(s,i){
                var j=Math.abs((s.offsetLeft+s.clientWidth/2)-tengah);
                if(j<jarakMin){jarakMin=j;idx=i;}
            });
            btns.forEach(function(b,i){b.classList.toggle('aktif',i===idx);});
        }
        btns.forEach(function(b,i){
            if(i===0)b.classList.add('aktif');
            b.addEventListener('click',function(){
                var s=slider.querySelectorAll('.ss-slide')[i];
                if(s)slider.scrollTo({left:s.offsetLeft-(slider.clientWidth-s.clientWidth)/2,behavior:'smooth'});
            });
        });
        var t;slider.addEventListener('scroll',function(){clearTimeout(t);t=setTimeout(tandai,120);},{passive:true});
    })();
    </script>
@elseif($testimonis->isNotEmpty())
    <div class="mt-12 mb-8">
        <p class="font-extrabold text-center mb-3" style="color:#14532D">Bukti Kepuasan</p>
        @foreach($testimonis as $t)
            <div class="rounded-2xl p-4 mb-2 bg-white" style="border:2px solid #F0E7CC">
                <p class="text-sm italic mb-1" style="color:#444">"{{ $t->isi }}"</p>
                <p class="text-xs font-bold" style="color:#a39e93">— {{ $t->nama }}{{ $t->peran ? ', '.$t->peran : '' }}</p>
            </div>
        @endforeach
    </div>
@endif
