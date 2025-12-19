<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Total Badges</h5>
                <h2 class="card-text">{{ $stats['total'] }}</h2>
                <p class="card-text">Out of 32 badges</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Approved</h5>
                <h2 class="card-text">{{ $stats['approved'] }}</h2>
                <p class="card-text">Completed badges</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Pending</h5>
                <h2 class="card-text">{{ $stats['pending'] }}</h2>
                <p class="card-text">Under review</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Total XP</h5>
                <h2 class="card-text">{{ $stats['xp_total'] }}</h2>
                <p class="card-text">Experience Points</p>
            </div>
        </div>
    </div>
</div>