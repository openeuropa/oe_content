<?php

/**
 * @file
 * Builds placeholder replacement tokens for oe_content content types.
 */

declare(strict_types=1);

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function oe_content_token_info() {

  // Short title fallback token.
  $node['short-title-fallback'] = [
    'name' => t('Short title fallback'),
    'description' => t('Short title field in case it is not empty or the title field otherwise.'),
  ];

  return [
    'tokens' => ['node' => $node],
  ];
}

/**
 * Implements hook_tokens().
 */
function oe_content_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type !== 'node' || empty($data['node'])) {
    return [];
  }
  /** @var \Drupal\node\NodeInterface $node */
  $node = $data['node'];

  foreach ($tokens as $name => $original) {
    switch ($name) {
      case 'short-title-fallback':
        $replacements[$original] = $node->get('oe_content_short_title')->isEmpty() ? $node->getTitle() : $node->get('oe_content_short_title')->value;
        break;
    }
  }

  return $replacements;
}
