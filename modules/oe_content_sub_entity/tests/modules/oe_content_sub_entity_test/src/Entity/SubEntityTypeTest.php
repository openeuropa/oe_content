<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity_test\Entity;

use Drupal\oe_content_sub_entity\Entity\SubEntityTypeBase;

/**
 * Defines the sub entity type test entity.
 *
 * @ConfigEntityType(
 *   id = "oe_sub_entity_type_test",
 *   label = @Translation("Sub entity type test"),
 *   bundle_of = "oe_sub_entity_test",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_prefix = "oe_sub_entity_type_test",
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *   },
 *   handlers = {
 *     "access" = "Drupal\oe_content_entity\CorporateEntityTypeAccessControlHandler",
 *     "list_builder" = "Drupal\oe_content\Entity\EntityTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\oe_content\Form\EntityTypeForm",
 *       "add" = "Drupal\oe_content\Form\EntityTypeForm",
 *       "edit" = "Drupal\oe_content\Form\EntityTypeForm",
 *       "delete" = "Drupal\oe_content\Form\EntityTypeDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer sub entity types",
 *   links = {
 *     "add-form" = "/admin/structure/oe_sub_entity_type_test/add",
 *     "edit-form" = "/admin/structure/oe_sub_entity_type_test/{oe_sub_entity_type_test}/edit",
 *     "delete-form" = "/admin/structure/oe_sub_entity_type_test/{oe_sub_entity_type_test}/delete",
 *     "collection" = "/admin/structure/oe_sub_entity_type_test",
 *   }
 * )
 */
class SubEntityTypeTest extends SubEntityTypeBase {}
