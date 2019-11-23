<?php

namespace Chiron\Views\Provider;

use Chiron\Views\PhpRenderer;
use Chiron\Views\PhpRendererFactory;
use Chiron\Views\TemplateRendererInterface;
use Chiron\Container\Container;
use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;

class PhpRendererServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        // factory
        $container->add(PhpRenderer::class, new PhpRendererFactory());
        // alias
        $container->alias(TemplateRendererInterface::class, PhpRenderer::class);
    }
}
