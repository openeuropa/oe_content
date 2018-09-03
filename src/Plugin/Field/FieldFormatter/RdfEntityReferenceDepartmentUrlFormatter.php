<?php

declare(strict_types = 1);

namespace Drupal\oe_content\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'rdf_entity_reference_department_url' formatter.
 *
 * @FieldFormatter(
 *   id = "rdf_entity_reference_department_url",
 *   label = @Translation("RDF Entity Reference Department URL"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class RdfEntityReferenceDepartmentUrlFormatter extends RdfEntityReferenceLabelFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a RdfEntityReferenceDepartmentUrlFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = [
      '#markup' => $this->t('Linked label to the Department RDF entity canonical URL if one exists.'),
    ];

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesToView(EntityReferenceFieldItemListInterface $items, $langcode): array {
    $entities = parent::getEntitiesToView($items, $langcode);

    // Only allow RDF taxonomy entities.
    $entities = array_filter($entities, function (EntityInterface $entity) {
      return $entity instanceof TermInterface;
    });

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  protected function getUrlForEntity(EntityInterface $entity): Url {
    $uris = $this->entityTypeManager->getStorage('rdf_entity')->getQuery()
      ->condition('rid', 'oe_department')
      ->condition('oe_department_name', $entity->id())
      ->execute();

    if (!$uris) {
      return $entity->toUrl()->setAbsolute(TRUE);
    }

    // Normally there should only be one Department instance.
    $uri = reset($uris);
    return Url::fromUri($uri);
  }

}
