<?php

declare(strict_types=1);

namespace Chiron\Views;

class FileViewFinder
{
    /** Identifier of the default namespace. */
    public const DEFAULT_NAMESPACE = '__DEFAULT__';

    protected $paths = [];

    protected $cache = [];

    /**
     * Namespace path delimiter value.
     *
     * @var string
     */
    public const NAMESPACE_DELIMITER = '::';

    /**
     * Register a view extension with the finder.
     *
     * @var array
     */
    protected $extensions = ['phtml', 'html', 'php'];

    /**
     * Create a new file view loader instance.
     *
     * @param array $paths
     * @param array $extensions
     */
    public function __construct(array $paths = [], array $extensions = null)
    {
        if ($paths) {
            $this->setPaths($paths);
        }
        if ($extensions) {
            $this->extensions = $extensions;
        }
    }

    /**
     * Determine if a given view exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function exists(string $name): bool
    {
        if (isset($this->cache[$name])) {
            return true;
        }

        try {
            $this->findTemplate($name);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    /**
     * Get the fully qualified location of the view.
     *
     * @param string $name
     *
     * @return string
     */
    public function findTemplate(string $name): string
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        list($namespace, $shortname) = $this->parseName($name);

        foreach ($this->paths[$namespace] as $path) {
            foreach ($this->getPossibleViewFiles($shortname) as $file) {
                if (is_file($viewPath = $path . '/' . $file)) {
                    if (false !== $realpath = realpath($viewPath)) {
                        return $this->cache[$name] = $realpath;
                    }

                    return $this->cache[$name] = $viewPath;
                }
            }
        }

        throw new \InvalidArgumentException("Template [{$name}] not found.");
    }

    /**
     * Get the path to a template with a named path.
     *
     * @param string $name
     *
     * @return array
     */
    protected function parseName(string $name, string $default = self::DEFAULT_NAMESPACE): array
    {
        if ($this->hasHintInformation($name)) {
            return $this->parseNamespaceSegments($name);
        }

        return [$default, $name];
    }

    /**
     * Get the segments of a template with a named path.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function parseNamespaceSegments(string $name): array
    {
        $segments = explode(static::NAMESPACE_DELIMITER, $name);
        if (count($segments) !== 2) {
            throw new \InvalidArgumentException("Template [{$name}] has an invalid name.");
        }
        if (! isset($this->paths[$segments[0]])) {
            throw new \InvalidArgumentException("No namespace defined for [{$segments[0]}].");
        }

        return $segments;
    }

    /**
     * Get an array of possible view files.
     *
     * @param string $name
     *
     * @return array
     */
    protected function getPossibleViewFiles(string $name): array
    {
        return array_map(function ($extension) use ($name) {
            return str_replace('.', '/', $name) . '.' . $extension;
        }, $this->extensions);
    }

    /**
     * Returns the paths to the templates.
     *
     * @param string $namespace A path namespace
     *
     * @return array The array of paths where to look for templates
     */
    public function getPaths($namespace = self::DEFAULT_NAMESPACE)
    {
        return isset($this->paths[$namespace]) ? $this->paths[$namespace] : [];
    }

    /**
     * Returns the path namespaces.
     *
     * The main namespace is always defined.
     *
     * @return array The array of defined namespaces
     */
    public function getNamespaces()
    {
        return array_keys($this->paths);
    }

    /**
     * Sets the paths where templates are stored.
     *
     * @param string|array $paths     A path or an array of paths where to look for templates
     * @param string       $namespace A path namespace
     */
    public function setPaths($paths, $namespace = self::DEFAULT_NAMESPACE)
    {
        if (! is_array($paths)) {
            $paths = [$paths];
        }
        $this->paths[$namespace] = [];
        foreach ($paths as $path) {
            $this->addPath($path, $namespace);
        }
    }

    /**
     * Adds a path where templates are stored.
     *
     * @param string $path      A path where to look for templates
     * @param string $namespace A path namespace
     *
     * @throws Twig_Error_Loader
     */
    public function addPath(string $path, string $namespace = self::DEFAULT_NAMESPACE)
    {
        // invalidate the cache
        $this->cache = [];

        $this->paths[$namespace][] = rtrim($path, '/\\');
    }

    /**
     * Register an extension with the view finder.
     *
     * @param string $extension
     */
    public function addExtension(string $extension): void
    {
        if (($index = array_search($extension, $this->extensions)) !== false) {
            unset($this->extensions[$index]);
        }
        array_unshift($this->extensions, $extension);
    }

    /**
     * Get registered extensions.
     *
     * @return array
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Returns whether or not the view name has any hint information.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasHintInformation(string $name): bool
    {
        return strpos($name, static::NAMESPACE_DELIMITER) > 0;
    }
}
