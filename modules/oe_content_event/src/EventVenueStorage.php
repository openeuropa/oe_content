<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\oe_content_event\Entity\EventVenueInterface;

/**
 * Defines an interface for Event venue entity storage classes.
 *
 * This extends the base storage class, adding required special handling for
 * Event venue entities.
 *
 * @ingroup event
 */
class EventVenueStorage extends SqlContentEntityStorage implements EventVenueStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(EventVenueInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {event_venue_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {event_venue_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(EventVenueInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {event_venue_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('event_venue_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
