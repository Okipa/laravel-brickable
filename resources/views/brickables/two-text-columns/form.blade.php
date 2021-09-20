@extends('laravel-brickables::admin.form.layout')
@section('inputs')
    <div class="form-group">
        <label for="left-content">{{ __('Left content') }}</label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-font"></i></span>
            </div>
            <textarea id="left-content"
                      class="form-control @error('text_left') is-invalid @enderror"
                      name="text_left"
                      placeholder="{{ __('Left content') }}">{{ data_get($brick, 'data.text_left') }}</textarea>
            @error('text_left')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="form-group">
        <label for="right-content">{{ __('Right content') }}</label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-font"></i></span>
            </div>
            <textarea id="right-content"
                      class="form-control @error('text_right') is-invalid @enderror"
                      name="text_right"
                      placeholder="{{ __('Right content') }}">{{ data_get($brick, 'data.text_right) }}</textarea>
            @error('text_right')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@endsection
