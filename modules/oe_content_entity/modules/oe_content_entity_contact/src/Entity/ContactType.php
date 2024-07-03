<?php

declare(strict_types=1);

namespace Drupal\oe_content_entity_contact\Entity;

use Drupal\oe_content_entity\Entity\CorporateEntityTypeBase;

/**
 * Defines the contact type entity.
 *
 * @ConfigEntityType(
 *   id = "oe_contact_type",
 *   label = @Translation("Contact type"),
 *   bundle_of = "oe_contact",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_prefix = "oe_contact_type",
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
 *     "add-form" = "/admin/structure/oe_contact_type/add",
 *     "edit-form" = "/admin/structure/oe_contact_type/{oe_contact_type}/edit",
 *     "delete-form" = "/admin/structure/oe_contact_type/{oe_contact_type}/delete",
 *     "collection" = "/admin/structure/oe_contact_type",
 *   }
 * )
 */
class ContactType extends CorporateEntityTypeBase {}
