<?php

namespace Okipa\LaravelBrickables\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Okipa\LaravelBrickables\Abstracts\Brickable;
use Okipa\LaravelBrickables\Facades\Brickables;
use Okipa\LaravelBrickables\Models\Brick;
use Okipa\LaravelBrickables\Tests\BrickableTestCase;
use Okipa\LaravelBrickables\Tests\Models\BrickModel;
use Okipa\LaravelBrickables\Tests\Models\BrickModelWithCompanyRelationShip;
use Okipa\LaravelBrickables\Tests\Models\Company;
use Okipa\LaravelBrickables\Tests\Models\Page;

class BrickablesTest extends BrickableTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_only_displays_once_css_resources(): void
    {
        view()->addNamespace('laravel-brickables', 'tests/views');
        $brickableOne = new class extends Brickable {
            public function setBrickViewPath(): string
            {
                return 'laravel-brickables::brick-test';
            }

            protected function setCssResourcePath(): string
            {
                return 'my/test/css/path/brickable-one.css';
            }

            public function validateStoreInputs(): array
            {
                return [];
            }

            public function validateUpdateInputs(): array
            {
                return [];
            }
        };
        $brickableTwo = new class extends Brickable {
            public function setBrickViewPath(): string
            {
                return 'laravel-brickables::brick-test';
            }

            protected function setCssResourcePath(): string
            {
                return 'my/test/css/path/brickable-two.css';
            }

            public function validateStoreInputs(): array
            {
                return [];
            }

            public function validateUpdateInputs(): array
            {
                return [];
            }
        };
        config()->set('brickables.registered', [get_class($brickableOne), get_class($brickableTwo)]);
        $page = factory(Page::class)->create();
        $page->addBricks([
            [get_class($brickableOne), ['custom' => 'first-brickable-one']],
            [get_class($brickableTwo), ['custom' => 'first-brickable-two']],
            [get_class($brickableOne), ['custom' => 'second-brickable-one']],
            [get_class($brickableTwo), ['custom' => 'second-brickable-two']],
        ]);
        $html = view('laravel-brickables::page-test', compact('page', 'brickableOne', 'brickableTwo'))->toHtml();
        self::assertStringNotContainsString($html, 'javascript');
        $headHtmlContent = Str::beforeLast(Str::after($html, '<head>'), '</head>');
        self::assertEquals(1, substr_count($headHtmlContent, 'my/test/css/path/brickable-one.css'));
        self::assertEquals(1, substr_count($headHtmlContent, 'my/test/css/path/brickable-two.css'));
    }

    /** @test */
    public function it_only_displays_once_js_resources(): void
    {
        view()->addNamespace('laravel-brickables', 'tests/views');
        $brickableOne = new class extends Brickable {
            public function setBrickViewPath(): string
            {
                return 'laravel-brickables::brick-test';
            }

            protected function setJsResourcePath(): string
            {
                return 'my/test/js/path/brickable-one.js';
            }

            public function validateStoreInputs(): array
            {
                return [];
            }

            public function validateUpdateInputs(): array
            {
                return [];
            }
        };
        $brickableTwo = new class extends Brickable {
            public function setBrickViewPath(): string
            {
                return 'laravel-brickables::brick-test';
            }

            protected function setJsResourcePath(): string
            {
                return 'my/test/js/path/brickable-two.js';
            }

            public function validateStoreInputs(): array
            {
                return [];
            }

            public function validateUpdateInputs(): array
            {
                return [];
            }
        };
        config()->set('brickables.registered', [get_class($brickableOne), get_class($brickableTwo)]);
        $page = factory(Page::class)->create();
        $page->addBricks([
            [get_class($brickableOne), ['custom' => 'first-brickable-one']],
            [get_class($brickableTwo), ['custom' => 'first-brickable-two']],
            [get_class($brickableOne), ['custom' => 'second-brickable-one']],
            [get_class($brickableTwo), ['custom' => 'second-brickable-two']],
        ]);
        $html = view('laravel-brickables::page-test', compact('page', 'brickableOne', 'brickableTwo'))->toHtml();
        $bodyHtmlContent = Str::beforeLast(Str::after($html, '<body>'), '</body>');
        self::assertStringNotContainsString($html, 'css');
        self::assertEquals(1, substr_count($bodyHtmlContent, 'my/test/js/path/brickable-one.js'));
        self::assertEquals(1, substr_count($bodyHtmlContent, 'my/test/js/path/brickable-two.js'));
    }

    /** @test */
    public function it_can_cast_bricks_to_their_brickable_related_brick_model(): void
    {
        $brickableOne = new class extends Brickable {
            public function validateStoreInputs(): array
            {
                return [];
            }

            public function validateUpdateInputs(): array
            {
                return [];
            }
        };
        $brickableTwo = new class extends Brickable {
            protected function setBrickModelClass(): string
            {
                return BrickModel::class;
            }

            public function validateStoreInputs(): array
            {
                return [];
            }

            public function validateUpdateInputs(): array
            {
                return [];
            }
        };
        config()->set('brickables.registered', [get_class($brickableOne), get_class($brickableTwo)]);
        $page = factory(Page::class)->create();
        $page->addBricks([[get_class($brickableOne)], [get_class($brickableTwo)]]);
        $bricks = Brick::all();
        self::assertEquals(
            Brick::class,
            $bricks->where('brickable_type', get_class($brickableOne))->first()->getMorphClass()
        );
        self::assertEquals(
            Brick::class,
            $bricks->where('brickable_type', get_class($brickableTwo))->first()->getMorphClass()
        );
        $bricks = Brickables::castBricks($bricks);
        self::assertCount(2, $bricks);
        self::assertInstanceOf(Brick::class, $bricks->where('brickable_type', get_class($brickableOne))->first());
        self::assertInstanceOf(BrickModel::class, $bricks->where('brickable_type', get_class($brickableTwo))->first());
    }

    /** @test */
    public function it_can_cast_bricks_and_return_them_in_correct_order(): void
    {
        $brickableOne = new class extends Brickable {
            public function validateStoreInputs(): array
            {
                return [];
            }

            public function validateUpdateInputs(): array
            {
                return [];
            }
        };
        $brickableTwo = new class extends Brickable {
            protected function setBrickModelClass(): string
            {
                return BrickModel::class;
            }

            public function validateStoreInputs(): array
            {
                return [];
            }

            public function validateUpdateInputs(): array
            {
                return [];
            }
        };
        config()->set('brickables.registered', [get_class($brickableOne), get_class($brickableTwo)]);
        $page = factory(Page::class)->create();
        $page->addBrick(get_class($brickableOne));
        $page->addBrick(get_class($brickableTwo));
        $page->addBrick(get_class($brickableOne));
        $page->addBrick(get_class($brickableTwo));
        $bricks = Brickables::castBricks(Brick::all());
        self::assertEquals(['1', '2', '3', '4'], $bricks->pluck('position')->toArray());
    }

    /** @test */
    public function it_can_cast_brick_to_its_brickable_related_brick_model(): void
    {
        $brickable = new class extends Brickable {
            protected function setBrickModelClass(): string
            {
                return BrickModel::class;
            }

            public function validateStoreInputs(): array
            {
                return [];
            }

            public function validateUpdateInputs(): array
            {
                return [];
            }
        };
        config()->set('brickables.registered', [get_class($brickable)]);
        $page = factory(Page::class)->create();
        $page->addBricks([[get_class($brickable), []]]);
        $brick = Brick::first();
        self::assertEquals(
            Brick::class,
            $brick->where('brickable_type', get_class($brickable))->first()->getMorphClass()
        );
        $brick = Brickables::castBrick($brick);
        self::assertInstanceOf(BrickModel::class, $brick->where('brickable_type', get_class($brickable))->first());
    }

    /** @test */
    public function it_can_cast_bricks_and_eager_load_relationships(): void
    {
        $brickable = new class extends Brickable {
            protected function setBrickModelClass(): string
            {
                return BrickModelWithCompanyRelationShip::class;
            }

            public function validateStoreInputs(): array
            {
                return [];
            }

            public function validateUpdateInputs(): array
            {
                return [];
            }
        };
        config()->set('brickables.registered', [get_class($brickable)]);
        $page = factory(Page::class)->create();
        $company = factory(Company::class)->create();
        $brick = $page->addBrick(get_class($brickable));
        $brick->companies()->sync([$company->id]);
        $rawBrick = $brick = Brick::first();
        $castedBrick = Brickables::castBrick($brick);
        // The relation does not exist on the raw brick.
        self::assertNull($rawBrick->companies);
        // Companies relationship is loaded after the brick is casted.
        self::assertEquals($company->toArray(), Arr::except($castedBrick->toArray()['companies'][0], 'pivot'));
    }

    /** @test */
    public function it_can_get_model_from_create_request(): void
    {
        $page = factory(Page::class)->create();
        $request = (new Request())->merge(['model_type' => $page->getMorphClass(), 'model_id' => $page->id]);
        $model = Brickables::getModelFromRequest($request);
        self::assertTrue($page->is($model));
    }

    /** @test */
    public function it_can_get_model_from_edit_request(): void
    {
        $brickable = new class extends Brickable {
            public function validateStoreInputs(): array
            {
                return [];
            }

            public function validateUpdateInputs(): array
            {
                return [];
            }
        };
        config()->set('brickables.registered', [get_class($brickable)]);
        $page = factory(Page::class)->create();
        $brick = $page->addBrick(get_class($brickable), []);
        $request = (new Request())->merge(['brick' => $brick]);
        $model = Brickables::getModelFromRequest($request);
        self::assertTrue($page->is($model));
    }
}
