@extends('layouts.app')

@section('title', 'Badges')

@php
    $categories = DB::table('badge_categories')->get();
    $categoryCounts = [];
    foreach ($categories as $cat) {
        $categoryCounts[$cat->code] = DB::table('badges')->where('category_code', $cat->code)->count();
    }
    
    // Map category to color classes
    $colorMap = [
        'RE' => ['bg' => 'bg-blue-light', 'text' => 'text-blue', 'badge' => '#3B82F6'],
        'DP' => ['bg' => 'bg-purple-light', 'text' => 'text-purple', 'badge' => '#8B5CF6'],
        'PT' => ['bg' => 'bg-green-light', 'text' => 'text-green', 'badge' => '#10B981'],
        'EA' => ['bg' => 'bg-orange-light', 'text' => 'text-orange', 'badge' => '#F59E0B'],
        'PI' => ['bg' => 'bg-yellow-light', 'text' => 'text-yellow', 'badge' => '#FBBF24'],
    ];
    
    // Create a session ID for the user (no login required)
    if (!session()->has('achievement_user_id')) {
        session(['achievement_user_id' => 'user_' . uniqid() . '_' . time()]);
    }
    $userId = session('achievement_user_id');
    
    // Get user badges from session-based storage
    $userBadges = [];
    $userBadgesData = DB::table('user_badges')
        ->where('user_id', $userId)
        ->get()
        ->keyBy('badge_code');
