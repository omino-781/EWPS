<div class="row g-3 mb-4">
    @foreach($stats as $label => $value)
    <div class="col-md-4 col-xl-3">
        <div class="card card-stat shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted text-uppercase small">{{ str_replace('_', ' ', $label) }}</div>
                <div class="fs-3 fw-bold">{{ $value }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>
