{{-- Section: Materi Ebook --}}
@if(!empty($kampanye->materi))
    <div class="rounded-3xl p-5 mb-10" style="background:#FFFDF4;border:2px solid #F0E7CC">
        <p class="font-extrabold mb-3" style="color:#14532D">📖 Materi Ebook</p>
        <ol class="text-sm space-y-2" style="color:#444;list-style-position:inside;margin:0;padding:0">
            @foreach($kampanye->materi as $i => $m)
                <li><span class="font-bold" style="color:#1E7B34">{{ $i + 1 }}.</span> {{ $m }}</li>
            @endforeach
        </ol>
    </div>
@endif
