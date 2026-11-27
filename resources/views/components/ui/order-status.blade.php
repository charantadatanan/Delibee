@props(['status'])

@php
    $status = $status instanceof \App\Enums\OrderStatus
        ? $status
        : \App\Enums\OrderStatus::from($status);

    [$dot, $classes] = match ($status) {
        \App\Enums\OrderStatus::Delivered => ['bg-[#4E7A39]', 'border-[#7BA663] bg-[#E4F2DA] text-[#315D22]'],
        \App\Enums\OrderStatus::Cancelled => ['bg-[#9A4B4B]', 'border-[#C99A9A] bg-[#F6E7E7] text-[#7A2E2E]'],
        \App\Enums\OrderStatus::Shipped, \App\Enums\OrderStatus::OutForDelivery => ['bg-[#3C6EA5]', 'border-[#8FB4D9] bg-[#E4EFFB] text-[#1C4E8A]'],
        \App\Enums\OrderStatus::Confirmed, \App\Enums\OrderStatus::Processing => ['bg-[#B77C00]', 'border-[#D9AE55] bg-[#FFF1CE] text-[#6A4700]'],
        default => ['bg-[#8A6A00]', 'border-[#D9C27E] bg-[#FFF6D9] text-[#6B5200]'],
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold '.$classes]) }}>
    <span class="size-1.5 rounded-full {{ $dot }}" aria-hidden="true"></span>
    {{ $status->label() }}
</span>
