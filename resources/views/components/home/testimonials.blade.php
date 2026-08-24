@props(['testimonials'])

<section aria-labelledby="testimonials-heading">
    <div class="text-center max-w-xl mx-auto mb-10">
        <h2 id="testimonials-heading" class="text-2xl md:text-3xl font-extrabold text-foreground tracking-tight">What Our Users Say</h2>
        <p class="text-muted text-sm mt-1">Real stories from job seekers and employers</p>
    </div>

    @if($testimonials->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($testimonials as $testimonial)
                @php
                    $initials = collect(explode(' ', $testimonial->name))
                        ->map(fn($word) => mb_substr($word, 0, 1))
                        ->take(2)
                        ->implode('');
                @endphp
                <div class="bg-card border border-border/80 rounded-2xl p-6 flex flex-col shadow-xs hover:border-primary/40 hover:shadow-md transition-all">
                    {{-- Star Rating --}}
                    <div class="flex items-center gap-1 mb-4" role="img" aria-label="Rating: {{ $testimonial->rating }} out of 5 stars">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="w-4 h-4 {{ $i <= $testimonial->rating ? 'text-amber-400 fill-amber-400' : 'text-gray-300' }}" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endfor
                    </div>

                    {{-- Testimonial Text --}}
                    <p class="text-sm text-foreground/90 leading-relaxed mb-6 flex-1 italic">
                        "{{ Str::limit($testimonial->text, 200) }}"
                    </p>

                    {{-- User Info --}}
                    <div class="flex items-center gap-3 pt-4 border-t border-border/70">
                        @if($testimonial->avatar_url)
                            <img
                                src="{{ $testimonial->avatar_url }}"
                                alt="{{ $testimonial->name }}"
                                class="w-10 h-10 rounded-full object-cover border border-border/60 shadow-2xs"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                            >
                            <div class="w-10 h-10 rounded-full bg-primary text-white items-center justify-center text-xs font-bold shadow-2xs" style="display:none;" aria-hidden="true">{{ $initials }}</div>
                        @else
                            <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold shadow-2xs" aria-hidden="true">{{ $initials }}</div>
                        @endif
                        <div>
                            <p class="text-sm font-bold text-foreground">{{ $testimonial->name }}</p>
                            <p class="text-xs text-muted font-medium">{{ $testimonial->role }}@if($testimonial->company) at {{ $testimonial->company }}@endif</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-card border border-border/80 rounded-2xl p-10 text-center shadow-xs">
            <p class="text-muted text-sm font-medium">No testimonials yet.</p>
        </div>
    @endif
</section>
