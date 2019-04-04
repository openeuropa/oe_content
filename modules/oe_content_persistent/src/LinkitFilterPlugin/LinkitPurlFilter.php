<?php

namespace Drupal\oe_content_persistent\LinkitFilterPlugin;

use Drupal\Component\Utility\Html;
use Drupal\filter\FilterProcessResult;
use Drupal\linkit\Plugin\Filter\LinkitFilter;
use Drupal\linkit\SubstitutionManagerInterface;

/**
 * Provides overriden linkit filter.
 */
class LinkitPurlFilter extends LinkitFilter {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    if (strpos($text, 'data-entity-type') !== FALSE && strpos($text, 'data-entity-uuid') !== FALSE) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);

      foreach ($xpath->query('//a[@data-entity-type and @data-entity-uuid]') as $element) {
        /** @var \DOMElement $element */
        try {
          // Load the appropriate translation of the linked entity.
          $entity_type = $element->getAttribute('data-entity-type');
          $uuid = $element->getAttribute('data-entity-uuid');

          // Make the substitution optional, for backwards compatibility,
          // maintaining the previous hard-coded direct file link assumptions,
          // for content created before the substitution feature.
          if (!$substitution_type = $element->getAttribute('data-entity-substitution')) {
            $substitution_type = $entity_type === 'file' ? 'file' : SubstitutionManagerInterface::DEFAULT_SUBSTITUTION;
          }

          $entity = $this->entityRepository->loadEntityByUuid($entity_type, $uuid);
          if ($entity) {

            $entity = $this->entityRepository->getTranslationFromContext($entity, $langcode);

            /** @var \Drupal\Core\GeneratedUrl $url */
            $url = $this->substitutionManager
              ->createInstance($substitution_type)
              ->getUrl($entity);

            $element->setAttribute('href', $url->getGeneratedUrl());
            $access = $entity->access('view', NULL, TRUE);

            // Set the appropriate title attribute.
            if ($this->settings['title'] && !$access->isForbidden() && !$element->getAttribute('title')) {
              $element->setAttribute('title', $entity->label());
            }

            // The processed text now depends on:
            $result
              // - the linked entity access for the current user.
              ->addCacheableDependency($access)
              // - the generated URL (which has undergone path & route
              // processing)
              ->addCacheableDependency($url)
              // - the linked entity (whose URL and title may change)
              ->addCacheableDependency($entity);
          }
        }
        catch (\Exception $e) {
          watchdog_exception('linkit_filter', $e);
        }
      }

      $result->setProcessedText(Html::serialize($dom));
    }

    return $result;
  }

}
