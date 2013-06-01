Orchestra Platform Resources Component
==============

Orchestra\Resources is an adhoc routing manager that allow extension developer to add CRUD interface without touching Orchestra Platform.

[![Build Status](https://travis-ci.org/orchestral/resources.png?branch=master)](https://travis-ci.org/orchestral/resources)

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

## Resources

* [Documentation](http://orchestraplatform.com/docs/2.0/components/resources)
* [Change Logs](https://github.com/orchestral/resources/wiki/Change-Logs)
