<?php

namespace Okipa\LaravelBrickables\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Okipa\LaravelBrickables\Facades\Brickables;
use Okipa\LaravelBrickables\Models\Brick;

class BricksController extends Controller
{
    public function create(Request $request): View
    {
        $brick = null;
        /** @var \Okipa\LaravelBrickables\Contracts\HasBrickables $model */
        $model = app($request->model_type)->findOrFail($request->model_id);
        /** @var \Okipa\LaravelBrickables\Abstracts\Brickable $brickable */
        $brickable = app($request->brickable_type);
        $adminPanelUrl = $request->admin_panel_url;

        return view($brickable->getFormViewPath(), compact('brick', 'model', 'brickable', 'adminPanelUrl'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     * @throws \Okipa\LaravelBrickables\Exceptions\InvalidBrickableClassException
     * @throws \Okipa\LaravelBrickables\Exceptions\NotRegisteredBrickableClassException
     * @throws \Okipa\LaravelBrickables\Exceptions\BrickableCannotBeHandledException
     */
    public function store(Request $request)
    {
        /** @var \Okipa\LaravelBrickables\Contracts\HasBrickables $model */
        $model = app($request->model_type)->findOrFail($request->model_id);
        /** @var \Okipa\LaravelBrickables\Abstracts\Brickable $brickable */
        $brickable = app($request->brickable_type);
        $validated = $brickable->validateStoreInputs();
        $brick = $model->addBrick($request->brickable_type, $validated);
        $this->stored($request, $brick);

        return $this->sendBrickCreatedResponse($request, $brick);
    }

    /**
     * Execute additional treatment once the brick has been stored.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Okipa\LaravelBrickables\Models\Brick $brick
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function stored(Request $request, Brick $brick): void
    {
        //
    }

    protected function sendBrickCreatedResponse(Request $request, Brick $brick): RedirectResponse
    {
        return redirect()->to($request->admin_panel_url)
            ->with('success', __('The entry :model > :brickable has been created.', [
                'brickable' => $brick->brickable->getLabel(),
                'model' => $brick->model->getReadableClassName(),
            ]));
    }

    public function edit(Brick $brick, Request $request): View
    {
        $brick = Brickables::castBrick($brick);
        /** @var \Okipa\LaravelBrickables\Contracts\HasBrickables $model */
        $model = $brick->model;
        /** @var \Okipa\LaravelBrickables\Abstracts\Brickable $brickable */
        $brickable = $brick->brickable;
        $adminPanelUrl = $request->admin_panel_url;

        return view($brickable->getFormViewPath(), compact('brick', 'model', 'brickable', 'adminPanelUrl'));
    }

    /**
     * @param \Okipa\LaravelBrickables\Models\Brick $brick
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function update(Brick $brick, Request $request)
    {
        $brick = Brickables::castBrick($brick);
        $validated = $brick->brickable->validateUpdateInputs();
        $brick->data = $validated;
        $brick->save();
        $this->updated($request, $brick);

        return $this->sendBrickUpdatedResponse($request, $brick);
    }

    /**
     * Execute additional treatment once the brick has been updated.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Okipa\LaravelBrickables\Models\Brick $brick
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function updated(Request $request, Brick $brick): void
    {
        //
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Okipa\LaravelBrickables\Models\Brick $brick
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function sendBrickUpdatedResponse(Request $request, Brick $brick)
    {
        return redirect()->back()->with('success', __('The entry :model > :brickable has been updated.', [
            'brickable' => $brick->brickable->getLabel(),
            'model' => $brick->model->getReadableClassName(),
        ]));
    }

    /**
     * @param \Okipa\LaravelBrickables\Models\Brick $brick
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function destroy(Brick $brick, Request $request)
    {
        $brick = Brickables::castBrick($brick);
        $brickClone = clone $brick;
        $brick->delete();

        return $this->sendBrickDestroyedResponse($request, $brickClone);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Okipa\LaravelBrickables\Models\Brick $brick
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    protected function sendBrickDestroyedResponse(Request $request, Brick $brick)
    {
        return redirect()->to($request->admin_panel_url)
            ->with('success', __('The entry :model > :brickable has been deleted.', [
                'brickable' => $brick->brickable->getLabel(),
                'model' => $brick->model->getReadableClassName(),
            ]));
    }

    /**
     * @param \Okipa\LaravelBrickables\Models\Brick $brick
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function moveUp(Brick $brick, Request $request)
    {
        $brick = Brickables::castBrick($brick);
        $brick->moveOrderUp();

        return $this->sendBrickMovedResponse($request, $brick->fresh());
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Okipa\LaravelBrickables\Models\Brick $brick
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function sendBrickMovedResponse(Request $request, Brick $brick)
    {
        return redirect()->to($request->admin_panel_url);
    }

    /**
     * @param \Okipa\LaravelBrickables\Models\Brick $brick
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function moveDown(Brick $brick, Request $request)
    {
        $brick = Brickables::castBrick($brick);
        $brick->moveOrderDown();

        return $this->sendBrickMovedResponse($request, $brick->fresh());
    }
}
