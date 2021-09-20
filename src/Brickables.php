<?php

namespace Okipa\LaravelBrickables;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Okipa\LaravelBrickables\Abstracts\Brickable;
use Okipa\LaravelBrickables\Contracts\HasBrickables;
use Okipa\LaravelBrickables\Controllers\DispatchController;
use Okipa\LaravelBrickables\Middleware\CRUDBrickable;
use Okipa\LaravelBrickables\Models\Brick;

class Brickables
{
    protected array $displayed = [];

    public function isDisplayedOnPage(Brickable $brickable)
    {
        $this->displayed[] = $brickable;
    }

    public function getCssResourcesToLoad(): Collection
    {
        return $this->getDisplayedOnPage()
            ->map(fn(Brickable $brickable) => $brickable->getCssResourcePath())
            ->unique()
            ->filter();
    }

    protected function getDisplayedOnPage(): Collection
    {
        return collect($this->displayed)->unique();
    }

    public function getJsResourcesToLoad(): Collection
    {
        return $this->getDisplayedOnPage()
            ->map(fn(Brickable $brickable) => $brickable->getJsResourcePath())
            ->unique()
            ->filter();
    }

    public function routes(Closure $additionalRoutes = null): void
    {
        Route::middleware([
            'web',
            CRUDBrickable::class,
            SubstituteBindings::class,
        ])->group(function () use ($additionalRoutes) {
            Route::get('brick/create', [DispatchController::class, 'create'])->name('brick.create');
            Route::post('brick/store', [DispatchController::class, 'store'])->name('brick.store');
            Route::get('brick/edit/{brick}', [DispatchController::class, 'edit'])->name('brick.edit');
            Route::put('brick/update/{brick}', [DispatchController::class, 'update'])->name('brick.update');
            Route::delete('brick/destroy/{brick}', [DispatchController::class, 'destroy'])->name('brick.destroy');
            Route::post('brick/move/up/{brick}', [DispatchController::class, 'moveUp'])->name('brick.move.up');
            Route::post('brick/move/down/{brick}', [DispatchController::class, 'moveDown'])->name('brick.move.down');
            if ($additionalRoutes) {
                $additionalRoutes();
            }
        });
    }

    public function getModelFromRequest(Request $request = null): ?HasBrickables
    {
        $request = $request ?: request();
        if ($request->has('model_type') && $request->has('model_id')) {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = app($request->model_type);
            /** @var \Okipa\LaravelBrickables\Contracts\HasBrickables $hasBrickablesModel */
            $hasBrickablesModel = $model instanceof HasBrickables ? $model->find($request->model_id) : null;

            return $hasBrickablesModel;
        }
        if ($request->brick) {
            return $this->castBrick($request->brick)->model;
        }

        return null;
    }

    public function castBrick(Brick $brick): Brick
    {
        return $this->castBricks(collect()->push($brick))->first();
    }

    public function castBricks(Collection $bricks): Collection
    {
        if ($bricks->isEmpty()) {
            return $bricks;
        }
        $castedBricks = new Collection();
        foreach ($bricks->pluck('brickable_type')->unique() as $brickableClass) {
            /** @var \Okipa\LaravelBrickables\Abstracts\Brickable $brickable */
            $brickable = app($brickableClass);
            $model = $brickable->getBrickModel();
            $brickableRawBricks = $bricks->where('brickable_type', $brickableClass)
                ->map(fn(Brick $brick) => $brick->getAttributes());
            $modelEagerLoadedRelations = array_keys($model->newQuery()->getEagerLoads());
            $brickableCastedBricks = $model->hydrate($brickableRawBricks->toArray())
                ->map(fn(Brick $brick) => $brick->loadMissing($modelEagerLoadedRelations));
            $castedBricks->push($brickableCastedBricks);
        }
        /** @var \Okipa\LaravelBrickables\Models\Brick $brickModel */
        $brickModel = app(config('brickables.bricks.model'));
        $orderColumnName = $brickModel->sortable['order_column_name'];

        return $castedBricks->flatten()->sortBy($orderColumnName);
    }
}