@endphp

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h2 fw-bold mb-1">Achievement Badges</h1>
                <p class="text-muted mb-0">Earn badges by completing projects</p>
            </div>
            <div class="d-flex gap-2">
                <a href="/my-badges" class="btn btn-primary">
                    <i class="fas fa-trophy me-2"></i>My Achievements
                </a>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0 bg-primary text-white">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-award fa-lg"></i>
                            </div>
                            <div>
                                <div class="fs-4 fw-bold">{{ $badges->count() }}</div>
                                <div class="small">Available</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0 bg-success text-white">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-unlock fa-lg"></i>
                            </div>
                            <div>
                                <div class="fs-4 fw-bold">{{ $userBadgesData->where('is_redeemed', true)->count() }}</div>
                                <div class="small">Redeemed</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0 bg-warning text-white">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-lock fa-lg"></i>
                            </div>
                            <div>
                                <div class="fs-4 fw-bold">{{ $userBadgesData->where('is_earned', true)->where('is_redeemed', false)->count() }}</div>
                                <div class="small">Earned (Not Redeemed)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0 bg-secondary text-white">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-lock-open fa-lg"></i>
                            </div>
                            <div>
                                <div class="fs-4 fw-bold">{{ $badges->count() - $userBadgesData->where('is_earned', true)->count() }}</div>
                                <div class="small">Locked</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-uppercase text-muted">Category</label>
                    <select name="category" class="form-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->code }}" {{ $category == $cat->code ? 'selected' : '' }}>
                            {{ $cat->name }} ({{ $categoryCounts[$cat->code] ?? 0 }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase text-muted">Level</label>
                    <select name="level" class="form-select" onchange="this.form.submit()">
                        <option value="">All Levels</option>
                        <option value="Beginner" {{ $level == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="Intermediate" {{ $level == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="Advanced" {{ $level == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase text-muted">Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="available">Available</option>
                        <option value="earned">Earned</option>
                        <option value="redeemed">Redeemed</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="/badges" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Badges Grid -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
        @foreach($badges as $badge)
        @php
            $colors = $colorMap[$badge->category_code] ?? ['bg' => 'bg-gray-light', 'text' => 'text-gray', 'badge' => '#6B7280'];
            $userBadge = $userBadgesData[$badge->code] ?? null;
            $status = $userBadge ? ($userBadge->is_redeemed ? 'redeemed' : ($userBadge->is_earned ? 'earned' : 'locked')) : 'locked';
        @endphp
        
        <div class="col">
            <div class="card h-100 hover-lift position-relative">
                <!-- Status Badge -->
                <div class="position-absolute top-0 end-0 m-3">
                    @if($status === 'redeemed')
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i> Redeemed
                        </span>
                    @elseif($status === 'earned')
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-star me-1"></i> Earned
                        </span>
                    @else
                        <span class="badge bg-secondary">
                            <i class="fas fa-lock me-1"></i> Locked
                        </span>
                    @endif
                </div>
                
                <div class="card-body">
                    <!-- Badge Header -->
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 {{ $colors['bg'] }} {{ $colors['text'] }}"
                             style="width: 56px; height: 56px;">
                            <i class="fas fa-certificate fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">{{ $badge->name }}</h6>
                            <small class="text-muted">{{ $badge->code }}</small>
                        </div>
                    </div>
                    
                    <!-- Badge Description -->
                    <p class="small text-muted mb-4">{{ $badge->description }}</p>
                    
                    <!-- Requirements Preview -->
                    <div class="mb-4">
                        <h6 class="small fw-bold text-uppercase text-muted mb-2">Requirements</h6>
                        <ul class="list-unstyled mb-0">
                            @php
                                $reqs = json_decode($badge->requirements ?? '[]', true);
                                $displayReqs = array_slice($reqs, 0, 2);
                            @endphp
                            @foreach($displayReqs as $req)
                            <li class="small text-muted mb-1">
                                <i class="fas fa-circle text-xs me-2" style="font-size: 0.5rem;"></i>
                                {{ Str::limit($req, 40) }}
                            </li>
                            @endforeach
                            @if(count($reqs) > 2)
                            <li class="small text-muted">
                                <i class="fas fa-ellipsis-h me-2"></i>
                                +{{ count($reqs) - 2 }} more requirements
                            </li>
                            @endif
                        </ul>
                    </div>
                    
                    <!-- Badge Info -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge" style="background-color: {{ $colors['badge'] }}; color: white">
                                {{ $badge->category_name }}
                            </span>
                            <span class="badge bg-{{ $badge->level == 'Beginner' ? 'success' : ($badge->level == 'Intermediate' ? 'warning' : 'danger') }}">
                                {{ $badge->level }}
                            </span>
                        </div>
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-star me-1"></i>{{ $badge->xp_reward }} XP
                        </span>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        @if($status === 'locked')
                            <form method="POST" action="{{ route('badges.earn', $badge->code) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary w-100" 
                                        onclick="return confirm('Mark this badge as earned? Make sure you have completed all requirements.')">
                                    <i class="fas fa-flag me-2"></i> Mark as Earned
                                </button>
                            </form>
                        @elseif($status === 'earned')
                            <form method="POST" action="{{ route('badges.redeem', $badge->code) }}">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-gift me-2"></i> Redeem Badge
                                </button>
                            </form>
                        @elseif($status === 'redeemed')
                            <button class="btn btn-outline-success w-100" disabled>
                                <i class="fas fa-check-circle me-2"></i> Already Redeemed
                            </button>
                        @endif
                        
                        <a href="{{ route('badges.show', $badge->code) }}" 
                           class="btn btn-sm btn-outline-secondary w-100">
                            <i class="fas fa-info-circle me-2"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    @if($badges->count() == 0)
    <div class="text-center py-8">
        <div class="mb-4">
            <i class="fas fa-search fa-3x text-muted"></i>
        </div>
        <h4 class="fw-bold mb-2">No badges found</h4>
        <p class="text-muted mb-4">Try adjusting your search or filters</p>
        <a href="/badges" class="btn btn-primary">Clear Filters</a>
    </div>
    @endif
</div>

<style>
.hover-lift {
    transition: transform 0.2s, box-shadow 0.2s;
}
.hover-lift:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px -10px rgba(0, 0, 0, 0.1);
}
.text-xs {
    font-size: 0.5rem !important;
}
</style>

@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    showToast('{{ session('success') }}', 'success');
});
</script>
@endif

@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    showToast('{{ session('error') }}', 'danger');
});
</script>
@endif

<script>
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'position-fixed bottom-0 end-0 p-3';
    toast.style.zIndex = '11';
    
    const bgColor = type === 'success' ? 'bg-success' : 'bg-danger';
    
    toast.innerHTML = `
        <div class="toast show" role="alert">
            <div class="toast-body d-flex align-items-center ${bgColor} text-white rounded p-3">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.remove();
    }, 5000);
}
</script>
@endsection