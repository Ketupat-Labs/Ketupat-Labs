@extends('layouts.app')

@section('title', 'Badges')

@section('content')
<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold mb-1">Pencapaian Saya</h1>
            <p class="text-muted mb-0">Dapatkan lencana dengan menyelesaikan cabaran</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('badges.my') }}" class="btn btn-primary">
                <i class="fas fa-trophy me-2"></i>Dashboard Saya
            </a>
            <a href="/demo/badges" class="btn btn-outline-secondary">
                <i class="fas fa-eye me-2"></i>Demo Version
            </a>
        </div>
    </div>

    <!-- Category Filter -->
    @if(isset($categories) && $categories->count() > 0)
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-sm filter-btn active" data-filter="all">Semua</button>
                @foreach($categories as $cat)
                    <button class="btn btn-sm filter-btn" data-filter="{{ $cat->code }}">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Badges Grid -->
    @if(isset($badgesWithStatus) && $badgesWithStatus->count() > 0)
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4" id="badgesGrid">
        @foreach($badgesWithStatus as $badge)
        <div class="col" data-category="{{ $badge['category_slug'] }}">
            <div class="card h-100 border position-relative
                @if($badge['status'] === 'redeemed') border-success border-2
                @elseif($badge['status'] === 'earned') border-warning border-2
                @else border-secondary @endif"
                style="transition: transform 0.2s, box-shadow 0.2s;">

                @php $status = $badge['status']; @endphp
                <div class="position-absolute top-0 end-0 m-2">
                    @if($status === 'redeemed')
                        <span class="badge bg-success rounded-circle p-2" title="Telah Ditebus">
                            <i class="fas fa-crown fa-sm"></i>
                        </span>
                    @elseif($status === 'earned')
                        <span class="badge bg-warning rounded-circle p-2" title="Sedia Ditebus">
                            <i class="fas fa-gift fa-sm"></i>
                        </span>
                    @else
                        <span class="badge bg-secondary rounded-circle p-2" title="Terkunci">
                            <i class="fas fa-lock fa-sm"></i>
                        </span>
                    @endif
                </div>

                <div class="card-body text-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                         style="width: 70px; height: 70px; background-color: {{ $badge['color'] }}; color: white; font-size: 1.5rem;">
                        <i class="{{ $badge['icon'] }}"></i>
                    </div>

                    <h5 class="fw-bold mb-2">{{ $badge['name'] }}</h5>
                    <p class="small text-muted mb-3">{{ $badge['description'] }}</p>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Mata:</small>
                            <small class="fw-bold">{{ $badge['user_points'] }}/{{ $badge['requirement_value'] }}</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar
                                @if($status === 'redeemed') bg-success
                                @elseif($status === 'earned') bg-warning
                                @else bg-secondary @endif"
                                style="width: {{ $badge['progress'] }}%"></div>
                        </div>
                        <small class="text-muted d-block mt-1">{{ number_format($badge['progress'], 0) }}%</small>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge" style="background-color: {{ $badge['color'] }}; color: white">
                            {{ $badge['category_name'] }}
                        </span>
                        <span class="fw-bold text-warning">
                            <i class="fas fa-star me-1"></i>{{ $badge['xp_reward'] }} XP
                        </span>
                    </div>

                    @if($badge['is_redeemable'])
                    <button type="button" 
                    class="btn btn-warning w-100 btn-redeem"
                    data-badge-code="{{ $badge['code'] }}">
                    <i class="fas fa-gift me-2"></i> Tebus Sekarang
                    </button>

                    @elseif($status === 'redeemed')
                        <button class="btn btn-outline-success w-100" disabled>
                            <i class="fas fa-check-circle me-2"></i> Telah Ditebus
                        </button>
                    @else
                        <button class="btn btn-outline-secondary w-100" disabled>
                            <i class="fas fa-lock me-2"></i> Terkunci
                        </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-8">
        <h4 class="fw-bold mb-2">Tiada Lencana Dijumpai</h4>
        <p class="text-muted mb-4">Mulakan aktiviti untuk memperoleh lencana pertama anda!</p>
        <a href="{{ route('demo.badges') }}" class="btn btn-primary">Cuba Demo</a>
    </div>
    @endif
</div>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter
    const filterBtns = document.querySelectorAll('.filter-btn');
    const badgeCards = document.querySelectorAll('#badgesGrid .col');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filter = this.getAttribute('data-filter');
            badgeCards.forEach(card => {
                const cardCategory = card.getAttribute('data-category');
                if(filter === 'all' || cardCategory === filter){
                    card.classList.remove('d-none');
                } else {
                    card.classList.add('d-none');
                }
            });
        });
    });

    // Redeem badge via AJAX
    const redeemBtns = document.querySelectorAll('.btn-redeem');
    redeemBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const badgeCode = this.getAttribute('data-badge-code');
            const btnEl = this;

            fetch("{{ route('badges.redeem') }}", {
                method: 'POST',
                headers: {
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN':'{{ csrf_token() }}'
                },
                body: JSON.stringify({ badge_code: badgeCode })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    btnEl.classList.remove('btn-warning');
                    btnEl.classList.add('btn-outline-success');
                    btnEl.innerHTML = '<i class="fas fa-check-circle me-2"></i> Telah Ditebus';
                    btnEl.disabled = true;
                } else {
                    alert(data.message);
                }
            })
            .catch(err => console.error(err));
        });
    });
});



document.addEventListener('DOMContentLoaded', function() {
    const redeemBtns = document.querySelectorAll('.btn-redeem');

    redeemBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const badgeCode = this.getAttribute('data-badge-code');
            const btnElement = this;

            btnElement.disabled = true;
            btnElement.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

            fetch("{{ route('badges.redeem') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ badge_code: badgeCode })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    btnElement.classList.remove('btn-warning');
                    btnElement.classList.add('btn-outline-success');
                    btnElement.innerHTML = '<i class="fas fa-check-circle me-2"></i>Telah Ditebus';

                    // Update badge card border
                    const card = btnElement.closest('.card');
                    card.classList.remove('border-warning', 'border-secondary');
                    card.classList.add('border-success', 'border-2');
                } else {
                    alert(data.message);
                    btnElement.disabled = false;
                    btnElement.innerHTML = '<i class="fas fa-gift me-2"></i> Tebus Sekarang';
                }
            })
            .catch(error => {
                console.error(error);
                alert('Something went wrong!');
                btnElement.disabled = false;
                btnElement.innerHTML = '<i class="fas fa-gift me-2"></i> Tebus Sekarang';
            });
        });
    });
});

</script>

<style>
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
.progress, .progress-bar {
    border-radius: 10px;
}
.filter-btn.active {
    background-color: var(--primary);
    color: white;
    border-color: var(--primary);
}
</style>

@endsection
