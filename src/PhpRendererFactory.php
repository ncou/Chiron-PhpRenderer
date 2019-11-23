<?php

declare(strict_types=1);

namespace Chiron\Views;

use Chiron\Container\Container;
use Chiron\Boot\DirectoriesInterface;

class PhpRendererFactory
{
    /**
     * You should have in your container the config informations using the following structure :.
     *
     * 'templates' => [
     *     'extension' => 'file extension used by templates; defaults to phtml',
     *     'paths' => [
     *         // namespace / path pairs
     *         //
     *         // Numeric namespaces imply the default/main namespace. Paths may be
     *         // strings or arrays of string paths to associate with the namespace.
     *     ],
     * ],
     */
    public function __invoke(Container $container)
    {
        // directories
        $dirs = $container->get(DirectoriesInterface::class);
        $config = $container->get(\Chiron\Config\ConfigManager::class);

        // config
        if (! $config->has('templates')) {
            $config->merge(['templates' => [
                'extension' => 'phtml',
                'paths'     => [$dirs->get('templates')],
            ]]);
        }

        $templates = $config->get('templates');

        //die(var_dump($templates));

        $renderer = new PhpRenderer([], (array) $templates['extension']);

        // Add template paths
        $allPaths = isset($templates['paths']) && is_array($templates['paths']) ? $templates['paths'] : [];
        foreach ($allPaths as $namespace => $paths) {
            $namespace = is_numeric($namespace) ? null : $namespace;
            foreach ((array) $paths as $path) {
                $renderer->addPath($path, $namespace);
            }
        }

        return $renderer;
    }
}
