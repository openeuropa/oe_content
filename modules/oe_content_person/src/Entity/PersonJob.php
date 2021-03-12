<?php

declare(strict_types = 1);

namespace Drupal\oe_content_person\Entity;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_content_sub_entity\Entity\SubEntityBase;

/**
 * Defines the Person job entity.
 *
 * @ingroup oe_content_person
 *
 * @ContentEntityType(
 *   id = "oe_person_job",
 *   label = @Translation("Person job"),
 *   label_collection = @Translation("Person jobs"),
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
 *   base_table = "oe_person_job",
 *   data_table = "oe_person_job_field_data",
 *   revision_table = "oe_person_job_revision",
 *   revision_data_table = "oe_person_job_field_revision",
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
 *   bundle_entity_type = "oe_person_job_type",
 *   field_ui_base_route = "entity.oe_person_job_type.edit_form",
 *   content_translation_ui_skip = TRUE,
 * )
 */
class PersonJob extends SubEntityBase implements PersonJobInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function label() {
    if ($this->bundle() === 'oe_default') {
      // Define label for Default Person job.
      $label = $this->get('oe_role_name')->value;
      if (!$this->get('oe_role_reference')->isEmpty()) {
        $label = $this->get('oe_role_reference')->entity->label();

        if ($this->get('oe_acting')->value) {
          $label = $this->t('(Acting) @role', ['@role' => $label]);
        }
      }

      if (!empty($label)) {
        return $label;
      }
    }

    return parent::label();
  }

}
