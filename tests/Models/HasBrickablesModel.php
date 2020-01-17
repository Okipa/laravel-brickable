<?php

namespace Okipa\LaravelBrickables\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Okipa\LaravelBrickables\Contracts\HasBrickables;
use Okipa\LaravelBrickables\Traits\HasBrickablesTrait;

class HasBrickablesModel extends Model implements HasBrickables
{
    use HasBrickablesTrait;
}