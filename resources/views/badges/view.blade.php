@extends('layouts.app')

@section('title', $badge->name . ' - Badge Details')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Back Button -->
            <div class="mb-4">
                <a href="{{ route('badges.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to All Badges
                </a>
            </div>
            
            <!-- Badge Details Card -->
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-gradient-primary text-white py-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="fw-bold mb-2">{{ $badge->name }}</h2>
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-light text-dark">{{ $badge->code }}</span>
                                <span class="badge" style="background-color: {{ $badge->color }}; color: white">
                                    {{ $badge->category_name }}
                                </span>
                                <span class="badge bg-{{ $badge->level == 'Beginner' ? 'success' : ($badge->level == 'Intermediate' ? 'warning' : 'danger') }}">
                                    {{ $badge->level }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="display-4 fw-bold text-warning">
                                <i class="fas fa-star me-2"></i>{{ $badge->xp_reward }} XP
                            </div>
                            <small class="text-white-50">Reward Points</small>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-5">
                    <!-- Description -->
                    <div class="mb-5">
                        <h4 class="fw-bold mb-3">📝 Description</h4>
                        <p class="lead">{{ $badge->description }}</p>
                    </div>
                    
                    <!-- Requirements -->
                    <div class="mb-5">
                        <h4 class="fw-bold mb-3">✅ Requirements to Earn</h4>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="list-group">
                                    @foreach($requirements as $req)
                                    <div class="list-group-item border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            @if(in_array($req, $completedReqs))
                                            <span class="badge bg-success rounded-circle p-2 me-3">
                                                <i class="fas fa-check"></i>
                                            </span>
                                            <span class="text-success text-decoration-line-through">{{ $req }}</span>
                                            @else
                                            <span class="badge bg-secondary rounded-circle p-2 me-3">
                                                <i class="fas fa-circle"></i>
                                            </span>
                                            <span>{{ $req }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="fw-bold mb-3">Your Progress</h5>
                                        <div class="mb-4">
                                            <div class="display-3 fw-bold text-primary">{{ $progressPercentage }}%</div>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar bg-primary" style="width: {{ $progressPercentage }}%"></div>
                                            </div>
                                        </div>
                                        <p class="text-muted">
                                            {{ count(array_intersect($requirements, $completedReqs)) }} of {{ count($requirements) }} requirements completed
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status & Actions -->
                    <div class="mt-5">
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="fw-bold mb-3">🎯 Current Status</h4>
                                @php
                                    $status = $userBadge ? ($userBadge->is_redeemed ? 'redeemed' : ($userBadge->is_earned ? 'earned' : 'locked')) : 'locked';
                                @endphp
                                
                                <div class="alert alert-{{ $status == 'redeemed' ? 'success' : ($status == 'earned' ? 'warning' : 'secondary') }} py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            @if($status == 'redeemed')
                                                <i class="fas fa-check-circle fa-2x text-success"></i>
                                            @elseif($status == 'earned')
                                                <i class="fas fa-star fa-2x text-warning"></i>
                                            @else
                                                <i class="fas fa-lock fa-2x text-secondary"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-1">
                                                @if($status == 'redeemed')
                                                    🎉 Badge Redeemed!
                                                @elseif($status == 'earned')
                                                    ⭐ Ready to Redeem!
                                                @else
                                                    🔒 Locked - Complete Requirements
                                                @endif
                                            </h5>
                                            <p class="mb-0">
                                                @if($status == 'redeemed')
                                                    You redeemed this badge on {{ date('F d, Y', strtotime($userBadge->redeemed_at)) }}
                                                @elseif($status == 'earned')
                                                    You earned this badge on {{ date('F d, Y', strtotime($userBadge->earned_at)) }}
                                                @else
                                                    Complete all requirements to earn this badge
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <h4 class="fw-bold mb-3">⚡ Quick Actions</h4>
                                
                                @if($status == 'earned')
                                    <div class="d-grid gap-3">
                                        <button class="btn btn-success btn-lg py-3" id="redeemBtn">
                                            <i class="fas fa-gift me-2"></i>Redeem Badge
                                        </button>
                                        
                                        <button class="btn btn-primary btn-lg py-3" data-bs-toggle="modal" data-bs-target="#shareModal">
                                            <i class="fas fa-share-alt me-2"></i>Share Achievement
                                        </button>
                                    </div>
                                @elseif($status == 'redeemed')
                                    <div class="d-grid gap-3">
                                        <button class="btn btn-outline-success btn-lg py-3" disabled>
                                            <i class="fas fa-check-circle me-2"></i>Already Redeemed
                                        </button>
                                        
                                        <button class="btn btn-primary btn-lg py-3" data-bs-toggle="modal" data-bs-target="#shareModal">
                                            <i class="fas fa-share-alt me-2"></i>Share Achievement
                                        </button>
                                    </div>
                                @else
                                    <div class="d-grid">
                                        <button class="btn btn-secondary btn-lg py-3" disabled>
                                            <i class="fas fa-lock me-2"></i>Complete Requirements First
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Share Stats -->
            @if($userBadge && $userBadge->shared_count > 0)
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3 text-primary">
                            <i class="fas fa-share-alt fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Shared {{ $userBadge->shared_count }} times</h5>
                            <p class="text-muted mb-0">This badge has been shared with others</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-share-alt me-2"></i>Share Your Achievement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary text-white mb-3"
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-award fa-2x"></i>
                    </div>
                    <h5 class="fw-bold">Share "{{ $badge->name }}"</h5>
                    <p class="text-muted">Celebrate your achievement with others!</p>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Share Platform</label>
                    <div class="row g-3" id="sharePlatforms">
                        <div class="col-4">
                            <div class="share-option card text-center border h-100" data-platform="class_leaderboard">
                                <div class="card-body py-3">
                                    <div class="text-primary mb-2">
                                        <i class="fas fa-trophy fa-2x"></i>
                                    </div>
                                    <small class="fw-bold">Class Leaderboard</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="share-option card text-center border h-100" data-platform="internal_feed">
                                <div class="card-body py-3">
                                    <div class="text-success mb-2">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                    <small class="fw-bold">Internal Feed</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="share-option card text-center border h-100" data-platform="social_media">
                                <div class="card-body py-3">
                                    <div class="text-info mb-2">
                                        <i class="fas fa-share-alt fa-2x"></i>
                                    </div>
                                    <small class="fw-bold">Social Media</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Custom Message (Optional)</label>
                    <textarea class="form-control" id="shareMessage" rows="3" 
                              placeholder="I just earned the {{ $badge->name }} badge! 🎉 #Achievement #Learning"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="shareBtn" disabled>
                    <i class="fas fa-share me-2"></i>Share Now
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast Template -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" style="display: none;">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i>
                <span id="toastMessage"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}
.card {
    border-radius: 12px;
}
.share-option {
    cursor: pointer;
    transition: all 0.2s;
}
.share-option:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.share-option.selected {
    border-color: #3B82F6;
    border-width: 2px;
    background-color: rgba(59, 130, 246, 0.05);
}
.list-group-item {
    border-left: none;
    border-right: none;
}
</style>

<script>
let selectedPlatform = null;

// Platform selection
document.querySelectorAll('.share-option').forEach(option => {
    option.addEventListener('click', function() {
        // Remove previous selection
        document.querySelectorAll('.share-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        
        // Select current
        this.classList.add('selected');
        selectedPlatform = this.dataset.platform;
        
        // Enable share button
        document.getElementById('shareBtn').disabled = false;
    });
});

// Share button click
document.getElementById('shareBtn').addEventListener('click', function() {
    if (!selectedPlatform) return;
    
    const message = document.getElementById('shareMessage').value;
    const button = this;
    const originalText = button.innerHTML;
    
    // Show loading
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sharing...';
    button.disabled = true;
    
    // Send share request
    fetch('/badges/{{ $badge->code }}/share', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            platform: selectedPlatform,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('shareModal'));
            modal.hide();
            
            // Show success toast
            showToast(data.message);
            
            // Reset form
            document.querySelectorAll('.share-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            document.getElementById('shareMessage').value = '';
            selectedPlatform = null;
            
            // Reload page after 2 seconds to update share count
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            alert(data.message || 'Failed to share badge');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to share badge');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = true;
    });
});

// Redeem button
document.getElementById('redeemBtn')?.addEventListener('click', function() {
    const button = this;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    button.disabled = true;
    
    fetch('/badges/{{ $badge->code }}/quick-redeem', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast(data.message, 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to redeem badge', 'error');
        button.innerHTML = originalText;
        button.disabled = false;
    });
});

// Show toast notification
function showToast(message, type = 'success') {
    const toast = document.getElementById('successToast');
    const toastMessage = document.getElementById('toastMessage');
    
    toastMessage.textContent = message;
    
    // Set color based on type
    if (type === 'error') {
        toast.classList.remove('bg-success');
        toast.classList.add('bg-danger');
    } else {
        toast.classList.remove('bg-danger');
        toast.classList.add('bg-success');
    }
    
    // Show toast
    toast.style.display = 'block';
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        bsToast.hide();
    }, 5000);
}

// Reset share form when modal closes
document.getElementById('shareModal').addEventListener('hidden.bs.modal', function() {
    document.querySelectorAll('.share-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    document.getElementById('shareMessage').value = '';
    selectedPlatform = null;
    document.getElementById('shareBtn').disabled = true;
});
</script>
@endsection