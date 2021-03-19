<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity_test\Entity;

use Drupal\oe_content_sub_entity\Entity\SubEntityBase;

/**
 * Defines Sub entity test.
 *
 * @ingroup oe_content_sub_entity
 *
 * @ContentEntityType(
 *   id = "oe_sub_entity_test",
 *   label = @Translation("Sub entity test"),
 *   label_collection = @Translation("Sub entities test"),
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
 *   base_table = "oe_sub_entity_test",
 *   data_table = "oe_sub_entity_test_field_data",
 *   revision_table = "oe_sub_entity_test_revision",
 *   revision_data_table = "oe_sub_entity_test_field_revision",
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
 *   bundle_entity_type = "oe_sub_entity_type_test",
 *   field_ui_base_route = "entity.oe_sub_entity_type_test.edit_form",
 *   content_translation_ui_skip = TRUE,
 * )
 */
class SubEntityTest extends SubEntityBase {}
