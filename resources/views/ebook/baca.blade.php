<x-app-layout>
@section('head')
    <meta name="theme-color" content="#167941">
@endsection

<div class="flex items-center justify-between mb-4">
    <a href="{{ route('ebook') }}" class="text-sm font-bold underline">← Kembali</a>
    <a href="{{ $downloadUrl }}" class="brutal-btn brutal-btn-blue" style="padding:.5rem 1rem;font-size:.9rem">⬇ Unduh PDF</a>
</div>

<h1 class="font-display text-xl font-bold mb-4 leading-tight">{{ $product->judul }}</h1>

<div class="brutal-card overflow-hidden" style="padding:0">
    <iframe src="{{ $fileUrl }}" class="w-full" style="height:75vh;border:0" title="{{ $product->judul }}"></iframe>
</div>
<p class="text-xs opacity-60 mt-2 text-center">Geser untuk membaca · Tautan baca berlaku 30 menit</p>
</x-app-layout>
