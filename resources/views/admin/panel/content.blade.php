@php($bricks = $model->getBricks())
<div class="card-body">
    @foreach(config('brickables.additional_stylesheets.admin_panel', []) as $stylesheet)
        <link href="{{ $stylesheet }}" rel="stylesheet"/>
    @endforeach
    @if($bricks->isEmpty())
        @include('laravel-brickables::admin.panel.empty')
    @else
        @foreach($bricks as $brick)
            <div class="card {{ $loop->first ? null : 'mt-3' }}">
                <div class="card-header d-flex justify-content-between">
                    <h3 class="h4 m-0">{{ $brick->brickable->getLabel() }}</h3>
                    <div class="d-flex">
                        @include('laravel-brickables::admin.panel.move-up')
                        @include('laravel-brickables::admin.panel.move-down')
                        @include('laravel-brickables::admin.panel.edit')
                        @if($model->canDeleteBricksFrom($brick->brickable_type))
                            @include('laravel-brickables::admin.panel.destroy')
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="brick">
                        {{ $brick }}
                    </div>
                </div>
            </div>
        @endforeach
        @brickableResourcesCompute
    @endif
</div>
