@php
$pageLinkClasses = 'inline-flex size-9 items-center justify-center rounded-lg border text-sm font-medium transition';
$pageLinkAvailable = 'border-comb bg-white/70 text-zinc-600 hover:border-honey-deep/40 hover:text-ink dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:border-honey/40 dark:hover:text-honey';
$pageLinkCurrent = 'border-honey bg-honey text-ink';
$pageLinkDisabled = 'border-comb/70 bg-white/40 text-zinc-400 dark:border-zinc-800 dark:bg-zinc-900/40 dark:text-zinc-500';
$arrowClasses = 'inline-flex min-h-9 items-center justify-center gap-1.5 rounded-lg border px-3 py-1.5 text-sm font-medium transition';
@endphp

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-wrap items-center justify-between gap-3">
        <p class="hidden text-sm text-zinc-600 sm:block dark:text-zinc-400">
            {!! __('Showing') !!}
            @if ($paginator->firstItem())
                <span class="font-medium text-ink dark:text-zinc-100">{{ $paginator->firstItem() }}</span>
                {!! __('to') !!}
                <span class="font-medium text-ink dark:text-zinc-100">{{ $paginator->lastItem() }}</span>
            @else
                <span class="font-medium text-ink dark:text-zinc-100">{{ $paginator->count() }}</span>
            @endif
            {!! __('of') !!}
            <span class="font-medium text-ink dark:text-zinc-100">{{ $paginator->total() }}</span>
            {!! __('results') !!}
        </p>

        <div class="ms-auto flex flex-wrap items-center gap-1.5">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true">
                    <span class="{{ $arrowClasses }} {{ $pageLinkDisabled }}">
                        <span class="sr-only">{{ __('Previous page') }}</span>
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $arrowClasses }} {{ $pageLinkAvailable }}" aria-label="{{ __('pagination.previous') }}">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span aria-hidden="true" class="px-1 text-zinc-400 dark:text-zinc-500">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="{{ $pageLinkClasses }} {{ $pageLinkCurrent }}">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="{{ $pageLinkClasses }} {{ $pageLinkAvailable }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $arrowClasses }} {{ $pageLinkAvailable }}" aria-label="{{ __('pagination.next') }}">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <span aria-disabled="true">
                    <span class="{{ $arrowClasses }} {{ $pageLinkDisabled }}">
                        <span class="sr-only">{{ __('Next page') }}</span>
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </span>
            @endif
        </div>
    </nav>
@endif
