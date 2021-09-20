<?php

namespace Okipa\LaravelBrickables\Models;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Okipa\LaravelBrickables\Abstracts\Brickable;
use Okipa\LaravelBrickables\Facades\Brickables;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Brick extends Model implements Htmlable, Sortable
{
    use SortableTrait;

    public array $sortable = ['order_column_name' => 'position', 'sort_when_creating' => true];

    protected $table = 'bricks';

    protected $casts = ['data' => 'json'];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function toHtml(): string
    {
        Brickables::isDisplayedOnPage($this->brickable);

        return view($this->brickable->getBrickViewPath(), ['brick' => $this])->toHtml();
    }

    public function getBrickableAttribute(): Brickable
    {
        return app($this->brickable_type);
    }

    public function buildSortQuery(): Builder
    {
        return static::query()->where('model_type', $this->model_type)->where('model_id', $this->model_id);
    }

    public function delete(): ?bool
    {
        if ($this->model->canDeleteBricksFrom($this->brickable_type)) {
            return parent::delete();
        }

        return false;
    }
}
