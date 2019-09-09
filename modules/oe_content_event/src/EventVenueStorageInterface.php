<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\oe_content_event\Entity\EventVenueInterface;

/**
 * Defines the storage handler class for Event venue entities.
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
  public function revisionIds(EventVenueInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Event venue author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Event venue revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\oe_content_event\Entity\EventVenueInterface $entity
   *   The Event venue entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(EventVenueInterface $entity);

  /**
   * Unsets the language for all Event venue with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
