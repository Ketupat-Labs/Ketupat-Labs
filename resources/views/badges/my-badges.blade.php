@extends('layouts.app')

@section('title', 'My Badges')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">My Badges Collection</h1>
    
    <!-- Auto-refresh button -->
    <div class="alert alert-info d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-sync-alt"></i> 
            Data will refresh automatically every 30 seconds
        </div>
        <button onclick="refreshData()" class="btn btn-sm btn-warning">
            <i class="fas fa-redo"></i> Refresh Now
        </button>
    </div>
    
    <!-- Stats -->
    <div class="row mb-4" id="statsSection">
        @include('partials.badge-stats', ['stats' => $stats])
    </div>
    
    <!-- Badges List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">My Badges</h5>
        </div>
        <div class="card-body" id="badgesList">
            @if($badges->count() > 0)
            <div class="row">
                @foreach($badges as $badge)
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100" style="border-top: 5px solid {{ $badge->color }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="card-title">{{ $badge->name }}</h6>
                                <span class="badge bg-{{ $badge->badge_status == 'approved' ? 'success' : 'warning' }}">
                                    {{ ucfirst($badge->badge_status) }}
                                </span>
                            </div>
                            <p class="card-text text-muted small">
                                <i class="fas fa-tag"></i> {{ $badge->category_name }}
                            </p>
                            <p class="card-text">
                                <i class="fas fa-star text-warning"></i> 
                                {{ $badge->xp_reward }} XP
                            </p>
                            <p class="card-text small text-muted">
                                <i class="fas fa-calendar"></i> 
                                {{ \Carbon\Carbon::parse($badge->obtained_at)->format('d M Y H:i') }}
                            </p>
                            @if($badge->given_by)
                            <p class="card-text small">
                                <i class="fas fa-user-check"></i> 
                                By: {{ $badge->given_by }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-award fa-3x text-muted mb-3"></i>
                <h5>No badges yet</h5>
                <p>You haven't earned any badges yet</p>
                <a href="{{ route('badges.index') }}" class="btn btn-primary">Browse Badges</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto refresh data every 30 seconds
setInterval(refreshData, 30000);

function refreshData() {
    // Show loading
    document.getElementById('statsSection').innerHTML = 
        '<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    document.getElementById('badgesList').innerHTML = 
        '<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    // Fetch fresh data
    fetch('/api/my-badges-data')
        .then(response => response.json())
        .then(data => {
            // Update stats
            document.getElementById('statsSection').innerHTML = data.stats_html;
            
            // Update badges list
            document.getElementById('badgesList').innerHTML = data.badges_html;
            
            // Show notification
            showNotification('Data refreshed at ' + new Date().toLocaleTimeString());
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error refreshing data', 'error');
        });
}

function showNotification(message, type = 'success') {
    // Create notification
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show fixed-top mt-5 mx-3`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alert);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        alert.remove();
    }, 3000);
}
</script>
@endpush