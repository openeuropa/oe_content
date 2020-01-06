<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity_organisation\Entity;

use Drupal\oe_content_entity\Entity\EntityTypeBase;

/**
 * Defines the organisation type entity.
 *
 * @ConfigEntityType(
 *   id = "oe_organisation_type",
 *   label = @Translation("Organisation type"),
 *   bundle_of = "oe_organisation",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_prefix = "oe_organisation_type",
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *   },
 *   handlers = {
 *     "list_builder" = "Drupal\oe_content_entity\EntityTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\oe_content_entity\Form\EntityTypeForm",
 *       "add" = "Drupal\oe_content_entity\Form\EntityTypeForm",
 *       "edit" = "Drupal\oe_content_entity\Form\EntityTypeForm",
 *       "delete" = "Drupal\oe_content_entity\Form\EntityTypeDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "manage custom content entities",
 *   links = {
 *     "add-form" = "/admin/structure/oe_organisation_type/add",
 *     "edit-form" = "/admin/structure/oe_organisation_type/{oe_organisation_type}/edit",
 *     "delete-form" = "/admin/structure/oe_organisation_type/{oe_organisation_type}/delete",
 *     "collection" = "/admin/structure/oe_organisation_type",
 *   }
 * )
 */
class OrganisationType extends EntityTypeBase {}
