Resources Component for Orchestra Platform
==============

[![Join the chat at https://gitter.im/orchestral/platform/components](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/orchestral/platform/components?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

Resources Component is an optional adhoc routing manager that allow extension developer to add CRUD interface without touching Orchestra Platform 2. The idea is to allow controllers to be map to specific URL in Orchestra Platform Administrator Interface.

[![Latest Stable Version](https://img.shields.io/github/release/orchestral/resources.svg?style=flat-square)](https://packagist.org/packages/orchestra/resources)
[![Total Downloads](https://img.shields.io/packagist/dt/orchestra/resources.svg?style=flat-square)](https://packagist.org/packages/orchestra/resources)
[![MIT License](https://img.shields.io/packagist/l/orchestra/resources.svg?style=flat-square)](https://packagist.org/packages/orchestra/resources)
[![Build Status](https://img.shields.io/travis/orchestral/resources/master.svg?style=flat-square)](https://travis-ci.org/orchestral/resources)
[![Coverage Status](https://img.shields.io/coveralls/orchestral/resources/master.svg?style=flat-square)](https://coveralls.io/r/orchestral/resources?branch=master)
[![Scrutinizer Quality Score](https://img.shields.io/scrutinizer/g/orchestral/resources/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/orchestral/resources/)

## Table of Content

* [Version Compatibility](#version-compatibility)
* [Installation](#installation)
* [Configuration](#configuration)
* [Usage](#usage)
  - [Adding a Resource](#adding-a-resource)
  - [Adding a Child Resource](#adding-a-child-resource)
  - [Returning Response from a Resource](#returning-response-from-a-resource)
* [Change Log](https://github.com/orchestral/resources/releases)

## Version Compatibility

Laravel    | Resources
:----------|:----------
 4.0.x     | 2.0.x
 4.1.x     | 2.1.x
 4.2.x     | 2.2.x
 5.0.x     | 3.0.x
 5.1.x     | 3.1.x
 ~5.2      | 3.2.x

## Installation

To install through composer, simply put the following in your `composer.json` file:

```json
{
    "require": {
        "orchestra/resources": "~3.0"
    }
}
```

And then run `composer install` from the terminal.

### Quick Installation

Above installation can also be simplify by using the following command:

    composer require "orchestra/resources=~3.0"

## Configuration

Add `Orchestra\Resources\ResourcesServiceProvider` service provider in `config/app.php`.


```php
'providers' => [

    // ...

    Orchestra\Resources\ResourcesServiceProvider::class,
],
```

### Aliases

You might want to add `Orchestra\Support\Facades\Resources` to class aliases in `config/app.php`:

```php
'aliases' => [

    // ...

    'Resources' => Orchestra\Support\Facades\Resources::class,
],
```

## Usage

### Adding a Resource

Normally we would identify an extension to a resource for ease of use, however Orchestra Platform still allow a single extension to register multiple resources if such requirement is needed.

```php

use Orchestra\Support\Facades\Foundation;

Event::listen('orchestra.started: admin', function () {
    $robots = Resources::make('robotix', [
        'name'    => 'Robots.txt',
        'uses'    => 'Robotix\ApiController',
        'visible' => function () {
            return (Foundation::acl()->can('manage orchestra'));
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

