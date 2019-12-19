<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity_venue\Entity;

use Drupal\oe_content_entity\Entity\EntityTypeBase;

/**
 * Defines the venue type entity.
 *
 * @ConfigEntityType(
 *   id = "oe_venue_type",
 *   label = @Translation("Venue type"),
 *   bundle_of = "oe_venue",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_prefix = "oe_venue_type",
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
 *     "add-form" = "/admin/structure/custom-entity-types/oe_venue_type/add",
 *     "edit-form" = "/admin/structure/custom-entity-types/oe_venue_type/{oe_venue_type}/edit",
 *     "delete-form" = "/admin/structure/custom-entity-types/oe_venue_type/{oe_venue_type}/delete",
 *     "collection" = "/admin/structure/custom-entity-types/oe_venue_type",
 *   }
 * )
 */
class VenueType extends EntityTypeBase {}
