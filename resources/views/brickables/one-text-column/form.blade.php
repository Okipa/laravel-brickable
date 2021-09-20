@extends('laravel-brickables::admin.form.layout')
@section('inputs')
    <div class="form-group">
        <label for="content">{{ __('Content') }}</label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-font"></i></span>
            </div>
            <textarea id="content"
                      class="form-control @error('text') is-invalid @enderror"
                      name="content"
                      placeholder="{{ __('Content') }}">{{ data_get($brick, 'data.text') }}</textarea>
            @error('text')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@endsection
