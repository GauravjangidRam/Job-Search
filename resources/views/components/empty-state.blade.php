@props(['message'])


<div {{ $attributes->merge(['class' => 'flex items-center justify-center py-12 px-6']) }}>
    <p class="text-gray-500 text-center text-lg">
        {{ $message }} 
    </p> 
</div>