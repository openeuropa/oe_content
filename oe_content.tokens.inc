<?php

/**
 * @file
 * Builds placeholder replacement tokens for oe_content content types.
 */

declare(strict_types = 1);

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function oe_content_token_info() {

  // Core tokens for nodes.
  $node['short-title-fallback'] = [
    'name' => t("Short title fallback"),
    'description' => t('Short title field in case it is available or the title field otherwise.'),
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

  if ($type != 'node' || empty($data['node'])) {
    return [];
  }
  /** @var \Drupal\node\NodeInterface $node */
  $node = $data['node']->getUntranslated();

  foreach ($tokens as $name => $original) {
    switch ($name) {
      case 'short-title-fallback':
        $replacements[$original] = $node->oe_content_short_title->value ?? $node->getTitle() ;
        break;
    }
  }

  return $replacements;

}
