Resources Component for Orchestra Platform 2
==============

Resources Component is an adhoc routing manager that allow extension developer to add CRUD interface without touching Orchestra Platform 2.

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

## Resources

* [Documentation](http://orchestraplatform.com/docs/latest/components/resources)
