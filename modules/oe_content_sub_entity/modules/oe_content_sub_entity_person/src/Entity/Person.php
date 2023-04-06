<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity_person\Entity;

use Drupal\Core\Cache\CacheableResponseTrait;
use Drupal\oe_content_sub_entity\Entity\SubEntityBase;
use Drupal\oe_content_sub_entity_person\Event\PersonExtractLinksEvent;
use Drupal\oe_content_sub_entity_person\Event\PersonEvents;

/**
 * Defines the Person entity.
 *
 * @ingroup oe_content
 *
 * @ContentEntityType(
 *   id = "oe_person",
 *   label = @Translation("Person"),
 *   label_collection = @Translation("Persons"),
 *   bundle_label = @Translation("Type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "access" = "Drupal\oe_content_sub_entity\SubEntityAccessControlHandler",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *   },
 *   base_table = "oe_person",
 *   data_table = "oe_person_field_data",
 *   revision_table = "oe_person_revision",
 *   revision_data_table = "oe_person_field_revision",
 *   translatable = TRUE,
 *   entity_revision_parent_type_field = "parent_type",
 *   entity_revision_parent_id_field = "parent_id",
 *   entity_revision_parent_field_name_field = "parent_field_name",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   bundle_entity_type = "oe_person_type",
 *   field_ui_base_route = "entity.oe_person_type.edit_form",
 *   content_translation_ui_skip = TRUE,
 * )
 */
class Person extends SubEntityBase implements PersonInterface {

  use CacheableResponseTrait;

  /**
   * {@inheritdoc}
   */
  public function getEntitiesAsLinks(): array {
    $event = new PersonExtractLinksEvent($this);
    $this->eventDispatcher()->dispatch($event, PersonEvents::EXTRACT_PERSON_LINKS);
    $this->addCacheableDependency($event->getCacheableMetadata());
    return $event->getLinks();
  }

}
