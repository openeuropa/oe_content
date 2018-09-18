<?php

declare(strict_types = 1);

namespace Drupal\oe_content\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\oe_content\Event\DepartmentReferencingEvent;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

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
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EventDispatcherInterface $eventDispatcher) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->eventDispatcher = $eventDispatcher;
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
      $container->get('event_dispatcher')
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
      return $entity instanceof TermInterface && $entity->bundle() === 'corporate_bodies';
    });

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  protected function getUrlForEntity(EntityInterface $entity): Url {
    $event = new DepartmentReferencingEvent($entity);
    $event = $this->eventDispatcher->dispatch(DepartmentReferencingEvent::EVENT, $event);
    $rdf_entity = $event->getRdfEntity();
    if (!$rdf_entity) {
      return $entity->toUrl()->setAbsolute(TRUE);
    }

    return Url::fromUri($rdf_entity->id());
  }

}
