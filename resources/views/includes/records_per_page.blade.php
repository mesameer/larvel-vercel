<div class="col-sm-12 col-md-2">
    <div class="dropdown show float-right">
        <a class="btn btn-primary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Items Per Page <span class="badge badge-light">{{ request()->get('items') ? request()->get('items') : 10 }}</span>
        </a>

        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
            <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['items' => 10]) }}">10</a>
            <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['items' => 50]) }}">50</a>
            <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['items' => 100]) }}">100</a>
            <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['items' => 500]) }}">500</a>
        </div>
    </div>
</div>