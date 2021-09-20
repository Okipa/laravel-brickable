![Laravel Brickables](/docs/laravel-brickables.png)
<p align="center">
    <a href="https://github.com/Okipa/laravel-brickables/releases" title="Latest Stable Version">
        <img src="https://img.shields.io/github/release/Okipa/laravel-brickables.svg" alt="Latest Stable Version">
    </a>
    <a href="https://packagist.org/packages/Okipa/laravel-brickables" title="Total Downloads">
        <img src="https://img.shields.io/packagist/dt/okipa/laravel-brickables.svg" alt="Total Downloads">
    </a>
    <a href="https://github.com/Okipa/laravel-brickables/actions" title="Build Status">
        <img src="https://github.com/Okipa/laravel-brickables/workflows/CI/badge.svg" alt="Build Status">
    </a>
    <a href="https://coveralls.io/github/Okipa/laravel-brickables?branch=master" title="Coverage Status">
        <img src="https://coveralls.io/repos/github/Okipa/laravel-brickables/badge.svg?branch=master" alt="Coverage Status">
    </a>
    <a href="/LICENSE.md" title="License: MIT">
        <img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="License: MIT">
    </a>
</p>

This package allows you to associate content bricks to Eloquent models and provides a fully customizable admin panel to manage them.

This package is shipped with few pre-built brickables. You can use them as is but you definitely should consider them as examples: customizing them or create new ones has been designed to be simple as hell! :fire:

Found this package helpful? Please consider supporting my work!

