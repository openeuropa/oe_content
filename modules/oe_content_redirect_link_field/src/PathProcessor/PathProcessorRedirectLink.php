<?php

declare(strict_types = 1);

namespace Drupal\oe_content_redirect_link_field\PathProcessor;

use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\oe_content_redirect_link_field\RetrieveRedirectLinkInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Path processor to replace path by uri from oe_redirect_link field.
 */
class PathProcessorRedirectLink implements OutboundPathProcessorInterface {

  /**
   * The redirect link retriever service.
   *
   * @var \Drupal\oe_content_redirect_link_field\RetrieveRedirectLinkInterface
   */
  protected $redirectLinkRetriever;

  /**
   * Constructs a PathProcessorRedirectLink object.
   *
   * @param \Drupal\oe_content_redirect_link_field\RetrieveRedirectLinkInterface $redirect_link_retriver
   *   The redirect link retriever.
   */
  public function __construct(RetrieveRedirectLinkInterface $redirect_link_retriver) {
    $this->redirectLinkRetriever = $redirect_link_retriver;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    return $this->redirectLinkRetriever->getPath($path, $options, $bubbleable_metadata);
  }

}
