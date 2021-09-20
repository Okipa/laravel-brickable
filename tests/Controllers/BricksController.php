<?php

namespace Okipa\LaravelBrickables\Tests\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BricksController extends \Okipa\LaravelBrickables\Controllers\BricksController
{
    public function create(Request $request): View
    {
        $brick = null;
        /** @var \Okipa\LaravelBrickables\Abstracts\Brickable $brickable */
        $brickable = app($request->brickable_type);

        return view($brickable->getFormViewPath(), compact('brick'));
    }
}
