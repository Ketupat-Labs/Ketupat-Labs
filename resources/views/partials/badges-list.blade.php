@foreach($badges as $badge)
@php
    $userBadge = $userBadges[$badge->code] ?? null;
    $status = $userBadge ? ($userBadge->is_redeemed ? 'redeemed' : ($userBadge->is_earned ? 'earned' : 'locked')) : 'locked';
@endphp

<div class="col">
    <div class="card h-100 border @if($status === 'earned') border-warning @elseif($status === 'redeemed') border-success @endif">
        <div class="card-body">
            <!-- Your badge HTML here (same as index.blade.php) -->
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white"
                     style="width: 50px; height: 50px;">
                    <i class="fas fa-award"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0">{{ $badge->name }}</h6>
                    <small class="text-muted">{{ $badge->code }}</small>
                </div>
            </div>
            
            <p class="small text-muted mb-3">{{ $badge->description }}</p>
            
            <!-- Badge Info -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge" style="background-color: {{ $badge->color ?? '#6B7280' }}; color: white">
                        {{ $badge->category_name }}
                    </span>
                    <span class="badge bg-{{ $badge->level == 'Beginner' ? 'success' : ($badge->level == 'Intermediate' ? 'warning' : 'danger') }}">
                        {{ $badge->level }}
                    </span>
                    <span class="fw-bold text-warning">
                        <i class="fas fa-star"></i>{{ $badge->xp_reward }}
                    </span>
                </div>
            </div>
            
            <!-- Status -->
            <div class="text-center mb-3">
                @if($status === 'earned')
                    <span class="badge bg-warning text-dark w-100 py-2">
                        <i class="fas fa-star me-1"></i> EARNED
                    </span>
                @elseif($status === 'redeemed')
                    <span class="badge bg-success w-100 py-2">
                        <i class="fas fa-check-circle me-1"></i> REDEEMED
                    </span>
                @else
                    <span class="badge bg-secondary w-100 py-2">
                        <i class="fas fa-lock me-1"></i> LOCKED
                    </span>
                @endif
            </div>
            
            <!-- Action Buttons -->
            @if($status === 'earned')
                <form method="POST" action="{{ route('badges.redeem', $badge->code) }}" 
                    onsubmit="earnBadge(event, '{{ $badge->code }}')">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-gift me-2"></i> Redeem Badge
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endforeach