<?php

namespace Jgzz\MediaLinkerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('jgzz_media_linker');

        $rootNode
            ->children()
                ->arrayNode('mappings')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('hostclass')->isRequired()->end()
                            ->scalarNode('linkedclass')->isRequired()->end()
                            ->scalarNode('builder')->defaultValue('sonata.builder')->end()
                            ->scalarNode('row_template')
                                ->defaultValue('JgzzMediaLinkerBundle:CRUD:linked_entity_row_sonatamedia.html.twig')
                                ->end()
                            ->scalarNode('candidateFetcher')->defaultValue('doctrine')->end()
                            // optional controller for actions among a host and a linked (fully qualified class)
                            ->scalarNode('action_extension_class')->defaultValue(null)->end()
                            // fetcherOptions: fetcher specific options (eg: filters for query...)
                            ->variableNode('fetcherOptions')->end()
                            // todo: candidateFetcher, builder options (media: provider, context), templates, extensions (what for??)..
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
