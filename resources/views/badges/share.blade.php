@extends('layouts.app')

@section('title', 'Share Badge')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-share-alt me-2"></i>Share Badge</h5>
                        <a href="{{ route('badges.my') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-left me-1"></i>Back to My Badges
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Badge Preview -->
                    <div class="text-center mb-5">
                        <div class="badge-share-preview mx-auto">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary text-white mb-3"
                                 style="width: 80px; height: 80px;">
                                <i class="fas fa-award fa-2x"></i>
                            </div>
                            <h3 class="fw-bold mb-2">{{ $badge->name }}</h3>
                            <p class="text-muted mb-0">{{ $badge->description }}</p>
                            <div class="mt-3">
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-star me-1"></i>{{ $badge->xp_reward }} XP
                                </span>
                                <span class="badge bg-secondary ms-2">{{ $badge->category_name }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Share Options -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Choose where to share:</h6>
                        <div class="row g-3" id="sharePlatforms">
                            <div class="col-md-4">
                                <div class="share-option card text-center border h-100" data-platform="class_leaderboard">
                                    <div class="card-body">
                                        <div class="display-6 text-primary mb-3">
                                            <i class="fas fa-trophy"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1">Class Leaderboard</h6>
                                        <p class="small text-muted mb-0">Share with classmates</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="share-option card text-center border h-100" data-platform="internal_feed">
                                    <div class="card-body">
                                        <div class="display-6 text-success mb-3">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1">Internal Feed</h6>
                                        <p class="small text-muted mb-0">Share within platform</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="share-option card text-center border h-100" data-platform="social_media">
                                    <div class="card-body">
                                        <div class="display-6 text-info mb-3">
                                            <i class="fas fa-share-alt"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1">Social Media</h6>
                                        <p class="small text-muted mb-0">Share externally</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Custom Message -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Custom Message (Optional)</label>
                        <textarea class="form-control" id="shareMessage" rows="3" 
                                  placeholder="I just earned the {{ $badge->name }} badge! 🎉 #Achievement #Learning"></textarea>
                        <small class="text-muted">This will be included with your share</small>
                    </div>
                    
                    <!-- Share Button -->
                    <div class="d-grid">
                        <button class="btn btn-success btn-lg" id="shareButton" disabled>
                            <i class="fas fa-share me-2"></i>Share Badge
                        </button>
                    </div>
                    
                    <!-- Share Count -->
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="fas fa-share-alt me-1"></i>
                            This badge has been shared {{ $userBadge->shared_count ?? 0 }} times
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Success!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-4">
                    <i class="fas fa-check-circle fa-4x text-success"></i>
                </div>
                <h4 class="fw-bold mb-3">Badge Shared Successfully!</h4>
                <p id="successMessage" class="text-muted"></p>
            </div>
            <div class="modal-footer">
                <a href="{{ route('badges.my') }}" class="btn btn-primary">
                    <i class="fas fa-trophy me-2"></i>Back to My Badges
                </a>
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">
                    Share Another
                </button>
            </div>
        </div>
    </div>
</div>

<style>
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
.badge-share-preview {
    max-width: 400px;
    padding: 2rem;
    border-radius: 12px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
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
        document.getElementById('shareButton').disabled = false;
        
        // Update button text based on platform
        const platformNames = {
            'class_leaderboard': 'Share to Leaderboard',
            'internal_feed': 'Share to Internal Feed',
            'social_media': 'Share on Social Media'
        };
        
        document.getElementById('shareButton').innerHTML = 
            `<i class="fas fa-share me-2"></i>${platformNames[selectedPlatform]}`;
    });
});

// Share button click
document.getElementById('shareButton').addEventListener('click', function() {
    if (!selectedPlatform) return;
    
    const message = document.getElementById('shareMessage').value;
    const button = this;
    const originalText = button.innerHTML;
    
    // Show loading
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sharing...';
    button.disabled = true;
    
    // Send share request
    fetch(`/badges/{{ $badge->code }}/share`, {
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
            // Show success modal
            const platformDisplay = {
                'class_leaderboard': 'class leaderboard',
                'internal_feed': 'internal feed',
                'social_media': 'social media'
            };
            
            document.getElementById('successMessage').textContent = 
                `Your "${$badge->name}" badge has been successfully shared to the ${platformDisplay[selectedPlatform]}.`;
            
            const modal = new bootstrap.Modal(document.getElementById('successModal'));
            modal.show();
            
            // Reset form
            document.querySelectorAll('.share-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            document.getElementById('shareMessage').value = '';
            selectedPlatform = null;
            button.innerHTML = originalText;
            button.disabled = true;
        } else {
            alert(data.error || 'Failed to share badge');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to share badge');
        button.innerHTML = originalText;
        button.disabled = false;
    });
});
</script>
@endsection