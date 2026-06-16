@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Notifications</h2>
    @if(auth()->user()->notifications()->whereNull('read_at')->count() > 0)
    <form action="{{ route('notifications.read-all') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-check-all me-1"></i>Mark All as Read
        </button>
    </form>
    @endif
</div>

<!-- Notifications List -->
<div class="card shadow-sm">
    <div class="list-group list-group-flush">
        @forelse($notifications as $notification)
        <div class="list-group-item p-3 {{ $notification->read_at ? '' : 'bg-light border-start border-4 border-primary' }}">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h6 class="mb-1 fw-bold {{ $notification->read_at ? 'text-secondary' : 'text-dark' }}">
                        {{ $notification->title }}
                    </h6>
                    <p class="mb-1 text-secondary small">{{ $notification->message }}</p>
                    <small class="text-muted fs-8">
                        <i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                    </small>
                </div>
                
                @if(!$notification->read_at)
                <form action="{{ route('notifications.read', $notification) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline-primary btn-sm py-0 px-2 fs-8" title="Mark as Read">
                        <i class="bi bi-check"></i> Read
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-bell-slash fs-1 d-block mb-3"></i>
            You have no notifications.
        </div>
        @endforelse
    </div>
    @if($notifications->hasPages())
    <div class="card-footer bg-white">
        {{ $notifications->links() }}
    </div>
    @endif
</div>
@endsection
