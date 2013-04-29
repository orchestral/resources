Orchestra Platform Resources Component
==============

Orchestra\Resources is an adhoc routing manager that allow extension developer to add CRUD interface without touching Orchestra Platform.

[![Build Status](https://travis-ci.org/orchestral/resources.png?branch=master)](https://travis-ci.org/orchestral/resources)

## Quick Installation

## Quick Installation

To install through composer, simply put the following in your `composer.json` file:

```json
{
	"require": {
		"orchestra/resources": "2.0.*"
	},
	"minimum-stability": "dev"
}
```

Next add the following service provider in `app/config/app.php`.

```php
'providers' => array(
	
	// ...

	'Orchestra\Resources\ResourcesServiceProvider',
),
```

You might want to add following facades to class aliases in `app/config/app.php`:

```php
'aliases' => array(

	// ...

	'Orchestra\Resources' => 'Orchestra\Support\Facades\Resources',
),
```

## Resources

* [Documentation](http://docs.orchestraplatform.com/pages/components/resources)
* [Change Logs](https://github.com/orchestral/resources/wiki/Change-Logs)
