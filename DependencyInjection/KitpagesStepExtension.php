<?php

/*
 * This file is part of the Kitpages CMS Project
 *
 * (c) Philippe Le Van (@plv)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kitpages\StepBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class KitpagesStepExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $this->remapParameters($config, $container, array(
            'shared_step_list'  => 'kitpages_step.shared_step_list'
        ));

    }

    public function getAlias()
    {
        return "kitpages_step";
    }

    /**
     * 
     * @param array            $config
     * @param ContainerBuilder $container
     * @param array            $map
     * @return void
     */
    protected function remapParameters(array $config, ContainerBuilder $container, array $map)
    {
        foreach ($map as $name => $paramName) {
            if (isset($config[$name])) {
                $container->setParameter($paramName, $config[$name]);
            }
        }
    }
}