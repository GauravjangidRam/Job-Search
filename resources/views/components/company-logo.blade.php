@props(['name', 'logo_url' => null, 'size' => 48])

@php
    // Generate initials: first letter of each word, up to 2 characters
    $words = preg_split('/\s+/', trim($name));
    $initials = '';
    foreach ($words as $word) {
        if ($initials === '' || strlen($initials) < 2) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }
    }
    $initials = mb_substr($initials, 0, 2);
    // Deterministic background color from company name
    $colorHex = str_pad(dechex(crc32($name) & 0xFFFFFF), 6, '0', STR_PAD_LEFT);

    // Build the UI Avatars placeholder URL
    $placeholderUrl = 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&background=' . $colorHex . '&color=fff&size=' . $size;
@endphp
 
@if($logo_url)
    <img
        src="{{ $logo_url }}"
        alt="{{ $name }} logo"
        width="{{ $size }}"
        height="{{ $size }}"
        class="object-contain"
        onerror="this.onerror=null;this.src='{{ $placeholderUrl }}';"
        {{ $attributes }}
    >
@else
    <img
        src="{{ $placeholderUrl }}"
        alt="{{ $name }} logo"
        width="{{ $size }}"
        height="{{ $size }}"
        class="object-contain"
        {{ $attributes }}
    >
@endif
