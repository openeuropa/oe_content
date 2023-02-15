<?php

declare(strict_types = 1);

namespace Drupal\oe_countries_test;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\oe_countries_test\Repository\CountryRepository;

/**
 * Replaces the address.country_repository service with our implementation.
 */
class OeCountriesTestServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->has('address.country_repository')) {
      $container->getDefinition('address.country_repository')
        ->setClass(CountryRepository::class);
    }
  }

}
