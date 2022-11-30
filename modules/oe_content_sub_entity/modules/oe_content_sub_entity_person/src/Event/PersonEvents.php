<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity_person\Event;

/**
 * Defines events for the Person.
 *
 * @internal
 */
final class PersonEvents {

  /**
   * The name of the event used for extracting entity links for each bundle.
   */
  const EXTRACT_PERSON_LINKS = 'oe_content_sub_entity_person.extract_links';

}
