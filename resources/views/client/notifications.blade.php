@extends('client.layouts.app')

@section('title', 'Thông báo - MommyKids')

@section('content')
    <section class="card p-4 lg:p-6">
        <h1 class="font-display font-bold text-xl text-ink mb-5">Thông báo</h1>
        @if (empty($notifications))
            <p class="text-ink-soft text-sm py-10 text-center">Bạn chưa có thông báo nào.</p>
        @endif
    </section>
@endsection
