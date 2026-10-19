{{-- Section: Banner --}}
@if($kampanye->gambarUrl())
    <div class="mb-6 -mx-5">
        <img src="{{ $kampanye->gambarUrl() }}" alt="{{ $kampanye->judul }}" class="w-full object-cover">
    </div>
@endif
