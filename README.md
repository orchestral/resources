Orchestra Platform Resources Component
==============

Orchestra\Resources is an adhoc routing manager that allow extension developer to add CRUD interface without touching Orchestra Platform.

[![Build Status](https://travis-ci.org/orchestral/resources.png?branch=2.0)](https://travis-ci.org/orchestral/resources) 
[![Coverage Status](https://coveralls.io/repos/orchestral/resources/badge.png?branch=2.0)](https://coveralls.io/r/orchestral/resources?branch=2.0)

## Quick Installation

To install through composer, simply put the following in your `composer.json` file:

```json
{
	"require": {
		"orchestra/resources": "2.0.*"
	}
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
* [Change Log](http://orchestraplatform.com/docs/2.0/components/resources/changes#v2.0)
