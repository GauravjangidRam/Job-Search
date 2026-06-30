@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-4xl mx-auto pt-24 px-6 pb-12">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-foreground">Notifications</h1>
            
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form method="POST" action="{{ route('notifications.markAllAsRead') }}">
                    @csrf
                    <button type="submit" class="text-sm text-primary hover:underline font-medium focus:outline-none">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>
        <div class="space-y-4">
            @forelse($notifications as $notification)
                <div class="relative bg-card border {{ $notification->read_at ? 'border-border/50' : 'border-primary/30 shadow-sm' }} rounded-[var(--radius-card)] p-6 transition-colors group">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4 flex-1">
                            {{-- Icon based on notification type --}}
                            <div class="shrink-0 mt-1">
                                @if($notification->data['type'] === 'application_received')
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                        <i data-lucide="file-text" class="w-5 h-5"></i>
                                    </div>
                                @elseif($notification->data['type'] === 'application_status_updated')
                                    <div class="w-10 h-10 rounded-full {{ $notification->data['status'] === 'rejected' ? 'bg-red-100 text-red-600' : ($notification->data['status'] === 'shortlisted' ? 'bg-green-100 text-green-600' : 'bg-primary/10 text-primary') }} flex items-center justify-center">
                                        <i data-lucide="{{ $notification->data['status'] === 'rejected' ? 'x-circle' : ($notification->data['status'] === 'shortlisted' ? 'check-circle' : 'clock') }}" class="w-5 h-5"></i>
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-full bg-secondary flex items-center justify-center text-muted">
                                        <i data-lucide="bell" class="w-5 h-5"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium {{ $notification->read_at ? 'text-foreground/80' : 'text-foreground' }}">
                                    {{ $notification->data['message'] ?? 'You have a new notification.' }}
                                </p>
                                
                                @if(isset($notification->data['job_title']))
                                    <p class="text-xs text-muted mt-1">Job: {{ $notification->data['job_title'] }}</p>
                                @endif
                                
                                <p class="text-xs text-muted mt-2 flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>

                        {{-- Mark as read button --}}
                        @if(!$notification->read_at)
                            <form method="POST" action="{{ route('notifications.markAsRead', $notification->id) }}" class="shrink-0">
                                @csrf
                                <button type="submit" class="p-2 text-primary/60 hover:text-primary rounded-full hover:bg-primary/10 transition-colors focus:outline-none" title="Mark as read">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div> 
            @empty
                <div class="bg-card border border-border rounded-[var(--radius-card)] p-12 text-center">
                    <div class="w-16 h-16 mx-auto bg-secondary rounded-full flex items-center justify-center text-muted mb-4">
                        <i data-lucide="bell-off" class="w-8 h-8"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-foreground mb-2">No notifications yet</h2>
                    <p class="text-muted">When you get updates about applications or jobs, they'll show up here.</p>
                </div>
            @endforelse
        </div>
        @if($notifications->hasPages())
            <div class="mt-8">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection