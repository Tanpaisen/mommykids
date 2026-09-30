@extends('client.layouts.app')

@section('title', $currentArticle->title ?? 'Cẩm Nang Cho Mẹ Và Bé')

{{-- ============ SIDEBAR CẨM NANG (Sẽ ghi đè Sidebar mặc định) ============ --}}
@section('sidebar')
    <aside class="hidden lg:block w-72 bg-white border border-gray-200 p-6 rounded-2xl overflow-y-auto sticky top-4 shrink-0" style="max-height: calc(100vh - 2rem);">
        <a href="{{ route('handbook.show') }}" class="font-bold text-xl text-[#FF2A54] block mb-6">
            MommyKids Handbook
        </a>

        <div class="space-y-6">
            @foreach($chapters as $chapter)
                <div>
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-2">
                        📖 {{ $chapter->name }}
                    </h3>
                    
                    <div class="space-y-3 ml-2 border-l-2 border-gray-100 pl-3">
                        @foreach($chapter->children as $subCategory)
                            <div>
                                <p class="text-xs font-semibold text-gray-500 mb-1">📁 {{ $subCategory->name }}</p>
                                <ul class="space-y-1 ml-2">
                                    @foreach($subCategory->articles as $art)
                                        <li>
                                            <a href="{{ route('handbook.show', $art->slug) }}" 
                                               class="block text-sm py-1 px-2 rounded-md transition-colors {{ isset($currentArticle) && $currentArticle->id === $art->id ? 'bg-red-50 text-[#FF2A54] font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
                                                📄 {{ $art->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </aside>
@endsection

{{-- ============ NỘI DUNG BÀI VIẾT ============ --}}
@section('content')
    <div class="bg-white rounded-2xl p-6 lg:p-10 shadow-sm border border-slate-200/80 min-h-[80vh]">
        @if($currentArticle)
            <article class="max-w-3xl mx-auto">
                <!-- Danh mục nhỏ -->
                <span class="text-xs font-bold text-[#FF2A54] uppercase tracking-wider bg-red-50 px-2.5 py-1 rounded-full">
                    {{ $currentArticle->handbookCategory->name ?? $currentArticle->category->name ?? 'Cẩm nang' }}
                </span>

                <h1 class="text-3xl font-extrabold text-gray-900 mt-4 mb-6 leading-tight">
                    {{ $currentArticle->title }}
                </h1>

                @if($currentArticle->summary)
                    <div class="p-4 bg-gray-50 rounded-xl border-l-4 border-[#FF2A54] text-gray-600 mb-8 italic">
                        {{ $currentArticle->summary }}
                    </div>
                @endif

                <!-- Nội dung bài viết -->
                <div class="prose max-w-none text-gray-700 leading-relaxed space-y-4">
                    {!! $currentArticle->content !!}
                </div>

                <!-- Thanh điều hướng Bài trước / Bài tiếp theo -->
                <div class="mt-12 pt-6 border-t border-gray-200 flex items-center justify-between">
                    @if(isset($prevArticle) && $prevArticle)
                        <a href="{{ route('handbook.show', $prevArticle->slug) }}" class="text-sm font-semibold text-gray-600 hover:text-[#FF2A54] transition-colors">
                            ← Bài trước: {{ Str::limit($prevArticle->title, 30) }}
                        </a>
                    @else
                        <div></div>
                    @endif

                    @if(isset($nextArticle) && $nextArticle)
                        <a href="{{ route('handbook.show', $nextArticle->slug) }}" class="text-sm font-semibold text-gray-600 hover:text-[#FF2A54] transition-colors">
                            Bài tiếp theo: {{ Str::limit($nextArticle->title, 30) }} →
                        </a>
                    @endif
                </div>
            </article>
        @else
            <div class="text-center py-20 text-gray-400">
                <p class="text-lg">Chưa có bài viết cẩm nang nào.</p>
            </div>
        @endif
    </div>
@endsection