<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\Plugin\LinkitMatcherPlugin;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\linkit\Plugin\Linkit\Matcher\NodeMatcher;
use Drupal\linkit\SubstitutionManagerInterface;
use Drupal\linkit\Suggestion\EntitySuggestion;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides overriden linkit matchers for the node entity type.
 */
class NodePurlMatcher extends NodeMatcher {

  /**
   * The config of PURL.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.ExcessiveParameterList)
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, EntityRepositoryInterface $entity_repository, ModuleHandlerInterface $module_handler, AccountInterface $current_user, SubstitutionManagerInterface $substitution_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $database, $entity_type_manager, $entity_type_bundle_info, $entity_repository, $module_handler, $current_user, $substitution_manager, $config_factory);

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

  /**
   * {@inheritdoc}
   */
  protected function buildPath(EntityInterface $entity) {
    return $this->config->get('base_url') . $entity->uuid();
  }

  /**
   * Creates a suggestion.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The matched entity.
   *
   * @return \Drupal\linkit\Suggestion\EntitySuggestion
   *   A suggestion object with populated entity data.
   */
  protected function createSuggestion(EntityInterface $entity) {
    $suggestion = new EntitySuggestion();
    $suggestion->setLabel($this->buildLabel($entity))
      ->setGroup($this->buildGroup($entity))
      ->setDescription($this->buildDescription($entity))
      ->setPath($this->buildPath($entity));
    return $suggestion;
  }

}
