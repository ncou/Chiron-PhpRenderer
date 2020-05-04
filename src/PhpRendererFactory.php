<?php

declare(strict_types=1);

namespace Chiron\Views;

use Chiron\Container\Container;
use Chiron\Boot\DirectoriesInterface;

// TODO : classe à virer elle ne sert plus à rien
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
    public function __invoke(Container $container): TemplateRendererInterface
    {
        // directories
        $dirs = $container->get(DirectoriesInterface::class);

        // config
        $config = $container->get(\Chiron\Config\ConfigManager::class);

        if (! $config->has('templates')) {
            $config->merge(['templates' => [
                'extension' => 'phtml',
                'paths'     => [$dirs->get('templates')],
            ]]);
        }

        $templates = $config->get('templates');

        // instanciate class
        $renderer = new PhpRenderer([], (array) $templates['extension']);

        // add template paths
        //$allPaths = isset($templates['paths']) && is_array($templates['paths']) ? $templates['paths'] : [];
        $allPaths = isset($templates['paths']) ? (array) $templates['paths'] : [];
        foreach ($allPaths as $namespace => $paths) {
            $namespace = is_numeric($namespace) ? null : $namespace;
            foreach ((array) $paths as $path) {
                $renderer->addPath($path, $namespace);
            }
        }

        return $renderer;
    }
}
