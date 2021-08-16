<?php

declare(strict_types = 1);

namespace Drupal\oe_content\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\oe_content_sub_entity\Entity\SubEntityBase;

/**
 * Defines the Authors entity.
 *
 * @ingroup oe_content
 *
 * @ContentEntityType(
 *   id = "oe_authors",
 *   label = @Translation("Authors"),
 *   label_collection = @Translation("Authors"),
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
 *   base_table = "oe_authors",
 *   data_table = "oe_authors_field_data",
 *   revision_table = "oe_authors_revision",
 *   revision_data_table = "oe_authors_field_revision",
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
 *   bundle_entity_type = "oe_authors_type",
 *   field_ui_base_route = "entity.oe_authors_type.edit_form",
 *   content_translation_ui_skip = TRUE,
 * )
 */
class Authors extends SubEntityBase implements AuthorsInterface {

  /**
   * Gets the label of the entity.
   *
   * Since document reference doesn't have name field, label is generated.
   * Pattern: Referenced entity 1 label, Referenced entity 2 label.
   */
  public function label() {
    $labels = $this->getReferencedEntityLabels();
    if (!empty($labels)) {
      return $labels;
    }

    return parent::label();
  }

  /**
   * Gets labels of referenced entities.
   *
   * @return string
   *   Labels separated by comma.
   */
  protected function getReferencedEntityLabels(): string {
    // Load referenced entities.
    $entities = $this->referencedEntities();

    $labels = [];
    foreach ($entities as $entity) {
      if ($entity instanceof ContentEntityInterface && $entity->getEntityType()->hasKey('label')) {
        $labels[] = $entity->label();
      }
    }

    return implode(', ', $labels);
  }

}
