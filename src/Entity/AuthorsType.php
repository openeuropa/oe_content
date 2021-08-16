<?php

declare(strict_types = 1);

namespace Drupal\oe_content\Entity;

use Drupal\oe_content_sub_entity\Entity\SubEntityTypeBase;

/**
 * Defines the authors type entity.
 *
 * @ConfigEntityType(
 *   id = "oe_authors_type",
 *   label = @Translation("Authors type"),
 *   bundle_of = "oe_authors",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_prefix = "oe_authors_type",
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *   },
 *   handlers = {
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
 *     "add-form" = "/admin/structure/oe_authors_type/add",
 *     "edit-form" = "/admin/structure/oe_authors_type/{oe_authors_type}/edit",
 *     "delete-form" = "/admin/structure/oe_authors_type/{oe_authors_type}/delete",
 *     "collection" = "/admin/structure/oe_authors_type",
 *   }
 * )
 */
class AuthorsType extends SubEntityTypeBase {}