[![Donate](https://img.shields.io/badge/Buy_me_a-Ko--fi-ff5f5f.svg)](https://ko-fi.com/arthurlorent)
[![Donate](https://img.shields.io/badge/Donate_on-PayPal-green.svg)](https://paypal.me/arthurlorent)

## Compatibility

| Laravel | PHP | Package |
|---|---|---|
| ^7.0 | ^7.4 | ^2.0 |
| ^5.8 | ^7.2 | ^1.0 |

## Upgrade guide

* [From V1 to V2](/docs/upgrade-guides/from-v1-to-v2.md)

## Usage

Associate content bricks to Eloquent models:

```php
$page = Page::find(1);

// Associate one content brick
$page->addBrick(OneTextColumn::class, ['text' => 'Text']);

// Or associate several content bricks at once
$page->addBricks([
    [OneTextColumn::class, ['text' => 'Text']],
    [TwoTextColumns::class, ['text_left' => 'Left text', 'text_right' => 'Right text']]
]);
```

Display bricks in your views:

```blade
{{-- all at once --}}
{!! $page->displayBricks() !!}

{{-- or one by one --}}
{{ $page->getFirstBrick(OneTextColumn::class) }}
```

Display the model-related bricks admin panel in your views:

```blade
{{ $page->displayAdminPanel() }}
```

## Table of contents

* [Installation](#installation)
* [Configuration](#configuration)
* [Views](#views)
* [Translations](#translations)
* [Implementation](#implementation)
  * [Models](#models)
  * [Routes](#routes)
* [How to](#how-to)
  * [Define brick constraints for model](#define-brick-constraints-for-model)
  * [Add content bricks](#add-content-bricks)
  * [Update a content brick](#update-a-content-brick)
  * [Remove content bricks](#remove-content-bricks)
  * [Set content bricks order](#set-content-bricks-order)
  * [Retrieve content bricks](#retrieve-content-bricks)
  * [Query content bricks](#query-content-bricks)
  * [Display content bricks](#display-content-bricks)
  * [Retrieve brickables](#retrieve-brickables)
  * [Manage model content bricks](#manage-model-content-bricks)
  * [Create your own brickable](#create-your-own-brickable)
  * [Define brickable css and js resources](#define-brickable-css-and-js-resources)
  * [Empower bricks with extra abilities](#empower-brickables-with-extra-abilities)
  * [Get Eloquent model from Request](#get-eloquent-model-from-request)
* [Testing](#testing)
* [Changelog](#changelog)
* [Contributing](#contributing)
* [Security](#security)
* [Credits](#credits)
* [Licence](#license)

## Installation

Install the package via composer:

```bash
composer require okipa/laravel-brickables
```

Then, publish and run the database migrations: 

```bash
php artisan vendor:publish --provider="Okipa\LaravelBrickables\BrickablesServiceProvider" --tag=migrations

php artisan migrate
```

Finally, to benefit from [smart loading of brickables css and js resources](#define-brickable-css-and-js-resources), add these directives to your blade layout as shown in the example bellow:

```blade
{{-- layout.blade.php --}}
<html>
    <head>
        @brickablesCss
    </head>
    <body>
        @yield('content')
        @brickablesJs
    </body>
</html>
```

## Configuration

Publish the package configuration: 

```bash
php artisan vendor:publish --provider="Okipa\LaravelBrickables\BrickablesServiceProvider" --tag=config
```

:warning: You may have to run a `composer dump-autoload` after changing a path in your configuration file.

## Views

Publish the package views: 

```bash
php artisan vendor:publish --provider="Okipa\LaravelBrickables\BrickablesServiceProvider" --tag=views
```

## Translations

All words and sentences used in this package are translatable.

See how to translate them on the Laravel official documentation : https://laravel.com/docs/localization#using-translation-strings-as-keys.

Here is the list of the words and sentences available for translation by default:

* `Content Bricks`
* `No saved content brick.`
* `Content`
* `Left content`
* `Right content`
* `Brick data`
* `Add`
* `Edit`
* `Update`
* `Destroy`
* `Cancel`
* `Move up`
* `Move down`
* `The entry :model > :brickable has been created.`
* `The entry :model > :brickable has been updated.`
* `The entry :model > :brickable has been deleted.`

You will also have to define the `validation.attributes.brickable_types` translation.

Finally, you will have to translate each registered brickable label translation.

## Implementation

### Models

Implement the `HasBrickables` interface and use the `HasBrickablesTrait` trait to any Eloquent model that you want to be able to be associated to content bricks to.

```php
use Illuminate\Database\Eloquent\Model;
use Okipa\LaravelBrickables\Contracts\HasBrickables;
use Okipa\LaravelBrickables\Traits\HasBrickablesTrait;

class Page extends Model implements HasBrickables
{
	use HasBrickablesTrait;

	// ...
}
```

### Routes

Add the `web` routes that will be required by the content bricks admin panel:

```php
Brickables::routes();
```

These routes are consuming the `Okipa\LaravelBrickables\Controllers\BricksController` controller by default.

To customize the admin panel actions, you can add routes inside or outside of the brickables route group.

```php
Brickables::routes(function(){
    // Inside the routes group: will benefit from the CRUDBrickable middleware.
});
// Outside the route group: will not benefit from the CRUDBrickable middleware.
```

Check the [Empower bricks with extra abilities](#empower-brickables-with-extra-abilities) part to get more information about the customization possibilities.

## How to

### Define brick constraints for model

In your Eloquent model, you optionally can define constraints:

* Define the brickables that your model is being authorized to manage,
* Define the minimum number of bricks of each brickable that the model must hold,
* Define the maximum number of bricks of each brickable that the model can hold.

```php
use Illuminate\Database\Eloquent\Model;
use Okipa\LaravelBrickables\Contracts\HasBrickables;
use Okipa\LaravelBrickables\Traits\HasBrickablesTrait;

class Page extends Model implements HasBrickables
{
	use HasBrickablesTrait;

    public array $brickables = [
        'can_only_handle' => [OneTextColumn::class], // By default all registered brickables can be handled.
        'number_of_bricks' => [OneTextColumn::class => ['min' => 1, 'max' => 3]], // By default, there are no number restrictions.
    ];

	// ...
}
```

In this example:

* The `Page` model will only be allowed to handle `OneTextColumn` bricks.
* The admin panel will only allow to manage `OneTextColumn` bricks.
* The admin panel will not allow to remove a `OneTextColumn` brick if there is only one left.
* The admin panel will not allow to add more `OneTextColumn` bricks if 3 are already added.
* Programmatically clearing all bricks for this model will keep the `OneTextColumn` one with the highest position.
* Programmatically adding a 4th `OneTextColumn` brick will thrown a `ModelHasReachedMaxNumberOfBricksException`.

**Important note:** you can disable a brickable management for a model by setting its max number to `0`.

### Add content bricks

Associate a single content brick to an Eloquent model:

```php
$brick = $page->addBrick(OneTextColumn::class, ['text' => 'Text']);
```

You also can associate several content bricks at once:

```php
$bricks = $page->addBricks([
    [OneTextColumn::class, ['text' => 'Text']],
    [TwoTextColumns::class, ['text_left' => 'Left text', 'text_right' => 'Right text']]
]);
```

### Update a content brick

Just update your content brick as you would fo for any other Eloquent model instance:

```php
// As data are stored in json, you will have to process this way: https://github.com/laravel/framework/pull/15464#issuecomment-247642772.
$brick->data = ['text' => 'Another text'];
$brick->save();
```

### Remove content bricks

Just delete your content brick as you would fo for any other Eloquent model instance:

```php
$brick->delete();
```

Clear all the content bricks associated to an Eloquent model, or only those with specific brickable types:

```php
$page->clearBricks();

$page->clearBricks([OneTextColumn::class]);
```

Clear all the content bricks except specific ones:

```php
$page->clearBricksExcept($bricksCollection);
```

**Note**

* According to the [number of bricks constraints](#define-brick-constraints-for-model) defined in the Eloquent model, these methods could be brought to keep the min number of bricks instead of removing the targeted brick(s).

### Set content bricks order

By default, all bricks are ordered by their creation order (last created at the end).

The `Brick` model uses the `spatie/eloquent-sortable` package to handle the content bricks positioning.

To see how to use this third party package, check its documentation here: https://github.com/spatie/eloquent-sortable.

You may note that the bricks order management is already handled in the provided [admin panel](#manage-model-content-bricks) and that you can use it as is.

### Retrieve content bricks

Retrieve all the content bricks associated to an Eloquent model, or only those with a specific brickable type:

```php
$bricks = $page->getBricks();

$bricks = $page->getBricks(OneTextColumn::class);
```

Get the first content brick associated to an Eloquent model, or the one with a specific brickable type:

```php
$brick = $page->getFirstBrick();

$brick = $page->getFirstBrick(OneTextColumn::class);
```

### Query content bricks

As each brickable can specify its own brick model, you should query content bricks and then cast them ito the model defined in their related brickable:

```php
$rawBricks = Brick::where('model_type', Page::class)->where('model_id', 1)->where('brickable_type', OneTextColumn::class)->get();
$bricks = Brickables::castBricks($rawBricks);
```

### Display content bricks

Display a single content brick in your view:

```blade
{{ $page->getFirstBrick(OneTextColumn::class) }}
```

Or display all the content bricks associated to an Eloquent model:

```blade
{!! $page->displayBricks() !!}
```

Or only display content bricks from given brickable types:

```blade
{!! $page->displayBricks([OneTextColumn::class, TwoTextColumns::class]) !!}
```

### Retrieve brickables

Get all the registered brickables:

```php
$registeredBrickables = $page->getRegisteredBrickables();
```

Get all the brickables that are allowed to be added to an Eloquent model:

```php
$additionableBrickables = $page->getAdditionableBrickables();
```

Retrieve a brickable from a brick instance:

```php
$brickable = $page->getFirstBrick(OneTextColumn::class)->brickable;
```

### Manage model content bricks

Use the ready-to-use admin panel to manage related-model content bricks:

```blade
{!! $page->displayAdminPanel() !!}
```

To give bricks the style they have on the front, you may be interested to load frontend stylesheets in the `brickables.additional_stylesheets.admin_panel` config value. These stylesheets will be loaded on top of the brickables admin panel.

**:bulb: Tips**

* Customize the admin panel views by [publishing them](#views)
* To prevent you entire admin interface from taking styles from the front, please note that you should always load you brickables admin panel and the end of your pages
* It is highly recommended adding a javascript confirmation request to intercept the content bricks delete action, otherwise the removal action will be directly executed without asking the user agreement.

### Create your own brickable

Create a new brickable class that extends class in your `app/vendor/Brickables` directory.

In your brickable class, you can override any method from the extended abstract `Brickable` to customize the brickable behaviour. 

```php
<?php

namespace App\Vendor\LaravelBrickables\Brickables;

use Okipa\LaravelBrickables\Abstracts\Brickable;

class MyNewBrickable extends Brickable
{
    public function validateStoreInputs(): array
    {
        return request()->validate(['text' => ['required', 'string']]);
    }

    public function validateUpdateInputs(): array
    {
        return request()->validate(['text' => ['required', 'string']]);
    }
    
    // Alternative example: use a form request to validate your inputs and return the validated fields.
    //public function validateStoreInputs(): array
    //{
    //    return app(MyNewBrickableStoreFormRequest::class)->validated();
    //}
}
```

Then, register it in your `config('brickables.registered')` array:

```php
<?php

return [

    'registered' => [
        // Other brickables declarations...
        App\Vendor\LaravelBrickables\Brickables\MyNewBrickable::class,
    ],
];

```

Finally, create the brickable views in the `resources/views/vendor/laravel-brickables/my-new-brickable` directory (you can customize the view paths in your `MyNewBrickable` class):

* the `brick` view that will be used to display your `MyNewBrickable` brick in the front.
* the `form` view which will embed the form inputs that will be used to CRUD your `MyNewBrickable` bricks in the admin panel.

You should see the existing brickables implementation to get familiar with their management.

Your brickable is now ready to be associated to Eloquent models.

### Define brickable css and js resources

You have the possibility to define a css and js resource to customize each brickable rendering.
 
In addition, this package embeds a smart resource management system : it determines which brickables are actually displayed on the page and only loads the resources once, even if a brickable is used more than once on the page.

To benefit from this feature, make sure you have implemented the `@brickablesCss` and the `@brickablesJs` directives as precised in the [installation](#installation) part.
 
Then, define which resources your brickables are using:

```php
<?php

namespace App\Vendor\LaravelBrickables\Brickables;

use Okipa\LaravelBrickables\Abstracts\Brickable;

class MyNewBrickable extends Brickable
{
    // ...

    protected function setCssResourcePath(): ?string
    {
        return mix('/css/brickables/my-new-brickable.css');
    }

    protected function setJsResourcePath(): ?string
    {
        return mix('/js/brickables/my-new-brickable.js');
    }
}
```

Finally, use the `@brickableResourcesCompute` directive under the last displayed brick in the page:

```blade
    {{-- page.blade.php --}}
    @extends('laravel-brickables::layout')
    @section('content')
        {{ $page->getFirstBrick(OneTextColumn::class) }}
        {{ $page->displayBricks([TwoTextColumns::class]) }}
        @brickableResourcesCompute
    @endsection
```

**:warning: Important:** Please note that you will always have to declare the `@brickableResourcesCompute` directive from a child view from the one which is declaring the `brickablesCss` and `@brickablesJs` directives. As you can see in our example, the `@brickableResourcesCompute` blade directive is called on the `page.blade.php` view, which is a child of `layout.blade.php` (where the `brickablesCss` and `@brickablesJs` are declared). This is the only way for this package to know which brickables are actually displayed on the page, in order to intelligently load the resources.

### Empower brickables with extra abilities

To add abilities to your brickables, you will have to implement the additional treatments in the brickable-related brick model and brick controller.

Let's add the ability to manage images in our `MyNewBrickable` from the previous example.

First create a `MyNewBrickableBrick` model that will extend the `Okipa\LaravelBrickables\Models\Brick` one in order to give the image management ability to this brick.

```php
<?php

namespace App;

use Okipa\LaravelBrickables\Models\Brick;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class MyNewBrickableBrick extends Brick implements HasMedia
{
    // Image management example with the spatie/laravel-medialibrary package.
    use InteractsWithMedia;
    
    // Optimize query by eager loading media relations.
    protected $with = ['media'];
    
    // ...
}
```

Then, create a `MyNewBrickableBricksController` model which will extend the `Okipa\LaravelBrickables\Controllers\BricksController` one where you will add the image management treatments.

```php
<?php

namespace App\Http\Controllers;

use Okipa\LaravelBrickables\Controllers\BricksController;

class MyNewBrickableBricksController extends BricksController
{

    // ...    

    protected function stored(Request $request, Brick $brick): void
    {
        // Image management example with the spatie/laravel-medialibrary package
        $brick->addMediaFromRequest('image')->toMediaCollection('bricks');
    }

    protected function updated(Request $request, Brick $brick): void
    {
        // Image management example with the spatie/laravel-medialibrary package
        if ($request->file('image')) {
            $brick->addMediaFromRequest('image')->toMediaCollection('bricks');
        }
    }

    // ...

}
```

Do not forget to validate your form inputs:

```php
class MyNewBrickable extends Brickable
{
    public function validateStoreInputs(): array
    {
        return request()->validate([
            'text' => ['required', 'string'],
            'image' => ['required', 'mimetypes:image/jpeg,image/png', 'dimensions:min_width=240,min_height=160', 'max:5000'],
        ]);
    }

    public function validateUpdateInputs(): array
    {
        return request()->validate([
            'text' => ['required', 'string'],
            'image' => ['nullable', 'mimetypes:image/jpeg,image/png', 'dimensions:min_width=240,min_height=160', 'max:5000'],
        ]);
    }
}
```

Finally, tell you brickable to use your `MyNewBrickableBrick` model and your `MyNewBrickableBricksController` controller:

```php
<?php

namespace App\Vendor\LaravelBrickables\Brickables;

use App\MyNewBrickableBrick;
use App\Http\Controllers\MyNewBrickableBricksController;
use Okipa\LaravelBrickables\Abstracts\Brickable;

class MyNewBrickable extends Brickable
{

    // ...
    
    protected function setBrickModelClass(): string
    {
        return MyNewBrickableBrick::class;
    }

    protected function setBricksControllerClass(): string
    {
        return MyNewBrickableBricksController::class;
    }

    // ...
}
```

That's it, your custom model and controller will now be used by the `MyNewBrickable` brickable.

### Get Eloquent model from request

It can be useful to retrieve the Eloquent model from the request, for navigation concerns, for example.

This helper will be able to return the related model when navigating on the brickables form views (bricks creation and edition requests).

```php
// You can pass a custom request in the parameters. If none is given, the current request is used.
$model = Brickables::getModelFromRequest();
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email arthur.lorent@gmail.com instead of using the issue tracker.

## Credits

- [Arthur LORENT](https://github.com/okipa)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
