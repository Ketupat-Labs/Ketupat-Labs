@extends('layouts.app')

@section('title', 'My Badges Dashboard')

@section('content')
<div class="container-fluid px-4">
    <!-- Dashboard Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold mb-1">🎖️ My Achievements</h1>
            <p class="text-muted mb-0">Track your learning progress and earned badges</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('badges.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-award me-2"></i>Browse All Badges
            </a>
        </div>
    </div>

    <!-- Dashboard Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 bg-gradient-primary text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Total XP Earned</h6>
                            <h2 class="fw-bold mb-0">{{ $stats['total_xp'] }}</h2>
                        </div>
                        <div class="icon-shape bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="fas fa-coins fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small><i class="fas fa-arrow-up me-1"></i> Your total achievement points</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 bg-gradient-success text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Badges Redeemed</h6>
                            <h2 class="fw-bold mb-0">{{ $stats['total_redeemed'] }}</h2>
                        </div>
                        <div class="icon-shape bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small><i class="fas fa-check me-1"></i> Successfully redeemed</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 bg-gradient-warning text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Ready to Redeem</h6>
                            <h2 class="fw-bold mb-0">{{ $stats['total_earned'] }}</h2>
                        </div>
                        <div class="icon-shape bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="fas fa-star fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small><i class="fas fa-gift me-1"></i> Earned but not redeemed</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 bg-gradient-info text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">In Progress</h6>
                            <h2 class="fw-bold mb-0">{{ $stats['in_progress'] }}</h2>
                        </div>
                        <div class="icon-shape bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="fas fa-spinner fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small><i class="fas fa-clock me-1"></i> Working towards completion</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="row g-4">
        <!-- Left Column: Filters and Progress -->
        <div class="col-xl-4">
            <!-- Filters Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-filter me-2"></i>Filter Badges</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('badges.my') }}" id="filterForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Category</label>
                            <select name="category" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                <option value="all" {{ $category == 'all' ? 'selected' : '' }}>All Categories</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->code }}" {{ $category == $cat->code ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Status</label>
                            <select name="status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="redeemed" {{ $status == 'redeemed' ? 'selected' : '' }}>Redeemed</option>
                                <option value="earned" {{ $status == 'earned' ? 'selected' : '' }}>Ready to Redeem</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Sort By</label>
                            <select name="sort" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                <option value="recent" {{ $sort == 'recent' ? 'selected' : '' }}>Most Recent</option>
                                <option value="xp" {{ $sort == 'xp' ? 'selected' : '' }}>Highest XP</option>
                                <option value="name" {{ $sort == 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                                <option value="category" {{ $sort == 'category' ? 'selected' : '' }}>Category</option>
                                <option value="level" {{ $sort == 'level' ? 'selected' : '' }}>Difficulty Level</option>
                            </select>
                        </div>
                        <div class="d-grid">
                            <a href="{{ route('badges.my') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Reset Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Progress Overview -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-chart-line me-2"></i>Progress Overview</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Overall Progress</span>
                            <span class="fw-bold small">{{ $stats['total_redeemed'] + $stats['total_earned'] }}/{{ $stats['total_badges'] }}</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            @php
                                $progressPercentage = $stats['total_badges'] > 0 
                                    ? round((($stats['total_redeemed'] + $stats['total_earned']) / $stats['total_badges']) * 100)
                                    : 0;
                            @endphp
                            <div class="progress-bar bg-gradient-primary" style="width: {{ $progressPercentage }}%"></div>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <div class="text-success fw-bold fs-4">{{ $stats['total_redeemed'] }}</div>
                                <div class="text-muted small">Redeemed</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <div class="text-warning fw-bold fs-4">{{ $stats['total_earned'] }}</div>
                                <div class="text-muted small">Ready</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <div class="text-info fw-bold fs-4">{{ $stats['in_progress'] }}</div>
                                <div class="text-muted small">In Progress</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <div class="text-secondary fw-bold fs-4">
                                    {{ $stats['total_badges'] - ($stats['total_redeemed'] + $stats['total_earned'] + $stats['in_progress']) }}
                                </div>
                                <div class="text-muted small">Not Started</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Badges Display -->
        <div class="col-xl-8">
            <!-- Tabs Navigation -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <ul class="nav nav-tabs card-header-tabs" id="badgeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="redeemed-tab" data-bs-toggle="tab" data-bs-target="#redeemed" type="button" role="tab">
                                <i class="fas fa-check-circle me-2"></i>Redeemed ({{ $redeemedBadges->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="earned-tab" data-bs-toggle="tab" data-bs-target="#earned" type="button" role="tab">
                                <i class="fas fa-star me-2"></i>Ready to Redeem ({{ $earnedBadges->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="progress-tab" data-bs-toggle="tab" data-bs-target="#progress" type="button" role="tab">
                                <i class="fas fa-spinner me-2"></i>In Progress ({{ $stats['in_progress'] }})
                            </button>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body p-0">
                    <div class="tab-content" id="badgeTabsContent">
                        <!-- Redeemed Tab -->
                        <div class="tab-pane fade show active" id="redeemed" role="tabpanel">
                            @if($redeemedBadges->count() > 0)
                            <div class="row row-cols-1 row-cols-md-2 g-4 p-4">
                                @foreach($redeemedBadges as $badge)
                                <div class="col">
                                    <div class="card border border-success border-2 h-100 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start gap-3 mb-3">
                                                <div class="position-relative">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                                         style="width: 50px; height: 50px; background: linear-gradient(135deg, {{ $badge->category_color ?? '#10B981' }}, #059669);">
                                                        <i class="fas fa-award text-white"></i>
                                                    </div>
                                                    <div class="position-absolute bottom-0 end-0 bg-success rounded-circle p-1 border border-2 border-white">
                                                        <i class="fas fa-check text-white" style="font-size: 0.7rem;"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="fw-bold mb-1">{{ $badge->name }}</h6>
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <span class="badge" style="background-color: {{ $badge->category_color ?? '#6B7280' }}; color: white">
                                                            {{ $badge->category_name }}
                                                        </span>
                                                        <span class="badge bg-{{ $badge->level == 'Beginner' ? 'success' : ($badge->level == 'Intermediate' ? 'warning' : 'danger') }}">
                                                            {{ $badge->level }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <p class="small text-muted mb-3">{{ $badge->description }}</p>
                                            
                                            <div class="d-flex justify-content-between align-items-center mt-4">
                                                <div>
                                                    <small class="text-muted">Redeemed on</small>
                                                    <div class="fw-bold">{{ date('M d, Y', strtotime($badge->redeemed_at)) }}</div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold text-success">+{{ $badge->xp_reward }} XP</div>
                                                    <small class="text-muted">Points earned</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="text-center py-8">
                                <div class="mb-4">
                                    <i class="fas fa-trophy fa-4x text-muted"></i>
                                </div>
                                <h5 class="fw-bold mb-2">No badges redeemed yet</h5>
                                <p class="text-muted mb-4">Earn badges and redeem them to see them here</p>
                                <a href="{{ route('badges.index') }}" class="btn btn-primary">
                                    <i class="fas fa-award me-2"></i>Browse Badges
                                </a>
                            </div>
                            @endif
                        </div>

                        <!-- Add to tabs navigation -->
<li class="nav-item" role="presentation">
    <button class="nav-link" id="locked-tab" data-bs-toggle="tab" data-bs-target="#locked" type="button" role="tab">
        <i class="fas fa-lock me-2"></i>Locked ({{ $stats['locked'] ?? 0 }})
    </button>
</li>

<!-- Add tab content -->
<div class="tab-pane fade" id="locked" role="tabpanel">
    @php
        $lockedBadges = $allBadges->where('progress_percentage', 0);
    @endphp
    
    @if($lockedBadges->count() > 0)
    <div class="p-4">
        <div class="alert alert-secondary mb-4">
            <i class="fas fa-lock me-2"></i>
            <strong>Complete requirements to unlock these badges</strong>
        </div>
        
        <div class="row row-cols-1 row-cols-md-2 g-4">
            @foreach($lockedBadges as $badge)
            <div class="col">
                <div class="card border border-secondary h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary text-white"
                                style="width: 50px; height: 50px;">
                                <i class="fas fa-lock"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">{{ $badge->name }}</h6>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge" style="background-color: {{ $badge->color ?? '#6B7280' }}; color: white">
                                        {{ $badge->category_name }}
                                    </span>
                                    <span class="badge bg-secondary">{{ $badge->level }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <p class="small text-muted mb-3">{{ $badge->description }}</p>
                        
                        <div class="text-center">
                            <a href="{{ route('badges.index') }}?search={{ urlencode($badge->name) }}" 
                            class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-play me-1"></i>Start Earning
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="text-center py-8">
        <div class="mb-4">
            <i class="fas fa-check-circle fa-4x text-success"></i>
        </div>
        <h5 class="fw-bold mb-2">No locked badges!</h5>
        <p class="text-muted mb-4">You've started earning all available badges</p>
    </div>
    @endif
</div>

                        <!-- Earned (Ready to Redeem) Tab -->
                        <div class="tab-pane fade" id="earned" role="tabpanel">
                            @if($earnedBadges->count() > 0)
                            <div class="p-4">
                                <div class="alert alert-warning mb-4">
                                    <i class="fas fa-gift me-2"></i>
                                    <strong>You have {{ $earnedBadges->count() }} badges ready to redeem!</strong> 
                                    Redeem them to earn XP and add to your collection.
                                </div>

                                <!-- In the Earned tab, update the actions section -->
<div class="d-flex gap-2 mt-2">
    <form method="POST" action="{{ route('badges.redeem', $badge->code) }}" class="flex-grow-1">
        @csrf
        <button type="submit" class="btn btn-success w-100">
            <i class="fas fa-gift me-1"></i>Redeem
        </button>
    </form>
    
    <a href="{{ route('badges.view', $badge->code) }}" class="btn btn-outline-primary">
        <i class="fas fa-eye me-1"></i>View
    </a>
    
    <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#shareModal{{ $badge->code }}">
        <i class="fas fa-share-alt me-1"></i>Share
    </button>
</div>

                                <div class="row row-cols-1 row-cols-md-2 g-4">
                                    @foreach($earnedBadges as $badge)
                                    <div class="col">
                                        <div class="card border border-warning border-2 h-100 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex align-items-start gap-3 mb-3">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning text-dark"
                                                         style="width: 50px; height: 50px;">
                                                        <i class="fas fa-star"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="fw-bold mb-1">{{ $badge->name }}</h6>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge" style="background-color: {{ $badge->category_color ?? '#6B7280' }}; color: white">
                                                                {{ $badge->category_name }}
                                                            </span>
                                                            <small class="text-muted">{{ $badge->code }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <p class="small text-muted mb-3">{{ $badge->description }}</p>
                                                
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <div>
                                                        <small class="text-muted">Earned on</small>
                                                        <div class="fw-bold">{{ date('M d, Y', strtotime($badge->earned_at)) }}</div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="fw-bold text-warning">+{{ $badge->xp_reward }} XP</div>
                                                        <small class="text-muted">Ready to claim</small>
                                                    </div>
                                                </div>
                                                
                                                <form method="POST" action="{{ route('badges.redeem', $badge->code ) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success w-100">
                                                        <i class="fas fa-gift me-2"></i>Redeem Now
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @else
                            <div class="text-center py-8">
                                <div class="mb-4">
                                    <i class="fas fa-star fa-4x text-muted"></i>
                                </div>
                                <h5 class="fw-bold mb-2">No badges ready to redeem</h5>
                                <p class="text-muted mb-4">Complete badge requirements to earn them</p>
                                <a href="{{ route('badges.index') }}" class="btn btn-warning">
                                    <i class="fas fa-tasks me-2"></i>Complete Requirements
                                </a>
                            </div>
                            @endif
                        </div>

                        <!-- In Progress Tab -->
                        <div class="tab-pane fade" id="progress" role="tabpanel">
                            @php
                                $inProgressBadges = $allBadges->where('progress_percentage', '>', 0)
                                    ->where('progress_percentage', '<', 100);
                            @endphp
                            
                            @if($inProgressBadges->count() > 0)
                            <div class="p-4">
                                <div class="alert alert-info mb-4">
                                    <i class="fas fa-spinner me-2"></i>
                                    <strong>Keep going!</strong> You're making progress on these badges.
                                </div>
                                
                                <div class="row g-4">
                                    @foreach($inProgressBadges as $badge)
                                    <div class="col-12">
                                        <div class="card border border-info h-100 shadow-sm">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-md-8">
                                                        <div class="d-flex align-items-center gap-3 mb-3">
                                                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-info text-white"
                                                                 style="width: 40px; height: 40px;">
                                                                <i class="fas fa-tasks"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="fw-bold mb-1">{{ $badge->name }}</h6>
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <span class="badge" style="background-color: {{ $badge->color ?? '#6B7280' }}; color: white">
                                                                        {{ $badge->category_name }}
                                                                    </span>
                                                                    <span class="badge bg-secondary">{{ $badge->level }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Progress Bar -->
                                                        <div class="mb-2">
                                                            <div class="d-flex justify-content-between mb-1">
                                                                <small class="text-muted">Progress</small>
                                                                <small class="fw-bold">{{ $badge->progress_percentage }}%</small>
                                                            </div>
                                                            <div class="progress" style="height: 8px;">
                                                                <div class="progress-bar bg-info" style="width: {{ $badge->progress_percentage }}%"></div>
                                                            </div>
                                                            <small class="text-muted">
                                                                {{ $badge->completed_count }} of {{ $badge->total_requirements }} requirements completed
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 text-md-end">
                                                        <div class="mb-3">
                                                            <div class="fw-bold text-warning">+{{ $badge->xp_reward }} XP</div>
                                                            <small class="text-muted">Reward</small>
                                                        </div>
                                                        <a href="{{ route('badges.index') }}?search={{ urlencode($badge->name) }}" 
                                                           class="btn btn-outline-primary btn-sm">
                                                            <i class="fas fa-external-link-alt me-1"></i>Continue
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @else
                            <div class="text-center py-8">
                                <div class="mb-4">
                                    <i class="fas fa-flag fa-4x text-muted"></i>
                                </div>
                                <h5 class="fw-bold mb-2">No badges in progress</h5>
                                <p class="text-muted mb-4">Start working on badge requirements to see progress here</p>
                                <a href="{{ route('badges.index') }}" class="btn btn-primary">
                                    <i class="fas fa-play me-2"></i>Start Earning
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notifications -->
@if(session('success'))
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
    <div class="toast show align-items-center text-white bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endif

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}
.bg-gradient-success {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%) !important;
}
.bg-gradient-warning {
    background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%) !important;
}
.bg-gradient-info {
    background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%) !important;
}
.icon-shape {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.card {
    border-radius: 12px;
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-2px);
}
.nav-tabs .nav-link {
    border: none;
    color: #6B7280;
    font-weight: 500;
    padding: 0.75rem 1rem;
}
.nav-tabs .nav-link.active {
    color: #3B82F6;
    border-bottom: 3px solid #3B82F6;
    background: transparent;
}
.progress {
    border-radius: 10px;
}
.toast {
    border-radius: 10px;
}
</style>

<script>
// Auto-hide toasts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(toast => {
        setTimeout(() => {
            toast.remove();
        }, 5000);
    });
    
    // Update tab based on filter
    @if($status == 'redeemed')
        const redeemedTab = new bootstrap.Tab(document.getElementById('redeemed-tab'));
        redeemedTab.show();
    @elseif($status == 'earned')
        const earnedTab = new bootstrap.Tab(document.getElementById('earned-tab'));
        earnedTab.show();
    @endif
});

// Toggle badge privacy
function togglePrivacy(badgeCode) {
    fetch(`/badges/${badgeCode}/toggle-privacy`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Reload the tab content
            setTimeout(() => {
                const activeTab = document.querySelector('.nav-link.active');
                if (activeTab) {
                    const tab = new bootstrap.Tab(activeTab);
                    tab.show();
                }
            }, 1000);
        }
    });
}

</script>
@endsection