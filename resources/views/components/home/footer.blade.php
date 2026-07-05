@props(['footerLinks'])

<footer aria-labelledby="footer-heading">
    <h2 id="footer-heading" class="sr-only">Footer</h2>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        {{-- Navigation Link Columns --}}
        @foreach ($footerLinks as $column)
            <div>
                <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">
                    {{ $column->heading }}
                </h3>  
                <ul class="space-y-3">
                    @foreach ($column->links as $link)
                        <li>
                            <a href="{{ $link['url'] }}" class="text-sm text-muted hover:text-primary transition-colors focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach 

        {{-- Social Media & Brand Column --}}
        <div>
            <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">
                Connect With Us
            </h3>
            <div class="flex items-center gap-4">
                <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" aria-label="Follow us on Twitter" class="text-muted hover:text-primary transition-colors focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">
                    <i data-lucide="twitter" class="w-5 h-5"></i>
                </a>
                <a href="https://github.com" target="_blank" rel="noopener noreferrer" aria-label="View our GitHub" class="text-muted hover:text-primary transition-colors focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">
                    <i data-lucide="github" class="w-5 h-5"></i>
                </a>
                <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" aria-label="Connect on LinkedIn" class="text-muted hover:text-primary transition-colors focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">
                    <i data-lucide="linkedin" class="w-5 h-5"></i>
                </a>
                <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Follow us on Instagram" class="text-muted hover:text-primary transition-colors focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">
                    <i data-lucide="instagram" class="w-5 h-5"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Copyright --}}
    <div class="mt-8 pt-8 border-t border-border">
        <p class="text-sm text-muted text-center">
            &copy; {{ date('Y') }} Job Hub. All rights reserved.
        </p>
    </div>
</footer>
