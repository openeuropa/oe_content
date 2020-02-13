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
 *     "list_builder" = "Drupal\oe_content_entity\EntityListBuilder",
 *     "access" = "Drupal\oe_content_entity\EntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\oe_content_entity\Routing\EntityRouteProvider",
 *     },
 *   },
 *   base_table = "oe_corporate",
 *   data_table = "oe_corporate_field_data",
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
 *   bundle_entity_type = "oe_corporate_type",
 *   links = {
 *     "canonical" = "/admin/content/oe_corporate/{oe_corporate}/edit",
 *     "add-page" = "/admin/content/oe_corporate/add",
 *     "add-form" = "/admin/content/oe_corporate/add/{oe_corporate_type}",
 *     "edit-form" = "/admin/content/oe_corporate/{oe_corporate}/edit",
 *     "delete-form" = "/admin/content/oe_corporate/{oe_corporate}/delete",
 *     "collection" = "/admin/content/oe_corporate",
 *   },
 *  type = {"entity"}
 * )
 */
class Corporate extends EntityBase {}
