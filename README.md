[![Build Status](https://travis-ci.org/geggleto/PHP-View.svg?branch=master)](https://travis-ci.org/geggleto/PHP-View)

[![Build Status](https://secure.travis-ci.org/zendframework/zend-view.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-view)
[![Coverage Status](https://coveralls.io/repos/zendframework/zend-view/badge.svg?branch=master)](https://coveralls.io/r/zendframework/zend-view?branch=master)

## PHP Renderer

This is a renderer for rendering PHP view scripts into a PSR-7 Response object. It works well with Chiron Framework.


### Cross-site scripting (XSS) risks

Note that PHP-View has no built-in mitigation from XSS attacks. It is the developer's responsibility to use `htmlspecialchars()` or a component like [zend-escaper](https://github.com/zendframework/zend-escaper). Alternatively, consider  [Twig-View](https://github.com/slimphp/Twig-View).



## Templates
You may use `$this` inside your php templates. `$this` will be the actual PhpRenderer object will allow you to render sub-templates

## Installation

Install with [Composer](http://getcomposer.org):

    composer require chiron/php-renderer


## Usage with Chiron

```php
use Chiron\Views\PhpRenderer;

include "vendor/autoload.php";

$app = new Chiron\App();
$container = $app->getContainer();
$container['renderer'] = new PhpRenderer("./templates");

$app->get('/hello/{name}', function ($request, $response, $args) use ($container) {
    $text = $container->get('renderer')->render("/hello.php", $args);
    return $response->write($text);
});

$app->run();
```

## Usage with any PSR-7 Project
```php
//Construct the View
$phpView = new PhpRenderer("./path/to/templates");

//Render a Template
$text = $phpView->render("/path/to/template.php", $yourData);
$response = $response->write($text);
```

## Template Variables

You can now add variables to your renderer that will be available to all templates you render.

```php
// via the constructor
$templateVariables = [
    "title" => "Title"
];
$phpView = new PhpRenderer("./path/to/templates", $templateVariables);

// or setter
$phpView->setAttributes($templateVariables);

// or individually
$phpView->addAttribute($key, $value);
```

Data passed in via `->render()` takes precedence over attributes.
```php
$templateVariables = [
    "title" => "Title"
];
$phpView = new PhpRenderer("./path/to/templates", $templateVariables);

//...

$phpView->render($template, [
    "title" => "My Title"
]);
// In the view above, the $title will be "My Title" and not "Title"
```

## Exceptions
`\RuntimeException` - if template does not exist
