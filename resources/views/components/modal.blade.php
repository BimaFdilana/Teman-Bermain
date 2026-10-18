@props(['id', 'judul'])
<div id="{{ $id }}" class="fixed inset-0 z-50 items-center justify-center p-4" style="display:none;background:rgba(0,0,0,.5)">
    <div class="kidza-card w-full max-w-lg p-6" style="background:#fff">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-lg" style="font-family:'Quicksand'">{{ $judul }}</h3>
            <button type="button" onclick="document.getElementById('{{ $id }}').style.display='none'" class="text-2xl">×</button>
        </div>
        {{ $slot }}
    </div>
</div>
