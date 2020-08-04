<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\Plugin\Linkit\Matcher;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\linkit\Plugin\Linkit\Matcher\EntityMatcher;
use Drupal\linkit\SubstitutionManagerInterface;
use Drupal\linkit\Suggestion\EntitySuggestion;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract class to create PURL linkit matchers for entity types.
 */
abstract class PurlEntityMatcherBase extends EntityMatcher {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, EntityRepositoryInterface $entity_repository, ModuleHandlerInterface $module_handler, AccountInterface $current_user, SubstitutionManagerInterface $substitution_manager, ConfigFactoryInterface $config_factory, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $database, $entity_type_manager, $entity_type_bundle_info, $entity_repository, $module_handler, $current_user, $substitution_manager, $config_factory);
    $this->entityFieldManager = $entity_field_manager;
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
      $container->get('config.factory'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $summery = parent::getSummary();

    if ($this->entityHasStatusField()) {
      $summery[] = $this->t('Include unpublished: @include_unpublished', [
        '@include_unpublished' => $this->configuration['include_unpublished'] ? $this->t('Yes') : $this->t('No'),
      ]);
    }

    return $summery;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'include_unpublished' => FALSE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    if ($this->entityHasStatusField()) {
      $entity_type = $this->getPluginDefinition()['target_entity'];
      $entity_type_definition = $this->entityTypeManager->getDefinition($entity_type);
      $form['unpublished'] = [
        '#type' => 'details',
        '#title' => $this->t('Unpublished @entity_type', ['@entity_type' => $entity_type_definition->getCollectionLabel()]),
        '#open' => TRUE,
      ];

      $form['unpublished']['include_unpublished'] = [
        '#title' => $this->t('Include unpublished @entity_type', ['@entity_type' => $entity_type_definition->getCollectionLabel()]),
        '#type' => 'checkbox',
        '#default_value' => $this->configuration['include_unpublished'],
        '#description' => $this->t('In order to see unpublished @entity_type, users must also have permissions to do so.', ['@entity_type' => $entity_type_definition->getCollectionLabel()]),
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    if ($this->entityHasStatusField()) {
      $this->configuration['include_unpublished'] = $form_state->getValue('include_unpublished');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($search_string) {
    $query = parent::buildEntityQuery($search_string);
    $this->applyPublishedCondition($query);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildPath(EntityInterface $entity) {
    return $this->config->get('base_url') . $entity->uuid();
  }

  /**
   * Applies the published condition to the entity query.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The query we want to alter.
   */
  protected function applyPublishedCondition(QueryInterface &$query) {
    if ($this->configuration['include_unpublished'] == FALSE) {
      $query->condition('status', 1);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function createSuggestion(EntityInterface $entity) {
    $suggestion = new EntitySuggestion();
    $suggestion->setLabel($this->buildLabel($entity))
      ->setGroup($this->buildGroup($entity))
      ->setDescription($this->buildDescription($entity))
      ->setPath($this->buildPath($entity));
    return $suggestion;
  }

  /**
   * Asserts whether the entity associated with the plugin has a status field.
   *
   * @return bool
   *   TRUE if the entity has a status field or FALSE otherwise.
   */
  protected function entityHasStatusField() {
    $entity_type = $this->getPluginDefinition()['target_entity'];
    $base_fields = $this->entityFieldManager->getBaseFieldDefinitions($entity_type);
    return array_key_exists('status', $base_fields);
  }

}
