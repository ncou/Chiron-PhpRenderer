<?php

namespace Chiron\Views\Provider;

use Psr\Container\ContainerInterface;
use Chiron\Views\TemplateRendererInterface;
use Chiron\Views\PhpRenderer;

class PhpRendererServiceProvider
{
    public function register(ContainerInterface $container)
    {
        // factory
        $container[PhpRenderer::class] = function ($c) {
            return new PhpRenderer();
        };
        // alias
        $container[TemplateRendererInterface::class] = function ($c) {
            return $c->get(PhpRenderer::class);
        };
    }
}
