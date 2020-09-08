<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\Plugin\Linkit\Matcher;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\linkit\Plugin\Linkit\Matcher\NodeMatcher;
use Drupal\linkit\SubstitutionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides overridden linkit matchers for the node entity type.
 */
class NodePurlMatcher extends NodeMatcher {

  use PurlMatcherTrait;

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.ExcessiveParameterList)
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, EntityRepositoryInterface $entity_repository, ModuleHandlerInterface $module_handler, AccountInterface $current_user, SubstitutionManagerInterface $substitution_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $database, $entity_type_manager, $entity_type_bundle_info, $entity_repository, $module_handler, $current_user, $substitution_manager);
    $this->config = $config_factory->get('oe_content_persistent.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity.repository'),
      $container->get('module_handler'),
      $container->get('current_user'),
      $container->get('plugin.manager.linkit.substitution'),
      $container->get('config.factory')
    );
  }

}
