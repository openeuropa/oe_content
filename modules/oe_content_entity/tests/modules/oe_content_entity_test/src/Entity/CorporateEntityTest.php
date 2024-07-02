<?php

declare(strict_types=1);

namespace Drupal\oe_content_entity_test\Entity;

use Drupal\oe_content_entity\Entity\CorporateEntityBase;

/**
 * Defines the Corporate entity.
 *
 * @ingroup oe_content_entity_test
 *
 * @ContentEntityType(
 *   id = "oe_corporate_entity_test",
 *   label = @Translation("Corporate Entity Test"),
 *   label_collection = @Translation("Corporate Entities Test"),
 *   bundle_label = @Translation("Corporate Type Entity Test"),
 *   handlers = {
 *     "list_builder" = "Drupal\oe_content_entity\CorporateEntityListBuilder",
 *     "access" = "Drupal\oe_content_entity\CorporateEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\oe_content_entity\Routing\CorporateEntityRouteProvider",
 *     },
 *   },
 *   base_table = "oe_corporate_entity_test",
 *   data_table = "oe_corporate_entity_test_field_data",
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
 *   bundle_entity_type = "oe_corporate_type_entity_test",
 *   links = {
 *     "canonical" = "/admin/content/oe_corporate_entity_test/{oe_corporate_entity_test}/edit",
 *     "add-page" = "/admin/content/oe_corporate_entity_test/add",
 *     "add-form" = "/admin/content/oe_corporate_entity_test/add/{oe_corporate_entity_test}",
 *     "edit-form" = "/admin/content/oe_corporate_entity_test/{oe_corporate_entity_test}/edit",
 *     "delete-form" = "/admin/content/oe_corporate_entity_test/{oe_corporate_entity_test}/delete",
 *     "collection" = "/admin/content/oe_corporate_entity_test",
 *   },
 *  type = {"entity"}
 * )
 */
class CorporateEntityTest extends CorporateEntityBase {}
