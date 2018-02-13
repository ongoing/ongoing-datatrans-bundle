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
                        ->scalarNode('sign')->isRequired()->end()
                        ->scalarNode('xml_merchant_id')->defaultNull()->end()
                        ->scalarNode('xml_sign')->defaultNull()->end()
                    ->end()
                ->end() //credentials
                ->scalarNode('test_mode')->defaultTrue()->end()
                ->scalarNode('return_url')->defaultNull()->end()
                ->scalarNode('error_url')->defaultNull()->end()
                ->scalarNode('cancel_url')->defaultNull()->end()
            ->end();

        return $treeBuilder;
    }
}
