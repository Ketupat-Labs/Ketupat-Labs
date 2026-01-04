@props(['badge'])

<div class="badge-card {{ $badge['status_class'] }} animate-pop">
    <div class="badge-icon" style="background-color: {{ $badge['color'] }};">
        <i class="{{ $badge['icon'] }}"></i>
    </div>
    
    <div class="badge-content">
        <h5 class="badge-title">{{ $badge['name_bm'] }}</h5>
        <p class="badge-description">{{ $badge['description_bm'] }}</p>
        
        <div class="badge-progress">
            @if($badge['status'] === 'progress')
            <div class="progress-bar-container">
                <div class="progress-bar" style="width: {{ $badge['progress'] }}%"></div>
                <span class="progress-text">{{ $badge['progress'] }}%</span>
            </div>
            @endif
        </div>
        
        <button class="badge-button {{ $badge['button_class'] }}" 
                {{ $badge['disabled'] ? 'disabled' : '' }}
                data-badge-name="{{ $badge['name_bm'] }}"
                onclick="{{ $badge['onclick'] }}">
            {!! $badge['button_text'] !!}
        </button>
    </div>
    
    @if($badge['status'] === 'redeemed')
    <div class="badge-ribbon">TERBAIK! 🎉</div>
    @endif
</div>