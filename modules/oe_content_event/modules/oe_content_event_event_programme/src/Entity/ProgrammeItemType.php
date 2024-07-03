<?php

declare(strict_types=1);

namespace Drupal\oe_content_event_event_programme\Entity;

use Drupal\oe_content_entity\Entity\CorporateEntityTypeBase;

/**
 * Defines the event programme type entity.
 *
 * @ConfigEntityType(
 *   id = "oe_event_programme_type",
 *   label = @Translation("Programme Item type"),
 *   bundle_of = "oe_event_programme",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_prefix = "oe_event_programme_type",
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
 *   admin_permission = "manage corporate content entity types",
 *   links = {
 *     "add-form" = "/admin/structure/oe_event_programme_type/add",
 *     "edit-form" = "/admin/structure/oe_event_programme_type/{oe_event_programme_type}/edit",
 *     "delete-form" = "/admin/structure/oe_event_programme_type/{oe_event_programme_type}/delete",
 *     "collection" = "/admin/structure/oe_event_programme_type",
 *   }
 * )
 */
class ProgrammeItemType extends CorporateEntityTypeBase {}
