# The Router Package [![Build Status](https://travis-ci.org/joomla-framework/router.png?branch=master)](https://travis-ci.org/joomla-framework/router)

### Construction


```php
use Joomla\Router\Router;

// Create a default web request router.
$router = new Router;
```

### Adding maps

The purpose of a router is to find a controller based on a routing path. The path could be a URL for a web site, or it could be an end-point for a RESTful web-services API.

The `addMap` method is used to map at routing pattern to a controller.

```php
$router = new Router;
$router
    ->addMap('/article/:article_id', 'Acme\\ArticleController`) // Route to Controller
	->addMap('/component/*', function() { return true; }) // Route to Closure
	->addMap('/user/:id, 'get_user'); // Route to function / callable

function get_user($id)
{
    // do stuff
}
```
By default, named variables in the defined route have a matching regex of `([^/]*)`, which matches everything in a route, up to the next `/`. You can optionally define the exact regex to use, by passing a third parameter to the `addMaps` function. This is an associative array using the named variables as the keys, and the desired regex as the values.

```php
$router = new Router;
$router->addMap(
    '/user/:id',
    'UserController@show',
    array(
        'id' => '(\d+)'
    )
);
```
If you were to take the above code, and match it against a path of `/user/123`, you would receive the following array back.

```php
$match = $router->parseRoute('/user/123');

print_r($match);
// Array
// (
//     [controller] => UserController@show
//     [vars] => Array
//         (
//             [name] => user
//         )
// )
```
At this point, you can do whatever you want with the returned data. It's up to you to instantiate your controller, or just use `call_user_func_array($matched['controller'], $matched['vars'])` in your FrontController if you're using the Router as the backbone for a micro framework. It's completely flexible.

## Installation via Composer

Add `"joomla/router": "2.0.*@dev"` to the require block in your composer.json and then run `composer install`.

```json
{
	"require": {
		"joomla/router": "2.0.*@dev"
	}
}
```

Alternatively, you can simply run the following from the command line:

```sh
composer require joomla/router "2.0.*@dev"
```
