@extends('layouts.app')
@section('title', $sekolah->nama)
@section('content')
@php $p = $sekolah->profil; @endphp

<a href="{{ url('/') }}" class="text-blue-700 text-sm">&larr; Kembali ke peta</a>
<h1 class="text-2xl font-bold mt-2">{{ $sekolah->nama }}</h1>
<p class="text-gray-600 mb-4">
    NPSN {{ $sekolah->npsn }} - {{ $sekolah->jenjang }} {{ $sekolah->status }}
</p>

<div class="bg-white rounded shadow p-6 max-w-2xl space-y-2">
    @if ($p?->logoUrl())
        <img src="{{ $p->logoUrl() }}" alt="Logo" class="h-20 mb-2">
    @endif
    <p><strong>Alamat:</strong> {{ $sekolah->alamat }}, {{ $sekolah->kecamatan }}</p>
    <p><strong>Telepon:</strong> {{ $sekolah->telepon ?: '-' }}</p>
    <p><strong>Kepala sekolah:</strong> {{ $p?->kepala_sekolah ?: '-' }}</p>
    <p><strong>Akreditasi:</strong> {{ $p?->akreditasi ?: '-' }}</p>
    <p><strong>Tahun berdiri:</strong> {{ $p?->tahun_berdiri ?: '-' }}</p>
    <p><strong>Jumlah siswa:</strong> {{ $p?->jumlah_siswa ?? '-' }}</p>
    <p><strong>Jumlah guru:</strong> {{ $p?->jumlah_guru ?? '-' }}</p>
    @if ($p?->deskripsi)
        <p class="pt-2">{{ $p->deskripsi }}</p>
    @endif
    <p class="text-sm text-gray-500 pt-2">
        Koordinat: {{ $sekolah->latitude }}, {{ $sekolah->longitude }}
    </p>
</div>
@endsection