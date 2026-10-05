@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
    'icon' => null,
    'title' => null,
])

@php
    $classes = trim('orbo-button orbo-button-' . $variant . ' ' . ($attributes->get('class') ?? ''));
    $attributes = $attributes->except('class');
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $classes }}" @if($title) title="{{ $title }}" @endif {{ $attributes }}>
        @if($icon)<i class="{{ $icon }}" aria-hidden="true"></i>@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" class="{{ $classes }}" @if($title) title="{{ $title }}" @endif {{ $attributes }}>
        @if($icon)<i class="{{ $icon }}" aria-hidden="true"></i>@endif
        {{ $slot }}
    </button>
@endif
