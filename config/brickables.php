<?php

return [

    /*
     * The fully qualified class names of the classes that will be used for the brickables management.
     * Each class can be overridden in each brickable itself to customize treatments.
     */
    'bricks' => [
        'model' => Okipa\LaravelBrickables\Models\Brick::class,
        'controller' => Okipa\LaravelBrickables\Controllers\BricksController::class,
    ],

    /*
     * Register here the available brickables.
     * Brickables will not be available for use if they are not declared here.
     */
    'registered' => [
        Okipa\LaravelBrickables\Brickables\OneTextColumn::class,
        Okipa\LaravelBrickables\Brickables\TwoTextColumns::class,
        // Add additional brickables here...
    ],

    /*
     * In order to give bricks the style they look on the front on your brickables admin panel,
     * you may want to embed some front stylesheets.
     */
    'additional_stylesheets' => [
        'admin_panel' => [
            // Add additional stylesheets here...
            // mix('your/frontend/styles'),
        ],
    ],

];
