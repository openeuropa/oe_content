<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Event venue entity.
 *
 * @ingroup oe_content_event
 *
 * @ContentEntityType(
 *   id = "event_venue",
 *   label = @Translation("Event venue"),
 *   handlers = {
 *     "storage" = "Drupal\oe_content_event\EventVenueStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\oe_content_event\EventVenueListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\oe_content_event\Form\EventVenueForm",
 *       "add" = "Drupal\oe_content_event\Form\EventVenueForm",
 *       "edit" = "Drupal\oe_content_event\Form\EventVenueForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "event_venue",
 *   data_table = "event_venue_field_data",
 *   revision_table = "event_venue_revision",
 *   revision_data_table = "event_venue_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer event venue entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log"
 *   },
 *   links = {
 *     "canonical" = "/event_venue/{event_venue}",
 *     "add-form" = "/admin/content/event_venue/add",
 *     "edit-form" = "/admin/content/event_venue/{event_venue}/edit",
 *     "delete-form" = "/admin/content/event_venue/{event_venue}/delete",
 *     "collection" = "/admin/content/event_venue",
 *   }
 * )
 */
class EventVenue extends EditorialContentEntityBase implements EventVenueInterface {

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName(string $name): EventVenueInterface {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCapacity(): string {
    return $this->get('capacity')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCapacity(string $capacity): EventVenueInterface {
    $this->set('capacity', $capacity);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRoom(): string {
    return $this->get('room')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRoom(string $room): EventVenueInterface {
    $this->set('room', $room);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime(): int {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime(int $timestamp): EventVenueInterface {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Event venue entity.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['capacity'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Capacity'))
      ->setDescription(t('The capacity of the venue.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['room'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Room'))
      ->setDescription(t('The name of the room.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['postal_address'] = BaseFieldDefinition::create('address')
      ->setLabel(t('Postal address'))
      ->setDescription(t('The address of the venue.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'available_countries' => [],
        'langcode_override' => '',
        'field_overrides' => [
          'givenName' => [
            'override' => 'hidden',
          ],
          'additionalName' => [
            'override' => 'hidden',
          ],
          'familyName' => [
            'override' => 'hidden',
          ],
        ],
        'fields' => [],
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['status']->setDescription(t('A boolean indicating whether the Event venue is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
