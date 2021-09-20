<?php

namespace Okipa\LaravelBrickables\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Okipa\LaravelBrickables\Abstracts\Brickable;
use Okipa\LaravelBrickables\Brickables\OneTextColumn;
use Okipa\LaravelBrickables\Brickables\TwoTextColumns;
use Okipa\LaravelBrickables\Contracts\HasBrickables;
use Okipa\LaravelBrickables\Exceptions\BrickableCannotBeHandledException;
use Okipa\LaravelBrickables\Exceptions\InvalidBrickableClassException;
use Okipa\LaravelBrickables\Exceptions\ModelHasReachedMaxNumberOfBricksException;
use Okipa\LaravelBrickables\Exceptions\NotRegisteredBrickableClassException;
use Okipa\LaravelBrickables\Facades\Brickables;
use Okipa\LaravelBrickables\Models\Brick;
use Okipa\LaravelBrickables\Tests\BrickableTestCase;
use Okipa\LaravelBrickables\Tests\Models\HasMultipleConstrainedBrickablesModel;
use Okipa\LaravelBrickables\Tests\Models\HasOneConstrainedBrickableModel;
use Okipa\LaravelBrickables\Tests\Models\Page;
use Okipa\LaravelBrickables\Traits\HasBrickablesTrait;

class HasBrickablesTest extends BrickableTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_cannot_add_invalid_brickable_class(): void
    {
        $brickable = new class {
            //
        };
        $page = factory(Page::class)->create();
        $this->expectException(InvalidBrickableClassException::class);
        $page->addBrick(get_class($brickable), []);
    }

    /** @test */
    public function it_cannot_add_not_registered_brickable_class(): void
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
        $page = factory(Page::class)->create();
        $this->expectException(NotRegisteredBrickableClassException::class);
        $page->addBrick(get_class($brickable), []);
    }

    public function it_cannot_add_not_handlable_brickable(): void
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
        $model = app(HasOneConstrainedBrickableModel::class)->create();
        $model->addBrick(get_class($brickable));
        $this->expectException(BrickableCannotBeHandledException::class);
    }

    /** @SuppressWarnings(PHPMD.UnusedLocalVariable) */
    public function it_can_check_if_model_can_handle_brickable(): void
    {
        $model = new class extends Model implements HasBrickables {
            use HasBrickablesTrait;

            public array $brickables = [
                'can_only_handle' => [OneTextColumn::class],
            ];
        };
        self::assertTrue($model->canHandle(OneTextColumn::class));
        self::assertFalse($model->canHandle(TwoTextColumns::class));
    }

    /** @test */
    public function it_can_add_brick(): void
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
        self::assertTrue($brick->is(Brick::first()));
    }

    /** @test */
    public function it_can_add_brick_with_a_custom_brick_model(): void
    {
        $brickModel = new class extends Brick {
            public function dummy(): string
            {
                return 'dummy';
            }
        };
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
        config()->set('brickables.bricks.model', get_class($brickModel));
        config()->set('brickables.registered', [get_class($brickable)]);
        $page = factory(Page::class)->create();
        $brick = $page->addBrick(get_class($brickable), []);
        self::assertTrue($brick->is($brickModel->first()));
        self::assertEquals('dummy', $brick->dummy());
    }

    /** @test */
    public function it_can_add_bricks_with_max_number_not_exceeded(): void
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
        config()->set('brickables.registered', [
            OneTextColumn::class,
            TwoTextColumns::class,
            get_class($brickable),
        ]);
        $model = app(HasMultipleConstrainedBrickablesModel::class)->create();
        $brick1 = $model->addBrick(OneTextColumn::class);
        $brick2 = $model->addBrick(TwoTextColumns::class);
        $brick3 = $model->addBrick(get_class($brickable));
        $bricks = Brick::all();
        self::assertTrue($brick1->is($bricks->get(0)));
        self::assertTrue($brick2->is($bricks->get(1)));
        self::assertTrue($brick3->is($bricks->get(2)));
    }

    /**
     * @test
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function it_cant_add_bricks_with_max_number_defined_to_zero(): void
    {
        $model = new class extends HasMultipleConstrainedBrickablesModel {
            public array $brickables = [
                'number_of_bricks' => [
                    OneTextColumn::class => ['max' => 0],
                ],
            ];
        };
        $instance = $model->create();
        config()->set('brickables.registered', [OneTextColumn::class]);
        $this->expectException(ModelHasReachedMaxNumberOfBricksException::class);
        $instance->addBrick(OneTextColumn::class);
    }

    /** @test */
    public function it_can_add_bricks(): void
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
        $bricks = $page->addBricks([[get_class($brickable), []], [get_class($brickable), []]]);
        self::assertCount(2, $bricks);
        self::assertEmpty(Brick::all()->diff($bricks));
    }

    /** @test */
    public function it_can_can_get_bricks(): void
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
        $page->addBricks([[get_class($brickable), []], [get_class($brickable), []]]);
        self::assertCount(2, Brick::all());
        self::assertEmpty(Brick::all()->diff($page->getBricks()));
    }

    /** @test */
    public function it_can_can_get_bricks_from_brickable_type(): void
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
        $otherBrickable = new class extends Brickable {
            public function validateStoreInputs(): array
            {
                return [];
            }

            public function validateUpdateInputs(): array
            {
                return [];
            }
        };
        config()->set('brickables.registered', [get_class($brickable), get_class($otherBrickable)]);
        $page = factory(Page::class)->create();
        $page->addBricks([[get_class($brickable)], [get_class($otherBrickable)]]);
        self::assertCount(1, $page->getBricks([get_class($brickable)]));
        self::assertEmpty(Brick::where('brickable_type', get_class($brickable))->get()
            ->diff($page->getBricks([get_class($brickable)])));
    }

    /** @test */
    public function it_can_can_get_brickable_types_bricks(): void
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
        $otherBrickable = new class extends Brickable {
            public function validateStoreInputs(): array
            {
                return [];
            }

            public function validateUpdateInputs(): array
            {
                return [];
            }
        };
        config()->set('brickables.registered', [get_class($brickable), get_class($otherBrickable)]);
        $page = factory(Page::class)->create();
        $page->addBricks([[get_class($brickable)], [get_class($otherBrickable)]]);
        self::assertCount(1, $page->getBricks([get_class($brickable)]));
        self::assertEmpty(Brick::where('brickable_type', get_class($brickable))
            ->get()
            ->diff($page->getBricks([get_class($brickable)])));
    }

    /** @test */
    public function it_can_return_first_brick(): void
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
        $otherBrickable = new class extends Brickable {
            public function validateStoreInputs(): array
            {
                return [];
            }

            public function validateUpdateInputs(): array
            {
                return [];
            }
        };
        config()->set('brickables.registered', [get_class($brickable), get_class($otherBrickable)]);
        $page = factory(Page::class)->create();
        $page->addBricks([
            [get_class($brickable), ['text' => 'Text #1']],
            [get_class($otherBrickable), ['text' => 'Text #2']],
            [get_class($brickable), ['text' => 'Text #3']],
        ]);
        $brick = $page->getFirstBrick();
        self::assertTrue($brick->is(Brick::where('data->text', 'Text #1')->first()));
    }

    /** @test */
    public function it_can_return_first_brick_from_brickable_type(): void
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
        $otherBrickable = new class extends Brickable {
            public function validateStoreInputs(): array
            {
                return [];
            }

            public function validateUpdateInputs(): array
            {
                return [];
            }
        };
        config()->set('brickables.registered', [get_class($brickable), get_class($otherBrickable)]);
        $page = factory(Page::class)->create();
        $page->addBricks([
            [get_class($brickable), ['text' => 'Text #1']],
            [get_class($otherBrickable), ['text' => 'Text #2']],
            [get_class($brickable), ['text' => 'Text #3']],
        ]);
        $brick = $page->getFirstBrick(get_class($otherBrickable));
        self::assertTrue($brick->is(Brick::where('data->text', 'Text #2')->first()));
    }

    /** @test */
    public function it_can_return_readable_class_name(): void
    {
        self::assertEquals(
            'Has one constrained brickable model',
            app(HasOneConstrainedBrickableModel::class)->getReadableClassName()
        );
    }

    /** @test */
    public function it_can_clear_bricks(): void
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
        $otherBrickable = new class extends Brickable {
            public function validateStoreInputs(): array
            {
                return [];
            }

            public function validateUpdateInputs(): array
            {
                return [];
            }
        };
        config()->set('brickables.registered', [get_class($brickable), get_class($otherBrickable)]);
        $page = factory(Page::class)->create();
        $page->addBricks([
            [get_class($brickable), ['text' => 'Text #1']],
            [get_class($brickable), ['text' => 'Text #2']],
            [get_class($otherBrickable), ['text' => 'Text #3']],
        ]);
        self::assertEquals(3, Brick::count());
        $page->clearBricks();
        $bricks = Brick::all();
        self::assertEquals(0, $bricks->count());
    }

    /** @test */
    public function it_can_clear_bricks_from_brickable_type(): void
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
        $otherBrickable = new class extends Brickable {
            public function validateStoreInputs(): array
            {
                return [];
            }

            public function validateUpdateInputs(): array
            {
                return [];
            }
        };
        config()->set('brickables.registered', [get_class($brickable), get_class($otherBrickable)]);
        $page = factory(Page::class)->create();
        $page->addBricks([
            [get_class($brickable), ['text' => 'Text #1']],
            [get_class($brickable), ['text' => 'Text #2']],
            [get_class($otherBrickable), ['text' => 'Text #3']],
        ]);
        self::assertEquals(3, Brick::count());
        $page->clearBricks([get_class($brickable)]);
        $bricks = Brick::all();
        self::assertEquals(1, $bricks->count());
        self::assertEquals(get_class($otherBrickable), $bricks->first()->brickable_type);
    }

    /** @test */
    public function it_can_clear_bricks_except(): void
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
        $otherBrickable = new class extends Brickable {
            public function validateStoreInputs(): array
            {
                return [];
            }

            public function validateUpdateInputs(): array
            {
                return [];
            }
        };
        config()->set('brickables.registered', [get_class($brickable), get_class($otherBrickable)]);
        $page = factory(Page::class)->create();
        $page->addBricks([
            [get_class($brickable), ['text' => 'Text #1']],
            [get_class($brickable), ['text' => 'Text #2']],
            [get_class($otherBrickable), ['text' => 'Text #3']],
        ]);
        self::assertEquals(3, Brick::count());
        $bricksToKeep = Brick::where('brickable_type', get_class($brickable))->get();
        $page->clearBricksExcept($bricksToKeep);
        $bricks = Brick::all();
        self::assertEquals($bricksToKeep->count(), $bricks->count());
        self::assertEquals($bricksToKeep, $bricks);
    }

    /** @test */
    public function it_can_clear_bricks_until_the_min_number_of_bricks(): void
    {
        $model = app(HasOneConstrainedBrickableModel::class)->create();
        $model->addBricks([[OneTextColumn::class], [OneTextColumn::class], [OneTextColumn::class]]);
        $model->clearBricks([OneTextColumn::class]);
        self::assertCount(1, Brick::all());
        self::assertEquals(3, Brick::first()->position);
    }

    /** @test */
    public function it_can_check_if_model_can_add_brick_for_brickable_type(): void
    {
        $model = app(HasOneConstrainedBrickableModel::class)->create();
        $model->addBricks([[OneTextColumn::class], [OneTextColumn::class], [OneTextColumn::class]]);
        self::assertFalse($model->canAddBricksFrom(OneTextColumn::class));
        Brick::first()->delete();
        self::assertTrue($model->canAddBricksFrom(OneTextColumn::class));
    }

    /** @test */
    public function it_can_check_if_model_can_destroy_brick_for_brickable_type(): void
    {
        $model = app(HasOneConstrainedBrickableModel::class)->create();
        $model->addBricks([[OneTextColumn::class], [OneTextColumn::class], [OneTextColumn::class]]);
        self::assertTrue($model->canDeleteBricksFrom(OneTextColumn::class));
        Brick::first()->delete();
        self::assertTrue($model->canDeleteBricksFrom(OneTextColumn::class));
        Brick::first()->delete();
        self::assertFalse($model->canDeleteBricksFrom(OneTextColumn::class));
    }

    /** @test */
    public function it_can_return_all_registered_brickables(): void
    {
        $model = app(HasOneConstrainedBrickableModel::class)->create();
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
        self::assertCount(count(config('brickables.registered')), $model->getRegisteredBrickables());
    }

    /** @test */
    public function it_can_return_brickables_that_can_be_added_to_model(): void
    {
        $instance = factory(Page::class)->create();
        config()->set('brickables.registered', [OneTextColumn::class, TwoTextColumns::class]);
        $additionableBbrickables = $instance->getAdditionableBrickables();
        self::assertCount(2, $additionableBbrickables);
    }

    /**
     * @test
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function it_can_return_brickables_that_can_be_added_to_model_with_one_disabled(): void
    {
        $model = new class extends HasMultipleConstrainedBrickablesModel {
            public array $brickables = [
                'number_of_bricks' => [
                    TwoTextColumns::class => ['max' => 0],
                ],
            ];
        };
        $instance = $model->create();
        config()->set('brickables.registered', [OneTextColumn::class, TwoTextColumns::class]);
        $additionableBbrickables = $instance->getAdditionableBrickables();
        self::assertCount(1, $additionableBbrickables);
        self::assertInstanceOf(OneTextColumn::class, $additionableBbrickables->first());
    }

    /**
     * @test
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function it_can_return_brickables_that_can_be_added_to_model_with_one_at_reached_max_number(): void
    {
        $model = new class extends HasMultipleConstrainedBrickablesModel {
            public array $brickables = [
                'number_of_bricks' => [
                    OneTextColumn::class => ['min' => 1, 'max' => 1],
                ],
            ];
        };
        $instance = $model->create();
        config()->set('brickables.registered', [OneTextColumn::class, TwoTextColumns::class]);
        $instance->addBrick(OneTextColumn::class);
        $additionableBbrickables = $instance->getAdditionableBrickables();
        self::assertCount(1, $additionableBbrickables);
        self::assertInstanceOf(TwoTextColumns::class, $additionableBbrickables->first());
    }

    /** @test */
    public function it_can_display_model_bricks_html(): void
    {
        view()->addNamespace('laravel-brickables', 'tests/views');
        $brickable = new class extends Brickable {
            public function setBrickViewPath(): string
            {
                return 'laravel-brickables::brick-test';
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
        $page->addBrick(get_class($brickable), ['custom' => 'dummy']);
        self::assertEquals(
            view('laravel-brickables::bricks', [
                'model' => $page,
                'brickableClasses' => [get_class($brickable)],
            ])->toHtml(),
            $page->displayBricks([get_class($brickable)])
        );
        self::assertEquals(
            view('laravel-brickables::bricks', [
                'model' => $page,
                'brickableClasses' => [],
            ])->toHtml(),
            $page->displayBricks()
        );
    }

    /** @test */
    public function it_can_display_model_admin_panel_html(): void
    {
        Brickables::routes();
        view()->addNamespace('laravel-brickables', 'tests/views');
        $brickable = new class extends Brickable {
            public function setBrickViewPath(): string
            {
                return 'laravel-brickables::brick-test';
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
        $page->addBrick(get_class($brickable), ['custom' => 'dummy']);
        self::assertEquals(
            view('laravel-brickables::admin.panel.layout', ['model' => $page])->toHtml(),
            $page->displayAdminPanel()
        );
    }

    /** @test */
    public function it_can_load_additional_stylesheets_on_admin_panel_html(): void
    {
        Brickables::routes();
        view()->addNamespace('laravel-brickables', 'tests/views');
        config()->set('brickables.additional_stylesheets.admin_panel', [
            '/first/path/to/test/stylesheet.css',
            '/second/path/to/test/stylesheet.css',
        ]);
        $brickable = new class extends Brickable {
            public function setBrickViewPath(): string
            {
                return 'laravel-brickables::brick-test';
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
        $page->addBrick(get_class($brickable), ['custom' => 'dummy']);
        self::assertStringContainsString(
            '<link href="/first/path/to/test/stylesheet.css" rel="stylesheet"/>',
            $page->displayAdminPanel()
        );
        self::assertStringContainsString(
            '<link href="/second/path/to/test/stylesheet.css" rel="stylesheet"/>',
            $page->displayAdminPanel()
        );
    }
}
