<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder as CoreEntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of corporate content entities.
 *
 * @ingroup oe_content_entity
 */
class EntityListBuilder extends CoreEntityListBuilder {

  /**
   * Storage for the current corporate content entity type bundles.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $bundleStorage;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new EntityListBuilderBase object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   Storage for the current corporate content entity type.
   * @param \Drupal\Core\Entity\EntityStorageInterface $bundle_storage
   *   Storage for the current corporate content entity type bundles.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, EntityStorageInterface $bundle_storage, DateFormatterInterface $date_formatter, RendererInterface $renderer) {
    parent::__construct($entity_type, $storage);

    $this->bundleStorage = $bundle_storage;
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('entity_type.manager')->getStorage($entity_type->getBundleEntityType()),
      $container->get('date.formatter'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Entity');
    $header['bundle'] = $this->t('Type');
    $header['created'] = $this->t('Created');
    $header['changed'] = $this->t('Changed');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\oe_content_entity\Entity\EntityTypeBase $entity */
    $row['id'] = $entity->toLink($entity->label());
    $row['bundle'] = $this->bundleStorage->load($entity->bundle())->label();
    $row['created'] = $this->dateFormatter->format($entity->getCreatedTime(), 'short');
    $row['changed'] = $this->dateFormatter->format($entity->getChangedTime(), 'short');

    return $row + parent::buildRow($entity);
  }

}
