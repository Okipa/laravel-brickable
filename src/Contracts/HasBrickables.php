<?php

namespace Okipa\LaravelBrickables\Contracts;

use Illuminate\Support\Collection;
use Okipa\LaravelBrickables\Models\Brick;

interface HasBrickables
{
    /**
     * @throws \Okipa\LaravelBrickables\Exceptions\BrickableCannotBeHandledException
     * @throws \Okipa\LaravelBrickables\Exceptions\InvalidBrickableClassException
     * @throws \Okipa\LaravelBrickables\Exceptions\NotRegisteredBrickableClassException
     */
    public function addBricks(array $bricksData): Collection;

    /**
     * @throws \Okipa\LaravelBrickables\Exceptions\BrickableCannotBeHandledException
     * @throws \Okipa\LaravelBrickables\Exceptions\InvalidBrickableClassException
     * @throws \Okipa\LaravelBrickables\Exceptions\NotRegisteredBrickableClassException
     */
    public function addBrick(string $brickableClass, array $data = []): Brick;

    /** @throws \Okipa\LaravelBrickables\Exceptions\InvalidBrickableClassException */
    public function checkBrickableTypeIsInstanceOfBrickable(string $brickableClass): void;

    /** @throws \Okipa\LaravelBrickables\Exceptions\BrickableCannotBeHandledException */
    public function checkBrickableCanBeHandled(string $brickableClass): void;

    public function canHandle(string $brickableClass): bool;

    public function getBricks(?array $brickableClasses = []): Collection;

    public function clearBricksExcept(Collection $excludeBricks): void;

    public function clearBricks(?array $brickableClasses = []): void;

    public function getFirstBrick(?string $brickableClass = null): ?Brick;

    public function getReadableClassName(): string;

    public function canDeleteBricksFrom(string $brickableClass): bool;

    public function getAdditionableBrickables(): Collection;

    public function getRegisteredBrickables(): Collection;

    public function canAddBricksFrom(string $brickableClass): bool;

    public function displayBricks(array $brickableClasses = []): string;

    public function displayAdminPanel(): string;
}
