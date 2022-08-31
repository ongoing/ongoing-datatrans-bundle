<?php

namespace Ongoing\DatatransBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ongoing_datatrans')
            ->children()
                ->arrayNode('credentials')
                    ->children()
                        ->scalarNode('merchant_id')->isRequired()->end()
                        ->scalarNode('password')->isRequired()->end()
                        ->scalarNode('sign')->isRequired()->end()
                        ->scalarNode('xml_merchant_id')->defaultNull()->end()
                        ->scalarNode('xml_password')->defaultNull()->end()
                        ->scalarNode('xml_sign')->defaultNull()->end()
                    ->end()
                ->end() //credentials
                ->scalarNode('test_mode')->defaultTrue()->end()
                ->arrayNode('transaction_parameter')
                    ->defaultValue([])
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
