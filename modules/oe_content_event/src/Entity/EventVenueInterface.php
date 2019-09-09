<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Event venue entities.
 *
 * @ingroup oe_content_event
 */
interface EventVenueInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Event venue name.
   *
   * @return string
   *   Name of the Event venue.
   */
  public function getName();

  /**
   * Sets the Event venue name.
   *
   * @param string $name
   *   The Event venue name.
   *
   * @return \Drupal\oe_content_event\Entity\EventVenueInterface
   *   The called Event venue entity.
   */
  public function setName($name);

  /**
   * Gets the Event venue capacity.
   *
   * @return string
   *   Capacity of the Event venue.
   */
  public function getCapacity();

  /**
   * Sets the Event venue capacity.
   *
   * @param string $capacity
   *   The Event venue capacity.
   *
   * @return \Drupal\oe_content_event\Entity\EventVenueInterface
   *   The called Event venue entity.
   */
  public function setCapacity($capacity);

  /**
   * Gets the Event venue room.
   *
   * @return string
   *   Room name of the Event venue.
   */
  public function getRoom();

  /**
   * Sets the Event venue room.
   *
   * @param string $room
   *   The Event venue room name.
   *
   * @return \Drupal\oe_content_event\Entity\EventVenueInterface
   *   The called Event venue entity.
   */
  public function setRoom($room);

  /**
   * Gets the Event venue creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Event venue.
   */
  public function getCreatedTime();

  /**
   * Sets the Event venue creation timestamp.
   *
   * @param int $timestamp
   *   The Event venue creation timestamp.
   *
   * @return \Drupal\oe_content_event\Entity\EventVenueInterface
   *   The called Event venue entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Event venue revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Event venue revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\oe_content_event\Entity\EventVenueInterface
   *   The called Event venue entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Event venue revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Event venue revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\oe_content_event\Entity\EventVenueInterface
   *   The called Event venue entity.
   */
  public function setRevisionUserId($uid);

}
