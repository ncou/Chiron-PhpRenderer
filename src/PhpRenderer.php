<?php

declare(strict_types=1);

namespace Chiron\Views;

use Chiron\Views\AttributesTrait;
use Chiron\Views\TemplateRendererInterface;
use Chiron\Views\TemplatePath;

class PhpRenderer implements TemplateRendererInterface
{
    use AttributesTrait;

    private $engine;
    private $finder;

    public function __construct()
    {
        $this->engine = new PhpEngine();
        $this->finder = new FileViewFinder();
    }

    /**
     * Render a template, optionally with parameters.
     *
     * Implementations MUST support the `namespace::template` naming convention,
     * and allow omitting the filename extension.
     *
     * @param string $name
     * @param array $params
     */
    public function render(string $name, array $params = []) : string
    {
        $path = $this->finder->find($name);
        $params = array_merge($this->attributes, $params);

        return $this->engine->render($path, $params);
    }
    /**
     * Add a template path to the engine.
     *
     * Adds a template path, with optional namespace the templates in that path
     * provide.
     */
    public function addPath(string $path, string $namespace = null) : void
    {
        if (! $namespace) {
            $this->finder->addLocation($path);
            return;
        }
        $this->finder->addNamespace($namespace, $path);
    }

    /**
     * Get the template directories
     *
     * @return TemplatePath[]
     */
    public function getPaths() : array
    {
        $templatePaths = [];

        $paths = $this->finder->getPaths();
        $hints = $this->finder->getHints();

        foreach ($paths as $path) {
            $templatePaths[] = new TemplatePath($path);
        }
        foreach ($hints as $namespace => $path) {
            $templatePaths[] = new TemplatePath($path[0], $namespace);
        }

        return $templatePaths;
    }


    /**
     * Checks if the view exists
     *
     * @param   string  $path  Full path or part of a path
     *
     * @return  boolean  True if the path exists
     */
    public function exists(string $name): bool
    {
        return $this->finder->exists($name);
    }

    /**
     * Wrapping method to redirect methods not available in this class to the
     * internal instance of the Finder class used for the rendering engine.
     * @param string $name Unknown method to call in the internal Twig rendering engine.
     * @param array $arguments Method's arguments.
     * @return mixed Result of the called method.
     */
    /*
    public function __call($name, $arguments)
    {
        call_user_func_array(array($this->finder, $name), $arguments);
    }*/

    /**
     * Wrapping method to redirect static methods not available in this class
     * to the internal instance of the Twig rendering engine.
     * @param string $name Unknown static method to call in the internal Twig rendering engine.
     * @param array $arguments Method's arguments.
     * @return mixed Result of the called static method.
     */
    /*
    public static function __callStatic($name, $arguments)
    {
        call_user_func_array(array('\\Twig_Environment', $name), $arguments);
    }*/
}
