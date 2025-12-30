@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<!-- Profile Header -->
<section class="hero-section" style="padding: var(--space-xl) 0;">
    <div class="container-custom">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <div class="position-relative">
                            <div class="badge-icon mx-auto" style="background: var(--primary); width: 80px; height: 80px;">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="mt-2">
                                <span class="badge rounded-pill" style="background: var(--accent-yellow); color: var(--black);">Level 3</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h1 class="h2 mb-1">Ahmad</h1>
                        <p class="text-muted mb-2">ahmad@compuplay.com</p>
                        <p class="text-muted mb-3">Passionate about programming and design. Always eager to learn new technologies.</p>
                        
                        <div class="d-flex gap-4">
                            <div class="text-center">
                                <strong class="d-block h4 mb-1" style="color: var(--primary);">{{ $totalBadges }}</strong>
                                <span class="text-sm text-muted">Total Badges</span>
                            </div>
                            <div class="text-center">
                                <strong class="d-block h4 mb-1" style="color: var(--secondary);">{{ $earnedBadges }}</strong>
                                <span class="text-sm text-muted">Earned</span>
                            </div>
                            <div class="text-center">
                                <strong class="d-block h4 mb-1" style="color: var(--accent-orange);">{{ $inProgress }}</strong>
                                <span class="text-sm text-muted">In Progress</span>
                            </div>
                            <div class="text-center">
                                <strong class="d-block h4 mb-1" style="color: var(--accent-yellow);">{{ $points }}</strong>
                                <span class="text-sm text-muted">XP Points</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="p-3 rounded" style="background: rgba(36, 84, 255, 0.1); border: 2px solid var(--primary);">
                            <i class="fas fa-trophy fa-2x mb-2" style="color: var(--accent-yellow);"></i>
                            <div>
                                <strong class="d-block">Top 15%</strong>
                                <small class="text-muted">Global Rank</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Progress Overview -->
<section class="section-padding-sm">
    <div class="container-custom">
        <h2 class="mb-xl text-center">Perkembangan Pembelajaran</h2>
        
        <div class="row row-gap">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Pengaturcaraan</span>
                            <span class="fw-bold" style="color: var(--primary);">65%</span>
                        </div>
                        <div class="progress-bar-large">
                            <div class="progress-fill" style="width: 65%; background: var(--primary);"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Reka Bentuk</span>
                            <span class="fw-bold" style="color: var(--secondary);">40%</span>
                        </div>
                        <div class="progress-bar-large">
                            <div class="progress-fill" style="width: 40%; background: var(--secondary);"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Pangkalan Data</span>
                            <span class="fw-bold" style="color: var(--accent-orange);">30%</span>
                        </div>
                        <div class="progress-bar-large">
                            <div class="progress-fill" style="width: 30%; background: var(--accent-orange);"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Pembangunan mobile</span>
                            <span class="fw-bold" style="color: var(--accent-yellow);">20%</span>
                        </div>
                        <div class="progress-bar-large">
                            <div class="progress-fill" style="width: 20%; background: var(--accent-yellow);"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Earned Badges -->
<section class="section-padding-sm" style="background: #f8f9fa;">
    <div class="container-custom">
        <h2 class="mb-xl text-center">Koleksi Lencana</h2>

        @if(count($redeemedBadges) > 0)
        <div class="row row-gap">
            @foreach($redeemedBadges as $badge)
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="badge-icon mx-auto mb-3" style="background-color: {{ $badge['color'] }};">
                            <i class="{{ $badge['icon'] }}"></i>
                        </div>
                        <h6 class="fw-bold mb-2">{{ $badge['name_bm'] }}</h6>
                        <p class="text-muted small mb-2">{{ $badge['description_bm'] }}</p>
                        <div class="mt-auto">
                            <small class="text-muted d-block mb-1">Earned {{ $badge['date'] }}</small>
                            <span class="badge rounded-pill" style="background: var(--accent-yellow); color: var(--black);">
                                <i class="fas fa-star me-1"></i>{{ $badge['points'] }} XP
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5">
            <div class="badge-icon mx-auto mb-4" style="background: var(--medium-grey);">
                <i class="fas fa-trophy"></i>
            </div>
            <h3 class="mb-3">No Badges Yet</h3>
            <p class="text-muted mb-4">Start your journey by completing challenges and earning your first badge!</p>
            <a href="./badges" class="btn btn-primary">Explore Badges</a>
        </div>
        @endif
    </div>
</section>
@endsection