<?php

namespace Anaiel\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

class AnaielExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param array $config An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $container->register('anaiel.book_repository', 'Anaiel\Repository\BookRepository')
            ->addArgument(new Reference('doctrine.dbal.database_connection'))
            ->addArgument(new Reference('anaiel.page_repository'));

        $container->register('anaiel.page_repository', 'Anaiel\Repository\PageRepository')
            ->addArgument(new Reference('doctrine.dbal.database_connection'))
            ->addArgument(new Reference('anaiel.section_repository'));

        $container->register('anaiel.section_repository', 'Anaiel\Repository\SectionRepository')
            ->addArgument(new Reference('doctrine.dbal.database_connection'));
    }
}
