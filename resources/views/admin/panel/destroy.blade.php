<form class="ml-1" role="form" method="POST" action="{{ $brick->brickable->getDestroyRoute($brick) }}">
    @csrf
    @method('DELETE')
    <input type="hidden" name="admin_panel_url" value="{{ url()->full() }}#bricks-admin-panel">
    <button class="btn btn-link p-0 text-danger" type="submit" title="{{ __('Destroy') }}">
        <i class="fas fa-trash fa-fw"></i>
    </button>
</form>
