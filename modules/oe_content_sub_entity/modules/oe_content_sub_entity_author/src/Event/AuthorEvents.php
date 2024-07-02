<?php

declare(strict_types=1);

namespace Drupal\oe_content_sub_entity_author\Event;

/**
 * Defines events for the Author.
 *
 * @internal
 */
final class AuthorEvents {

  /**
   * The name of the event used for extracting Author links for each bundle.
   */
  const EXTRACT_AUTHOR_LINKS = 'oe_content_sub_entity_author.extract_links';

}
