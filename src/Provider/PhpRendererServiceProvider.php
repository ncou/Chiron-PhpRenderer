<?php

namespace Chiron\Views\Provider;

use Chiron\Views\PhpRenderer;
use Chiron\Views\PhpRendererFactory;
use Chiron\Views\TemplateRendererInterface;
use Chiron\Container\Container;
use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;
use Chiron\Container\BindingInterface;

final class PhpRendererServiceProvider implements ServiceProviderInterface
{
    public function register(BindingInterface $container): void
    {
        $container->singleton(TemplateRendererInterface::class, PhpRenderer::class);
    }
}
