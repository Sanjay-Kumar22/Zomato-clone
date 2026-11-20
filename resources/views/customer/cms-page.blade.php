@extends('customer.layouts.app')
@section('title', $page->title)
@section('content')
<div class="container py-5" style="max-width:900px">
    <a class="text-decoration-none text-danger small fw-semibold" href="{{ route('customer.dashboard') }}"><i class="bi bi-arrow-left me-1"></i>Home</a>
    <article class="card mt-3"><div class="card-body p-4 p-md-5">
        <h1 class="fw-bold mb-4">{{ $page->title }}</h1>
        <div class="text-secondary" style="white-space:pre-line;line-height:1.85">{{ $page->body }}</div>
    </div></article>
</div>
@endsection
