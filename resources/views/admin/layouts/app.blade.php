<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị') · MommyKids Admin</title>
    @vite(['resources/css/app.css', 'resources/js/admin.js'])
    @stack('styles')
</head>
<body class="bg-admin-bg text-ink font-body antialiased">

    <div class="flex min-h-screen">

        {{-- ============ SIDEBAR ============ --}}
        @include('admin.partials.sidebar')

        <div id="admin-overlay" class="hidden fixed inset-0 bg-ink/40 z-40 lg:hidden"></div>

        {{-- ============ MAIN COLUMN ============ --}}
        <div class="flex-1 min-w-0 lg:ml-64">
            @include('admin.partials.topbar')

            <main class="p-4 lg:p-6 space-y-6">
                {{-- Page header slot: title + breadcrumb + action button --}}
                @hasSection('page_header')
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h1 class="font-display font-bold text-xl text-ink">@yield('page_title')</h1>
                            @hasSection('page_subtitle')
                                <p class="text-sm text-ink-soft mt-0.5">@yield('page_subtitle')</p>
                            @endif
                        </div>
                        <div>@yield('page_actions')</div>
                    </div>
                @endif

                @if (session('success'))
                    <div class="rounded-xl bg-mint-light text-mint font-medium text-sm px-4 py-3">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="rounded-xl bg-coral-light text-coral-dark font-medium text-sm px-4 py-3">{{ session('error') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
