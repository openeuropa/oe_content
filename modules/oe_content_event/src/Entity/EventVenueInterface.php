<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for Event venue entities.
 *
 * @ingroup oe_content_event
 */
interface EventVenueInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface {

  /**
   * Gets the Event venue name.
   *
   * @return string
   *   Name of the Event venue.
   */
  public function getName(): string;

  /**
   * Sets the Event venue name.
   *
   * @param string $name
   *   The Event venue name.
   *
   * @return \Drupal\oe_content_event\Entity\EventVenueInterface
   *   The called Event venue entity.
   */
  public function setName(string $name): EventVenueInterface;

  /**
   * Gets the Event venue capacity.
   *
   * @return string
   *   Capacity of the Event venue.
   */
  public function getCapacity(): string;

  /**
   * Sets the Event venue capacity.
   *
   * @param string $capacity
   *   The Event venue capacity.
   *
   * @return \Drupal\oe_content_event\Entity\EventVenueInterface
   *   The called Event venue entity.
   */
  public function setCapacity(string $capacity): EventVenueInterface;

  /**
   * Gets the Event venue room.
   *
   * @return string
   *   Room name of the Event venue.
   */
  public function getRoom(): string;

  /**
   * Sets the Event venue room.
   *
   * @param string $room
   *   The Event venue room name.
   *
   * @return \Drupal\oe_content_event\Entity\EventVenueInterface
   *   The called Event venue entity.
   */
  public function setRoom(string $room): EventVenueInterface;

  /**
   * Gets the Event venue creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Event venue.
   */
  public function getCreatedTime(): int;

  /**
   * Sets the Event venue creation timestamp.
   *
   * @param int $timestamp
   *   The Event venue creation timestamp.
   *
   * @return \Drupal\oe_content_event\Entity\EventVenueInterface
   *   The called Event venue entity.
   */
  public function setCreatedTime(int $timestamp): EventVenueInterface;

}
