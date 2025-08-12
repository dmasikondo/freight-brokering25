@props([
    'step', 
    'icon', 
    'title', 
    'description', 
    'usageTitle', 
    'items' => [], 
    'class' => '', 
    'disabled' => false,
])

<div x-show="currentStep === {{ $step }}" {{ $disabled ? 'disabled' : '' }} class="space-y-3 {{ $class }}">
    <div class="flex items-center gap-2">
        <x-graphic :name="$icon" class="size-5 text-blue-400"/>
        <h3 class="text-lg font-semibold">{{ $title }}</h3>
    </div>
    <p class="text-sm text-gray-300">{{ $description }}</p>
    <div class="bg-gray-700/50 p-3 rounded-lg text-xs space-y-1">
        <p class="text-gray-400">{{ $usageTitle }}</p>
        @foreach ($items as $item)
            <div class="flex items-center gap-1.5 text-gray-300">
                <x-graphic name="verifiedTick" class="size-3.5 text-green-400"/>
                {{ $item }}
            </div>
        @endforeach
    </div>
</div>