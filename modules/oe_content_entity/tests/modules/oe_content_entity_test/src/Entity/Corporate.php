<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity_test\Entity;

use Drupal\oe_content_entity\Entity\EntityBase;

/**
 * Defines the Corporate entity.
 *
 * @ingroup oe_content_entity_test
 *
 * @ContentEntityType(
 *   id = "oe_corporate",
 *   label = @Translation("Corporate"),
 *   label_collection = @Translation("Corporates"),
 *   bundle_label = @Translation("Corporate type"),
 *   handlers = {
 *     "access" = "Drupal\oe_content_entity\EntityAccessControlHandler",
 *   },
 *   base_table = "oe_corporate",
 *   data_table = "oe_corporate_field_data",
 *   revision_table = "oe_corporate_revision",
 *   revision_data_table = "oe_test_field_revision",
 *   translatable = TRUE,
 *   show_revision_ui = TRUE,
 *   admin_permission = "manage corporate content entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "bundle" = "bundle",
 *     "uuid" = "uuid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *     "created" = "created",
 *     "changed" = "changed",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log"
 *   },
 *   bundle_entity_type = "oe_corporate_type",
 *   field_ui_base_route = "entity.oe_corporate_type.edit_form",
 *  type = {"entity"}
 * )
 */
class Corporate extends EntityBase {}
