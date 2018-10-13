<?php

namespace Chiron\Views\Provider;

use Chiron\Views\PhpRenderer;
use Chiron\Views\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Chiron\Views\FileViewFinder;

class PhpRendererServiceProvider
{
    /**
     * You should have in your container the config informations using the following structure :
     *
     * 'templates' => [
     *     'extension' => 'file extension used by templates; defaults to html',
     *     'paths' => [
     *         // namespace / path pairs
     *         //
     *         // Numeric namespaces imply the default/main namespace. Paths may be
     *         // strings or arrays of string paths to associate with the namespace.
     *     ],
     * ],
    */
    public function register(ContainerInterface $container)
    {
        // config
        $container['templates'] = [
            'extension' => 'html',
            'paths'     => [],
        ];
        // factory
        $container[PhpRenderer::class] = function ($c) {
            $config = $c->get('templates');

            $renderer = new PhpRenderer([], (array) $config['extension']);

            // Add template paths
            $allPaths = isset($config['paths']) && is_array($config['paths']) ? $config['paths'] : [];
            foreach ($allPaths as $namespace => $paths) {
                $namespace = is_numeric($namespace) ? null : $namespace;
                foreach ((array) $paths as $path) {
                    $renderer->addPath($path, $namespace);
                }
            }

            return $renderer;
        };
        // alias
        $container[TemplateRendererInterface::class] = function ($c) {
            return $c->get(PhpRenderer::class);
        };
    }
}
