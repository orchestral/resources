Orchestra Platform Resources Component
==============

`Orchestra\Resources` is an adhoc routing manager that allow extension developer to add CRUD interface without touching Orchestra Platform.

[![Latest Stable Version](https://poser.pugx.org/orchestra/resources/v/stable.png)](https://packagist.org/packages/orchestra/resources) 
[![Total Downloads](https://poser.pugx.org/orchestra/resources/downloads.png)](https://packagist.org/packages/orchestra/resources) 
[![Build Status](https://travis-ci.org/orchestral/resources.png?branch=master)](https://travis-ci.org/orchestral/resources) 
[![Coverage Status](https://coveralls.io/repos/orchestral/resources/badge.png?branch=master)](https://coveralls.io/r/orchestral/resources?branch=master) 
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/orchestral/resources/badges/quality-score.png?s=8cbf94cc9944b7c3b039fe635676c4e574be5906)](https://scrutinizer-ci.com/g/orchestral/resources/) 

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
* [Change Log](http://orchestraplatform.com/docs/2.0/components/resources/changes#v2.1)
