@php
    $cards = [
        ['label' => 'Staffs', 'icon' => 'fa fa-users', 'count' => 24, 'color' => 'orange'],
        ['label' => 'Classes', 'icon' => 'fa fa-building', 'count' => 12, 'color' => 'green'],
        ['label' => 'Students', 'icon' => 'fa fa-graduation-cap', 'count' => 420, 'color' => 'blue'],
        ['label' => 'Applications', 'icon' => 'fa fa-file-text', 'count' => 38, 'color' => 'cyan'],
        ['label' => 'Parents', 'icon' => 'fa fa-user-circle', 'count' => 310, 'color' => 'purple'],
        ['label' => 'Vehicles', 'icon' => 'fa fa-bus', 'count' => 8, 'color' => 'red'],
        ['label' => 'Accounts', 'icon' => 'fa fa-bank', 'count' => 16, 'color' => 'azure'],
        ['label' => 'Admissions ' . $year, 'icon' => 'fa fa-check-circle', 'count' => 91, 'color' => 'indigo'],
    ];
@endphp

<div class="row clearfix row-deck col-md-12">
    @foreach($cards as $card)
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card h-80 d-flex flex-column">
                <div class="card-body ribbon">
                    <div class="ribbon-box {{ $card['color'] }}" data-toggle="tooltip" title="{{ $card['label'] }}">{{ $card['count'] }}</div>
                    <a href="#" class="nav-link my_sort_cut text-muted">
                        <i class="{{ $card['icon'] }}"></i>
                        <span>{{ $card['label'] }}</span>
                    </a>
                </div>
            </div>
        </div>
    @endforeach
</div>
