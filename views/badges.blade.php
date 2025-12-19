@extends('layouts.app')

@section('title', 'Pencapaian Saya')

@section('content')
<!-- Page Header -->
<section class="hero-malaysia" style="padding: var(--space-xl) 0;">
    <div class="container-custom">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="hero-title-malaysia mb-sm">Pencapaian Saya</h1>
                <p class="hero-subtitle-malaysia">Dapatkan lencana dengan menyelesaikan cabaran dan kuasai kemahiran.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="d-flex gap-4 justify-content-md-end justify-content-start">
                    <div class="text-center">
                        <h3 class="mb-1" style="color: var(--malaysia-yellow); font-size: var(--text-2xl);">{{ $totalBadges }}</h3>
                        <p class="mb-0 opacity-75">Lencana Keseluruhan</p>
                    </div>
                    <div class="text-center">
                        <h3 class="mb-1" style="color: var(--malaysia-yellow); font-size: var(--text-2xl);">{{ $earnedBadges }}</h3>
                        <p class="mb-0 opacity-75">Telah diperoleh</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Badges Grid -->
<section class="section-padding-sm">
    <div class="container-custom">
        <!-- Category Filter -->
        <div class="d-flex gap-2 mb-xl flex-wrap">
            <button class="filter-btn active" data-filter="all">Semua</button>
            <button class="filter-btn" data-filter="programming">Alatan Teknologi</button>
            <button class="filter-btn" data-filter="design">Reka Bentuk</button>
            <button class="filter-btn" data-filter="database">Teori</button>
            <button class="filter-btn" data-filter="mobiledev">Penyelidikan</button>
        </div>

        <!-- Badges Grid -->
        <div class="row row-gap" id="badgesGrid">
            @foreach($badges as $badge)
            <div class="col-xl-3 col-lg-4 col-md-6" data-category="{{ $badge['category'] }}">
                <div class="badge-card {{ $badge['status_class'] }}">
                    <div class="badge-icon" style="background-color: {{ $badge['color'] }};">
                        <i class="{{ $badge['icon'] }}"></i>
                    </div>
                    
                    <h5 class="badge-title">{{ $badge['name_bm'] }}</h5>
                    <p class="badge-description">{{ $badge['description_bm'] }}</p>
                    
                    @if(isset($badge['progress']))
                    <div class="progress-container mb-md">
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: {{ $badge['progress'] }}%"></div>
                            <span class="progress-text">{{ $badge['progress'] }}%</span>
                        </div>
                    </div>
                    @endif
                    
                    <button class="badge-button {{ $badge['button_class'] }}" 
                            {{ $badge['disabled'] ? 'disabled' : '' }}
                            data-badge-name="{{ $badge['name_bm'] }}">
                        {{ $badge['button_text'] }}
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Empty State -->
        <div class="text-center py-5 d-none" id="emptyState">
            <div class="badge-icon mx-auto mb-md" style="background: var(--medium-grey);">
                <i class="fas fa-search"></i>
            </div>
            <h3 class="mb-sm">No Badges Found</h3>
            <p class="text-muted">Try selecting a different category</p>
        </div>
    </div>
</section>

<!-- Achievement Progress -->
<section class="section-padding-sm" style="background: #f8f9fa;">
    <div class="container-custom">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-sm">Perkembangan Pembelajaran Anda</h3>
                        <p class="text-muted mb-md">Lengkapkan lagi cabaran untuk teroka lencana eksklusif dan ganjaran!</p>
                        <div class="progress-container">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-sm">Prestasi Keseluruhan</span>
                                <span class="text-sm fw-bold">25%</span>
                            </div>
                            <div class="progress-bar-large">
                                <div class="progress-fill" style="width: 25%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="level-display">
                            <div class="level-circle">
                                <span class="level-number">3</span>
                                <span class="level-text">Level</span>
                            </div>
                            <p class="text-sm text-muted mt-2">150 XP ke tahap seterusnya </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.filter-btn {
    padding: var(--space-xs) var(--space-md);
    border: 2px solid var(--primary);
    background: transparent;
    color: var(--primary);
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
}

.filter-btn.active,
.filter-btn:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-1px);
}

.progress-container {
    margin: var(--space-md) 0;
}

.progress-bar-container {
    background: #e9ecef;
    border-radius: 10px;
    height: 20px;
    position: relative;
    overflow: hidden;
}

.progress-bar {
    background: linear-gradient(90deg, var(--accent-yellow), var(--accent-orange));
    height: 100%;
    border-radius: 10px;
    transition: width 0.5s ease;
}

.progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: var(--dark-grey);
    font-weight: 600;
    font-size: 0.75rem;
}

.progress-bar-large {
    height: 12px;
    background: #E9ECEF;
    border-radius: 10px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    border-radius: 10px;
    transition: width 1s ease;
}

.level-display {
    text-align: center;
}

.level-circle {
    width: 100px;
    height: 100px;
    border: 4px solid var(--primary);
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.level-number {
    font-size: var(--text-2xl);
    font-weight: 700;
    color: var(--primary);
}

.level-text {
    font-size: var(--text-sm);
    color: var(--medium-grey);
}

.text-sm {
    font-size: var(--text-sm);
}

.btn-progress {
    background: var(--accent-yellow);
    color: var(--black);
}

.btn-progress:hover {
    background: #e6a500;
}
</style>
@endsection

@section('scripts')
<script>
// Category filtering
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const badgeItems = document.querySelectorAll('[data-category]');
    const emptyState = document.getElementById('emptyState');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filter = this.getAttribute('data-filter');
            let visibleCount = 0;

            badgeItems.forEach(item => {
                if (filter === 'all' || item.getAttribute('data-category') === filter) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            emptyState.classList.toggle('d-none', visibleCount > 0);
        });
    });
});

// BADGE REDEMPTION - ONLY HERE, NOT IN LAYOUT
document.addEventListener('click', function(e) {
    const redeemButton = e.target.closest('.btn-redeem');
    
    if (redeemButton && !redeemButton.disabled) {
        const badgeName = redeemButton.getAttribute('data-badge-name');
        const card = redeemButton.closest('.badge-card');
        
        if(confirm(`🎉 Adakah anda ingin menebus lencana "${badgeName}"?`)) {
            // Show loading
            const originalText = redeemButton.innerHTML;
            redeemButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menebus...';
            redeemButton.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // Success state
                redeemButton.innerHTML = '<i class="fas fa-check me-2"></i>Telah Ditebus!';
                redeemButton.classList.remove('btn-redeem');
                redeemButton.classList.add('btn-redeemed');
                
                // Update card
                card.classList.remove('redeemable');
                card.classList.add('redeemed');
                
                // Success message
                alert(`Tahniah! Anda telah menebus lencana "${badgeName}"! 🎊`);
                
            }, 1000);
        }
    }
});
</script>
@endsection