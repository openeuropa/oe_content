<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\Entity;

use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the EventProfile entity.
 *
 * @ingroup oe_content_event
 *
 * @ContentEntityType(
 *   id = "event_profile",
 *   label = @Translation("Event profile"),
 *   bundle_label = @Translation("Event profile type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\oe_content_event\EventProfileListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\oe_content_event\Form\EventProfileForm",
 *       "add" = "Drupal\oe_content_event\Form\EventProfileForm",
 *       "edit" = "Drupal\oe_content_event\Form\EventProfileForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "event_profile",
 *   data_table = "event_profile_field_data",
 *   revision_table = "event_profile_revision",
 *   revision_data_table = "event_profile_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer event profile entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
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
 *   bundle_entity_type = "event_profile_type",
 *   field_ui_base_route = "entity.event_profile_type.edit_form",
 *   links = {
 *     "add-form" = "/event_profile/add/{event_profile_type}",
 *     "add-page" = "/event_profile/add",
 *     "canonical" = "/event_profile/{event_profile}",
 *     "collection" = "/admin/content/event_profiles",
 *     "edit-form" = "/event_profile/{event_profile}/edit",
 *     "delete-form" = "/event_profile/{event_profile}/delete",
 *   }
 * )
 */
class EventProfile extends EditorialContentEntityBase implements EventProfileInterface {

  /**
   * {@inheritdoc}
   */
  public function getType(): string {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName(string $title): EventProfileInterface {
    $this->set('name', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime(): int {
    return (int) $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime(int $timestamp): EventProfileInterface {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['status']
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 120,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the list was created.'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the list was last edited.'))
      ->setRevisionable(TRUE);

    return $fields;
  }

}
