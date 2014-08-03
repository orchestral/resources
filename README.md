Resources Component for Orchestra Platform 2
==============

Resources Component is an adhoc routing manager that allow extension developer to add CRUD interface without touching Orchestra Platform 2. The idea is to allow controllers to be map to specific URL in Orchestra Platform Administrator Interface.

[![Latest Stable Version](https://poser.pugx.org/orchestra/resources/v/stable.png)](https://packagist.org/packages/orchestra/resources)
[![Total Downloads](https://poser.pugx.org/orchestra/resources/downloads.png)](https://packagist.org/packages/orchestra/resources)
[![Build Status](https://travis-ci.org/orchestral/resources.svg?branch=2.1)](https://travis-ci.org/orchestral/resources)
[![Coverage Status](https://coveralls.io/repos/orchestral/resources/badge.png?branch=2.1)](https://coveralls.io/r/orchestral/resources?branch=2.1)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/orchestral/resources/badges/quality-score.png?s=2.1)](https://scrutinizer-ci.com/g/orchestral/resources/)

## Table of Content

* [Version Compatibility](#version-compatibility)
* [Installation](#installation)
* [Configuration](#configuration)
* [Usage](#usage)
  - [Adding a Resource](#adding-a-resource)
  - [Adding a Child Resource](#adding-a-child-resource)
  - [Returning Response from a Resource](#returning-response-from-a-resource)
* [Change Log](http://orchestraplatform.com/docs/latest/components/resources/changes#v2-1)

## Version Compatibility

Laravel    | Resources
:----------|:----------
 4.0.x     | 2.0.x
 4.1.x     | 2.1.x

## Installation

To install through composer, simply put the following in your `composer.json` file:

```json
{
    "require": {
        "orchestra/resources": "2.1.*"
    }
}
```

And then run `composer install` from the terminal.

### Quick Installation

Above installation can also be simplify by using the following command:

    composer require "orchestra/resources=2.1.*"

## Configuration

Add `Orchestra\Resources\ResourcesServiceProvider` service provider in `app/config/app.php`.


```php
'providers' => array(

    // ...

    'Orchestra\Resources\ResourcesServiceProvider',
),
```

### Aliases

You might want to add `Orchestra\Support\Facades\Resources` to class aliases in `app/config/app.php`:

```php
'aliases' => array(

    // ...

    'Orchestra\Resources' => 'Orchestra\Support\Facades\Resources',
),
```

## Usage

### Adding a Resource

Normally we would identify an extension to a resource for ease of use, however Orchestra Platform still allow a single extension to register multiple resources if such requirement is needed.

```php

use Orchestra\Support\Facades\App;

Event::listen('orchestra.started: admin', function () {
    $robots = Orchestra\Resources::make('robotix', [
        'name'    => 'Robots.txt',
        'uses'    => 'Robotix\ApiController',
        'visible' => function () {
            return (App::acl()->can('manage orchestra'));
        },
    ]);
});
```

Name     | Usage
:--------|:-------------------------------------------------------
name     | A name or title to refer to the resource.
uses     | a path to controller, you can prefix with either `restful:` (default) or `resource:` to indicate how Orchestra Platform should handle the controller.
visible  | Choose whether to include the resource to Orchestra Platform Administrator Interface menu.

Orchestra Platform Administrator Interface now would display a new tab next to Extension, and you can now navigate to available resources.

### Adding a Child Resource

A single resource might require multiple actions (or controllers), we allow such feature to be used by assigning child resources.

```php
$robots->route('pages', 'resource:Robotix\PagesController');
```

Nested resource controller is also supported:

```php
$robots['pages.comments'] = 'resource:Robotix\Pages\CommentController';
```

### Returning Response from a Resource

Controllers mapped as Orchestra Platform Resources is no different from any other controller except the layout is using Orchestra Platform Administrator Interface. You can use `View`, `Response` and `Redirect` normally as you would without Orchestra Platform integration.

## Resources

* [Documentation](http://orchestraplatform.com/docs/latest/components/resources)
