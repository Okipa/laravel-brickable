@yield('prepend')
<div class="card">
    <div class="card-header">
        <h2 class="m-0">
            @hasSection('title')
                @yield('title')
            @else
                {{ __('Brick data') }}
            @endif
        </h2>
    </div>
    <div class="card-body">
        <form method="POST"
              action="{{ $brick ? $brickable->getUpdateRoute($brick) : $brickable->getStoreRoute() }}"
              enctype="multipart/form-data">
            @csrf
            @if($brick)@method('PUT')@endif
            <input type="hidden" name="model_id" value="{{ $model->id }}">
            <input type="hidden" name="model_type" value="{{ get_class($model) }}">
            <input type="hidden" name="brickable_type" value="{{ get_class($brickable) }}">
            <input type="hidden" name="admin_panel_url" value="{{ $adminPanelUrl }}">
            @yield('inputs')
            @hasSection('actions')
                @yield('actions')
            @else
                @include('laravel-brickables::admin.form.actions')
            @endif
        </form>
    </div>
</div>
@yield('append')
