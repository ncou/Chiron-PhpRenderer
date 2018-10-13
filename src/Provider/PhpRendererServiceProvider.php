<?php

namespace Chiron\Views\Provider;

use Chiron\Views\PhpRenderer;
use Chiron\Views\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

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
