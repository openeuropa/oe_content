<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity_contact\Entity;

use Drupal\oe_content_entity\Entity\EntityBase;

/**
 * Defines the Contact entity.
 *
 * @ingroup oe_content_entity_contact
 *
 * @ContentEntityType(
 *   id = "oe_contact",
 *   label = @Translation("Contact"),
 *   label_collection = @Translation("Contacts"),
 *   bundle_label = @Translation("Contact type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\oe_content_entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\oe_content_entity\Form\EntityForm",
 *       "add" = "Drupal\oe_content_entity\Form\EntityForm",
 *       "edit" = "Drupal\oe_content_entity\Form\EntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "oe_contact",
 *   data_table = "oe_contact_field_data",
 *   revision_table = "oe_contact_revision",
 *   revision_data_table = "oe_contact_field_revision",
 *   translatable = TRUE,
 *   show_revision_ui = TRUE,
 *   admin_permission = "manage custom content entities",
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
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log"
 *   },
 *   bundle_entity_type = "oe_contact_type",
 *   field_ui_base_route = "entity.oe_contact_type.edit_form",
 *   links = {
 *     "canonical" = "/admin/content/custom-entities/oe_contact/{oe_contact}",
 *     "add-page" = "/admin/content/custom-entities/oe_contact/add",
 *     "add-form" = "/admin/content/custom-entities/oe_contact/add/{oe_contact_type}",
 *     "edit-form" = "/admin/content/custom-entities/oe_contact/{oe_contact}/edit",
 *     "delete-form" = "/admin/content/custom-entities/oe_contact/{oe_contact}/delete",
 *     "collection" = "/admin/content/custom-entities/oe_contact",
 *   },
 *  type = {"entity"}
 * )
 */
class Contact extends EntityBase {}
