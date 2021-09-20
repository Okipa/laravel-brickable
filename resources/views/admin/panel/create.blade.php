@php($additionableBrickables = $model->getAdditionableBrickables())
@if($additionableBrickables->isNotEmpty())
    <form class="form-inline" role="form" method="GET" action="{{ route('brick.create') }}">
        <input type="hidden" name="model_id" value="{{ $model->id }}">
        <input type="hidden" name="model_type" value="{{ get_class($model) }}">
        <input type="hidden" name="admin_panel_url" value="{{ url()->full() }}#bricks-admin-panel">
        <div class="form-group mb-0 mr-3">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">
                        <i class="fas fa-shapes"></i>
                    </span>
                </div>
                <select class="custom-select{{ optional($errors ?? null)->has('brickable_type') ? ' is-invalid' : null }}"
                        name="brickable_type">
                    <option value="">{{ __('validation.attributes.brickable_type') }}</option>
                    @foreach($additionableBrickables as $brickable)
                        <option value="{{ get_class($brickable) }}">{{ $brickable->getLabel() }}</option>
                    @endforeach
                </select>
                @if(optional($errors ?? null)->has('brickable_type'))
                    <div class="invalid-feedback">{{ $errors->first('brickable_type') }}</div>
                @endif
            </div>
        </div>
        <button class="btn btn-primary" type="submit" title="{{ __('Add') }}">
            <i class="fas fa-plus-circle fa-fw"></i> {{ __('Add') }}
        </button>
    </form>
@endif
