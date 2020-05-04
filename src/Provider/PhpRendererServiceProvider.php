<?php

namespace Chiron\Views\Provider;

use Chiron\Views\PhpRenderer;
use Chiron\Views\PhpRendererFactory;
use Chiron\Views\TemplateRendererInterface;
use Chiron\Container\Container;
use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;
use Chiron\Container\BindingInterface;

class PhpRendererServiceProvider implements ServiceProviderInterface
{
    public function register(BindingInterface $container): void
    {
        $container->share(TemplateRendererInterface::class, PhpRenderer::class);



        // factory
        //$container->add(PhpRenderer::class, new PhpRendererFactory());
        // alias
        //$container->alias(TemplateRendererInterface::class, PhpRenderer::class);
    }
}
