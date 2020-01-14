<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\oe_content_event\Repository\CountryRepository;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Replaces the address.country_repository service with our implementation.
 */
class OeContentEventServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->has('address.country_repository')) {
      $container->getDefinition('address.country_repository')
        ->setClass(CountryRepository::class)
        ->setArguments([
          new Reference('cache.data'),
          new Reference('language_manager'),
          new Reference('entity_type.manager'),
          new Reference('entity.repository'),
          new Reference('sparql_endpoint'),
        ]);
    }
  }

}
