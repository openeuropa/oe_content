<?php

declare(strict_types = 1);

namespace Drupal\oe_content\Event;

/**
 * Defines events for the Content.
 *
 * @internal
 */
final class ContentEvents {

  /**
   * The name of the event used for extracting Author links for each bundle.
   */
  const EXTRACT_AUTHOR_LINKS = 'oe_content.get_author_entity_links';

}
