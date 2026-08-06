<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Traits;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides methods to create the corporate and sub-entities used by the tests.
 */
trait CorporateEntityCreationTrait {

  /**
   * Creates a general contact entity.
   *
   * @param array $values
   *   The field values, keyed by field name.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The contact entity.
   */
  protected function createGeneralContact(array $values = []): ContentEntityInterface {
    return $this->createCorporateEntity('oe_contact', 'oe_general', $values);
  }

  /**
   * Creates a default venue entity.
   *
   * @param array $values
   *   The field values, keyed by field name.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The venue entity.
   */
  protected function createDefaultVenue(array $values = []): ContentEntityInterface {
    return $this->createCorporateEntity('oe_venue', 'oe_default', $values);
  }

  /**
   * Creates a stakeholder organisation entity.
   *
   * @param array $values
   *   The field values, keyed by field name.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The organisation entity.
   */
  protected function createStakeholderOrganisation(array $values = []): ContentEntityInterface {
    return $this->createCorporateEntity('oe_organisation', 'oe_stakeholder', $values);
  }

  /**
   * Creates a default person job sub-entity.
   *
   * @param array $values
   *   The field values, keyed by field name.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The person job entity.
   */
  protected function createDefaultPersonJob(array $values = []): ContentEntityInterface {
    return $this->createCorporateEntity('oe_person_job', 'oe_default', $values);
  }

  /**
   * Creates a document reference sub-entity.
   *
   * @param string $bundle
   *   The bundle of the document reference, either "oe_document" or
   *   "oe_publication".
   * @param array $values
   *   The field values, keyed by field name.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The document reference entity.
   */
  protected function createDocumentReference(string $bundle, array $values = []): ContentEntityInterface {
    return $this->createCorporateEntity('oe_document_reference', $bundle, $values);
  }

  /**
   * Creates and saves an entity of the given type and bundle.
   *
   * @param string $entity_type
   *   The entity type ID.
   * @param string $bundle
   *   The bundle ID.
   * @param array $values
   *   The field values, keyed by field name.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The saved entity.
   */
  protected function createCorporateEntity(string $entity_type, string $bundle, array $values = []): ContentEntityInterface {
    $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
    $bundle_key = $storage->getEntityType()->getKey('bundle');
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $storage->create($values + [$bundle_key => $bundle]);
    $entity->save();

    return $entity;
  }

  /**
   * Asserts that an entity still exists in the storage.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to look for.
   */
  protected function assertEntityExists(ContentEntityInterface $entity): void {
    $storage = \Drupal::entityTypeManager()->getStorage($entity->getEntityTypeId());
    $storage->resetCache([$entity->id()]);
    $this->assertNotNull($storage->load($entity->id()), sprintf('The %s entity with ID %s does not exist anymore.', $entity->getEntityTypeId(), $entity->id()));
  }

}
