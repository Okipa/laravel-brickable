<?php

namespace Okipa\LaravelBrickables\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Okipa\LaravelBrickables\Contracts\HasBrickables;
use Symfony\Component\HttpFoundation\Response;

class CRUDBrickable
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->brick) {
            $this->checkModelTypeIsProvided($request);
            $this->checkModelTypeIsInstanceOfHasBrickables($request);
            $this->checkModelIdIsProvided($request);
            if ($request->brickable_type) {
                $this->checkBrickableTypeIsInstanceOfBrickable($request);
                $this->checkBrickableCanBeHandled($request);
            }
        }
        $this->checkAdminPanelUrlIsProvided($request);

        return $next($request);
    }

    protected function checkModelTypeIsProvided(Request $request): void
    {
        if (! $request->model_type) {
            abort(Response::HTTP_FORBIDDEN, 'The model_type value is missing from the request.');
        }
    }

    protected function checkModelTypeIsInstanceOfHasBrickables(Request $request): void
    {
        if (! new $request->model_type() instanceof HasBrickables) {
            abort(
                Response::HTTP_FORBIDDEN,
                'The ' . $request->model_type . ' class should implement ' . HasBrickables::class . '.'
            );
        }
    }

    protected function checkModelIdIsProvided(Request $request): void
    {
        if (! $request->model_id) {
            abort(Response::HTTP_FORBIDDEN, 'The model_id value is missing from the request.');
        }
    }

    protected function checkBrickableTypeIsInstanceOfBrickable(Request $request): void
    {
        try {
            /** @var \Okipa\LaravelBrickables\Contracts\HasBrickables $model */
            $model = app($request->model_type);
            $model->checkBrickableTypeIsInstanceOfBrickable($request->brickable_type);
        } catch (Exception $exception) {
            abort(Response::HTTP_FORBIDDEN, $exception->getMessage());
        }
    }

    protected function checkBrickableCanBeHandled(Request $request): void
    {
        try {
            /** @var \Okipa\LaravelBrickables\Contracts\HasBrickables $model */
            $model = app($request->model_type);
            $model->checkBrickableCanBeHandled($request->brickable_type);
        } catch (Exception $exception) {
            abort(Response::HTTP_FORBIDDEN, $exception->getMessage());
        }
    }

    protected function checkAdminPanelUrlIsProvided(Request $request): void
    {
        if (! $request->admin_panel_url) {
            abort(Response::HTTP_FORBIDDEN, 'The admin_panel_url value is missing from the request.');
        }
    }
}
