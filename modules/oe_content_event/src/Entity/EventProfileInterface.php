<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\Entity;

/**
 * Provides an interface for Event profile entities.
 *
 * @ingroup oe_content_event
 */
interface EventProfileInterface {

  /**
   * Gets the event profile type.
   *
   * @return string
   *   The event profile type.
   */
  public function getType(): string;

  /**
   * Gets the event profile name.
   *
   * @return string
   *   Title of the event profile.
   */
  public function getName(): string;

  /**
   * Sets the event profile name.
   *
   * @param string $name
   *   The event profile name.
   *
   * @return \Drupal\oe_content_event\Entity\EventProfileInterface
   *   The called event profile entity.
   */
  public function setName(string $name): EventProfileInterface;

  /**
   * Gets the event profile configuration.
   *
   * @return string
   *   Configuration of the event profile.
   */
  public function getConfiguration(): string;

  /**
   * Sets the event profile configuration.
   *
   * @param string $settings
   *   The event profile configuration.
   *
   * @return \Drupal\oe_content_event\Entity\EventProfileInterface
   *   The called event profile entity.
   */
  public function setConfiguration(string $settings): EventProfileInterface;

  /**
   * Gets the event profile creation timestamp.
   *
   * @return int
   *   Creation timestamp of the event profile.
   */
  public function getCreatedTime(): int;

  /**
   * Sets the event profile creation timestamp.
   *
   * @param int $timestamp
   *   The event profile creation timestamp.
   *
   * @return \Drupal\oe_content_event\Entity\EventProfileInterface
   *   The called event profile entity.
   */
  public function setCreatedTime(int $timestamp): EventProfileInterface;

  /**
   * Gets the event profile revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the event profile revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\oe_content_event\Entity\EventProfileInterface
   *   The called event profile entity.
   */
  public function setRevisionCreationTime($timestamp);

}
