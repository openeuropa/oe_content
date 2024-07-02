<?php

declare(strict_types=1);

namespace Drupal\oe_content_event_event_programme\Entity;

use Drupal\oe_content_entity\Entity\CorporateEntityBase;

/**
 * Defines the Programme Item entity.
 *
 * @ingroup oe_content_event_event_programme
 *
 * @ContentEntityType(
 *   id = "oe_event_programme",
 *   label = @Translation("Programme Item"),
 *   label_collection = @Translation("Programme Items"),
 *   bundle_label = @Translation("Programme Item type"),
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
 *   base_table = "oe_event_programme",
 *   data_table = "oe_event_programme_field_data",
 *   revision_table = "oe_event_programme_revision",
 *   revision_data_table = "oe_event_programme_field_revision",
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
 *   bundle_entity_type = "oe_event_programme_type",
 *   field_ui_base_route = "entity.oe_event_programme_type.edit_form",
 *   links = {
 *     "canonical" = "/admin/content/oe_event_programme/{oe_event_programme}",
 *     "add-page" = "/admin/content/oe_event_programme/add",
 *     "add-form" = "/admin/content/oe_event_programme/add/{oe_event_programme_type}",
 *     "edit-form" = "/admin/content/oe_event_programme/{oe_event_programme}/edit",
 *     "delete-form" = "/admin/content/oe_event_programme/{oe_event_programme}/delete",
 *     "collection" = "/admin/content/oe_event_programme",
 *   },
 *  type = {"entity"}
 * )
 */
class ProgrammeItem extends CorporateEntityBase implements ProgrammeItemInterface {}
