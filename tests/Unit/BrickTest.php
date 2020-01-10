<?php

namespace Okipa\LaravelBrickables\Tests\Unit;

use Okipa\LaravelBrickables\Tests\BrickableTestCase;
use Okipa\LaravelBrickables\Tests\Models\Page;

class BrickTest extends BrickableTestCase
{
    /** @test */
    public function it_returns_brick_type_label()
    {
        $page = factory(Page::class)->create();
        $brick = $page->addBrick('oneTextColumn', ['content' => 'Text content']);
        $this->assertEquals(config('brickables.types.oneTextColumn.label'), $brick->getBrickableLabel());
    }

    /** @test */
    public function it_returns_brick_type_view_path()
    {
        $page = factory(Page::class)->create();
        $brick = $page->addBrick('oneTextColumn', ['content' => 'Text content']);
        $this->assertEquals(config('brickables.types.oneTextColumn.view'), $brick->getBrickableViewPath());
    }
}