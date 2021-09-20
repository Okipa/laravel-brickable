<form class="mx-1" role="form" method="GET" action="{{ $brick->brickable->getEditRoute($brick) }}">
    <input type="hidden" name="admin_panel_url" value="{{ url()->full() }}#bricks-admin-panel">
    <button class="btn btn-link p-0" type="submit" title="{{ __('Edit') }}">
        <i class="fas fa-edit fa-fw"></i>
    </button>
</form>
