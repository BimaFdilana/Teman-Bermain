<x-admin-layout>
<div class="flex items-center justify-between mb-5">
    <div>
        <a href="{{ route('admin.miniapp.index') }}" class="text-sm font-bold underline">← Kembali</a>
        <h1 class="text-2xl font-bold mt-1" style="font-family:'Quicksand'">👁️ Preview: {{ $product->judul }}</h1>
    </div>
    <a href="{{ route('admin.miniapp.edit', $product) }}" class="kidza-btn kidza-btn-yellow">✏️ Edit</a>
</div>

@if($fileUrl)
    <div class="kidza-card overflow-hidden" style="padding:0">
        <iframe src="{{ $fileUrl }}" sandbox="allow-scripts" class="w-full" style="height:70vh;border:0" title="Preview {{ $product->judul }}"></iframe>
    </div>
@else
    <x-empty-state pesan="Belum ada file HTML yang diupload." />
@endif
</x-admin-layout>
