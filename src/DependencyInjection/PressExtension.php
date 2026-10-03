<?php

namespace Base\Press\DependencyInjection;

use Base\Bundle\AbstractBaseExtension;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class PressExtension extends AbstractBaseExtension
{
    public function getConfiguration(array $config, ContainerBuilder $container): PressConfiguration
    {
        return new PressConfiguration();
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__, 2).'/config'));
        $loader->load('services.php');

        $configuration = new PressConfiguration();
        $config = (new Processor())->processConfiguration($configuration, $configs);

        // Flat parameters: press.download_kit, press.bio_lengths... The contacts
        // are a list of maps: press.contacts is read whole.
        $this->setConfiguration($container, $config, $configuration->getTreeBuilder()->buildTree()->getName());
    }
}
