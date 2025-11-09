@props([
    'title' => '',
    'icon' => 'document-text',
    'iconColor' => 'lime',
    'completionPercentage' => 0,
    'completionText' => '0/0',
    'statusItems' => [],
    'buttonText' => 'View Details',
    'buttonAction' => 'view',
    'buttonVariant' => 'outline',
    'modalName' => '',
])

@php
    // Color mapping for icons and badges
    $colorClasses = [
        'lime' => [
            'iconBg' => 'bg-lime-100 dark:bg-lime-900',
            'iconText' => 'text-lime-600 dark:text-lime-400',
            'badgeBg' => 'bg-green-100 dark:bg-green-900',
            'badgeText' => 'text-green-800 dark:text-green-200',
            'progressBar' => 'bg-green-500',
            'buttonBorder' => 'border-lime-500 dark:border-lime-400',
            'buttonText' => 'text-lime-600 dark:text-lime-400',
            'buttonHover' => 'hover:bg-lime-50 dark:hover:bg-lime-900/20',
            'solidButton' => 'bg-lime-500 hover:bg-lime-600',
        ],
        'blue' => [
            'iconBg' => 'bg-blue-100 dark:bg-blue-900',
            'iconText' => 'text-blue-600 dark:text-blue-400',
            'badgeBg' => 'bg-blue-100 dark:bg-blue-900',
            'badgeText' => 'text-blue-800 dark:text-blue-200',
            'progressBar' => 'bg-blue-500',
            'buttonBorder' => 'border-blue-500 dark:border-blue-400',
            'buttonText' => 'text-blue-600 dark:text-blue-400',
            'buttonHover' => 'hover:bg-blue-50 dark:hover:bg-blue-900/20',
            'solidButton' => 'bg-blue-500 hover:bg-blue-600',
        ],
        'amber' => [
            'iconBg' => 'bg-amber-100 dark:bg-amber-900',
            'iconText' => 'text-amber-600 dark:text-amber-400',
            'badgeBg' => 'bg-amber-100 dark:bg-amber-900',
            'badgeText' => 'text-amber-800 dark:text-amber-200',
            'progressBar' => 'bg-amber-500',
            'buttonBorder' => 'border-amber-500 dark:border-amber-400',
            'buttonText' => 'text-amber-600 dark:text-amber-400',
            'buttonHover' => 'hover:bg-amber-50 dark:hover:bg-amber-900/20',
            'solidButton' => 'bg-amber-500 hover:bg-amber-600',
        ],
        'purple' => [
            'iconBg' => 'bg-purple-100 dark:bg-purple-900',
            'iconText' => 'text-purple-600 dark:text-purple-400',
            'badgeBg' => 'bg-purple-100 dark:bg-purple-900',
            'badgeText' => 'text-purple-800 dark:text-purple-200',
            'progressBar' => 'bg-purple-500',
            'buttonBorder' => 'border-purple-500 dark:border-purple-400',
            'buttonText' => 'text-purple-600 dark:text-purple-400',
            'buttonHover' => 'hover:bg-purple-50 dark:hover:bg-purple-900/20',
            'solidButton' => 'bg-purple-500 hover:bg-purple-600',
        ],
        'red' => [
            'iconBg' => 'bg-red-100 dark:bg-red-900',
            'iconText' => 'text-red-600 dark:text-red-400',
            'badgeBg' => 'bg-red-100 dark:bg-red-900',
            'badgeText' => 'text-red-800 dark:text-red-200',
            'progressBar' => 'bg-red-500',
            'buttonBorder' => 'border-red-500 dark:border-red-400',
            'buttonText' => 'text-red-600 dark:text-red-400',
            'buttonHover' => 'hover:bg-red-50 dark:hover:bg-red-900/20',
            'solidButton' => 'bg-red-500 hover:bg-red-600',
        ],
    ];

    $colors = $colorClasses[$iconColor] ?? $colorClasses['lime'];

    // Button configuration
    $buttonIcons = [
        'view' => 'eye',
        'edit' => 'pencil-square',
        'add' => 'plus-circle',
        'complete' => 'check-circle',
        'upload' => 'cloud-arrow-up',
    ];

    $buttonIcon = $buttonIcons[$buttonAction] ?? 'eye';
@endphp

<div
    {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700']) }}>
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 {{ $colors['iconBg'] }} rounded-full flex items-center justify-center">
                <flux:icon name="{{ $icon }}" class="w-5 h-5 {{ $colors['iconText'] }}" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
        </div>
        <span class="px-2 py-1 {{ $colors['badgeBg'] }} {{ $colors['badgeText'] }} text-xs rounded-full">
            {{ $completionPercentage }}%
        </span>
    </div>

    <!-- Progress Bar -->
    <div class="mb-4">
        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
            <span>Completion</span>
            <span>{{ $completionText }}</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
            <div class="h-2 rounded-full {{ $colors['progressBar'] }}" style="width: {{ $completionPercentage }}%">
            </div>
        </div>
    </div>

    <!-- Status Items -->
    <div class="space-y-2">
        @foreach ($statusItems as $item)
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">{{ $item['label'] }}</span>
                @if ($item['status'] === 'completed')
                    <flux:icon name="check-circle" class="w-4 h-4 text-green-500" />
                @elseif($item['status'] === 'pending')
                    <flux:icon name="clock" class="w-4 h-4 text-amber-500" />
                @elseif($item['status'] === 'in-progress')
                    <flux:icon name="arrow-path" class="w-4 h-4 text-blue-500" />
                @else
                    <div class="w-4 h-4">
                        <x-placeholder-pattern class="w-full h-full text-gray-400" />
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Action Button -->
    @if ($buttonVariant === 'outline')
        <flux:modal.trigger name="{{ $modalName }}">
            <flux:button type="submit"
                class="w-full mt-4 px-4 py-2 border {{ $colors['buttonBorder'] }} {{ $colors['buttonText'] }} rounded-lg {{ $colors['buttonHover'] }} transition-colors cursor-pointer">
                <flux:icon name="{{ $buttonIcon }}" class="w-4 h-4 inline mr-2" />
                {{ $buttonText }}
            </flux:button>
        </flux:modal.trigger>
    @else
        <flux:modal.trigger name="{{ $modalName }}">
            <flux:button type="submit"
                class="w-full mt-4 px-4 py-2 {{ $colors['solidButton'] }} text-white rounded-lg  transition-colors cursor-pointer">
                <flux:icon name="{{ $buttonIcon }}" class="w-4 h-4 inline mr-2" />
                {{ $buttonText }}
            </flux:button>
        </flux:modal.trigger>
    @endif

    {{ $slot }}
</div>
