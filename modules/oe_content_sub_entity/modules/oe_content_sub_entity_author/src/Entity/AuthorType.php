<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity_author\Entity;

use Drupal\oe_content_sub_entity\Entity\SubEntityTypeBase;

/**
 * Defines the Author type entity.
 *
 * @ConfigEntityType(
 *   id = "oe_author_type",
 *   label = @Translation("Author type"),
 *   bundle_of = "oe_author",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_prefix = "oe_author_type",
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
 *     "add-form" = "/admin/structure/oe_author_type/add",
 *     "edit-form" = "/admin/structure/oe_author_type/{oe_author_type}/edit",
 *     "delete-form" = "/admin/structure/oe_author_type/{oe_author_type}/delete",
 *     "collection" = "/admin/structure/oe_author_type",
 *   }
 * )
 */
class AuthorType extends SubEntityTypeBase {}
