<?php

declare(strict_types=1);

namespace Drupal\oe_content_entity_contact\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\oe_content_entity\Entity\CorporateEntityBase;

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
 *   base_table = "oe_contact",
 *   data_table = "oe_contact_field_data",
 *   revision_table = "oe_contact_revision",
 *   revision_data_table = "oe_contact_field_revision",
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
 *   bundle_entity_type = "oe_contact_type",
 *   field_ui_base_route = "entity.oe_contact_type.edit_form",
 *   links = {
 *     "canonical" = "/admin/content/oe_contact/{oe_contact}/edit",
 *     "add-page" = "/admin/content/oe_contact/add",
 *     "add-form" = "/admin/content/oe_contact/add/{oe_contact_type}",
 *     "edit-form" = "/admin/content/oe_contact/{oe_contact}/edit",
 *     "delete-form" = "/admin/content/oe_contact/{oe_contact}/delete",
 *     "collection" = "/admin/content/oe_contact",
 *   },
 *  type = {"entity"}
 * )
 */
class Contact extends CorporateEntityBase implements ContactInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['name']->setDescription(t('Name of office, organisation or person the contact details refer to.'));

    return $fields;
  }

}
