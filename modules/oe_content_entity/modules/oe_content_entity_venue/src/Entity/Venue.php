<?php

declare(strict_types=1);

namespace Drupal\oe_content_entity_venue\Entity;

use Drupal\oe_content_entity\Entity\CorporateEntityBase;

/**
 * Defines the Venue entity.
 *
 * @ingroup oe_content_entity_venue
 *
 * @ContentEntityType(
 *   id = "oe_venue",
 *   label = @Translation("Venue"),
 *   label_collection = @Translation("Venues"),
 *   bundle_label = @Translation("Venue type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\oe_content_entity\CorporateEntityListBuilder",
 *     "access" = "Drupal\oe_content_entity\CorporateEntityAccessControlHandler",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\oe_content_entity\Form\CorporateEntityForm",
 *       "add" = "Drupal\oe_content_entity\Form\CorporateEntityForm",
 *       "edit" = "Drupal\oe_content_entity\Form\CorporateEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\oe_content_entity\Routing\CorporateEntityRouteProvider",
 *     },
 *   },
 *   base_table = "oe_venue",
 *   data_table = "oe_venue_field_data",
 *   revision_table = "oe_venue_revision",
 *   revision_data_table = "oe_venue_field_revision",
 *   translatable = TRUE,
 *   show_revision_ui = TRUE,
 *   admin_permission = "manage corporate content entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "bundle" = "bundle",
 *     "uuid" = "uuid",
 *     "label" = "name",
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
 *   bundle_entity_type = "oe_venue_type",
 *   field_ui_base_route = "entity.oe_venue_type.edit_form",
 *   links = {
 *     "canonical" = "/admin/content/oe_venue/{oe_venue}",
 *     "add-page" = "/admin/content/oe_venue/add",
 *     "add-form" = "/admin/content/oe_venue/add/{oe_venue_type}",
 *     "edit-form" = "/admin/content/oe_venue/{oe_venue}/edit",
 *     "delete-form" = "/admin/content/oe_venue/{oe_venue}/delete",
 *     "collection" = "/admin/content/oe_venue",
 *   },
 *  type = {"entity"}
 * )
 */
class Venue extends CorporateEntityBase implements VenueInterface {}
