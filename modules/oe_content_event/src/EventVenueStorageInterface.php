<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface EventVenueStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Event venue revision IDs for a specific Event venue.
   *
   * @param \Drupal\oe_content_event\Entity\EventVenueInterface $entity
   *   The Event venue entity.
   *
   * @return int[]
   *   Event venue revision IDs (in ascending order).
   */
  public function revisionIds(EventVenueInterface $entity): array;

  /**
   * Gets a list of revision IDs having a given user as Event venue author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Event venue revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account): array;

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\oe_content_event\Entity\EventVenueInterface $entity
   *   The Event venue entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(EventVenueInterface $entity): int;

  /**
   * Unsets the language for all Event venue with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   *
   * @return \Drupal\oe_content_event\EventVenueStorageInterface
   *   The event venue storage.
   */
  public function clearRevisionsLanguage(LanguageInterface $language): EventVenueStorageInterface;

}
