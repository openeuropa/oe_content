<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Traits;

use Behat\Mink\Element\NodeElement;

/**
 * Provides methods to interact with named parts of the page.
 */
trait RegionTrait {

  /**
   * Maps the region names to the CSS selector that identifies them.
   *
   * @var string[]
   */
  protected static $regions = [
    'title form element' => '.field--name-title',
    'teaser form element' => '.field--name-oe-teaser',
    'summary form element' => '.field--name-oe-summary',
    'featured media form element' => '.form-wrapper.field--name-oe-featured-media',
    'featured media legend form element' => '.form-wrapper.field--name-oe-event-featured-media-legend',
    'featured media field' => '.field.field--name-oe-featured-media',
    'alternative title form element' => '.field--name-oe-content-short-title',
    'subject form element' => '.field--name-oe-subject',
    'redirect link form element' => '.field--name-oe-content-legacy-link',
    'content owner form element' => '.field--name-oe-content-content-owner',
    'first name form element' => '#edit-oe-person-first-name-wrapper',
    'last name form element' => '#edit-oe-person-last-name-wrapper',
    'Online link' => '#edit-oe-event-online-link-0',
    'Website' => '#edit-oe-event-website-0',
    'Project Website' => '#edit-oe-project-website-0',
    'Alternative titles and teaser' => '#edit-group-alternative-titles-teaser',
    'Budget' => '#edit-group-budget',
    'Result' => '#edit-group-result',
    'Call for proposals' => '#oe-project-calls-values',
    'Social media links' => '#oe-social-media-links-values',
    'Event media' => '.field--name-oe-event-media',
    'Description' => '#edit-oe-event-featured-media',
    'Event report' => '#edit-group-report',
    'Event contact' => '#edit-oe-event-contact-wrapper',
    'Call for proposals contact' => '#edit-oe-call-proposals-contact-wrapper',
    'Project coordinators' => '.field--name-oe-project-coordinators',
    'Project participants' => '.field--name-oe-project-participants',
    'Project result files' => '.field--name-oe-project-result-files',
    'Project documents' => '.field--name-oe-documents',
    'Project contact' => '#edit-oe-project-contact-wrapper',
    'Project locations' => '#edit-oe-project-locations-wrapper',
    'Documents' => '#edit-oe-documents',
    'Event venue' => '#edit-oe-event-venue-wrapper',
    'Contact social media links' => '.field--name-oe-social-media',
    'Contact link' => '.field--name-oe-link',
    'Organisation contact' => '#edit-oe-organisation-contact-wrapper',
    'Organisation chart' => '.field--name-oe-organisation-chart',
    'Organisation Transparency links' => '.field--name-oe-organisation-transp-links',
    'News contact' => '#edit-oe-news-contacts-wrapper',
    'News sources' => '.field--name-oe-news-sources',
    'Publication in the official journal' => '.field--name-oe-call-proposals-journal',
    'Grants awarded link' => '.field--name-oe-call-proposals-grants-link',
    'Related links' => '.field--name-oe-related-links',
    'Deadline model' => '#edit-oe-call-proposals-model-wrapper',
    'Deadline date' => '#edit-oe-call-proposals-deadline-wrapper',
    'Reference code form element' => '.field--name-oe-reference-code',
    'Publication contact' => '.field--name-oe-publication-contacts',
    'Publication thumbnail' => '#edit-oe-publication-thumbnail',
    'Reference codes' => '#edit-oe-reference-codes-wrapper',
    'Person portrait photo' => '#edit-oe-person-photo',
    'Person Media' => '#edit-oe-person-media',
    'Person jobs' => '#edit-oe-person-jobs-wrapper',
    'Person documents' => '#edit-oe-person-documents-wrapper',
    'Person CV upload' => '#edit-oe-person-cv',
    'Person declaration of interests file' => '#edit-oe-person-interests-file',
    'Person contacts' => '#edit-oe-person-contacts-wrapper',
    'Person transparency links' => '#edit-oe-person-transparency-links-wrapper',
    'Person biography' => '#edit-oe-person-biography-timeline-wrapper',
    'Person description' => '#edit-oe-person-description-wrapper',
    'Timeline' => '#edit-oe-timeline-wrapper',
    'Consultation contacts' => '#edit-oe-consultation-contacts-wrapper',
    'Consultation documents' => '#edit-oe-consultation-documents-wrapper',
    'Event programme' => '#edit-oe-event-programme-wrapper',
    'Programme name' => '.field--name-oe-event-programme .field--name-name',
  ];

  /**
   * Returns the element of the given region.
   *
   * @param string $region
   *   The region name.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The region element.
   */
  protected function getRegion(string $region): NodeElement {
    if (!isset(static::$regions[$region])) {
      throw new \InvalidArgumentException(sprintf('The region "%s" is not mapped to any selector.', $region));
    }

    $element = $this->getSession()->getPage()->find('css', static::$regions[$region]);
    if (!$element) {
      throw new \InvalidArgumentException(sprintf('No region "%s" found on the page.', $region));
    }

    return $element;
  }

  /**
   * Fills in a field inside a region.
   *
   * @param string $region
   *   The region name.
   * @param string $field
   *   The field locator, usually its label.
   * @param string $value
   *   The value to fill in.
   */
  protected function fillFieldInRegion(string $region, string $field, string $value): void {
    $this->getRegion($region)->fillField($field, $value);
  }

  /**
   * Selects an option of a select field inside a region.
   *
   * @param string $region
   *   The region name.
   * @param string $field
   *   The field locator, usually its label.
   * @param string $option
   *   The option to select.
   */
  protected function selectFieldOptionInRegion(string $region, string $field, string $option): void {
    $this->getRegion($region)->selectFieldOption($field, $option);
  }

  /**
   * Selects an option of the only select field present inside a region.
   *
   * @param string $region
   *   The region name.
   * @param string $option
   *   The option to select.
   */
  protected function selectSingleOptionInRegion(string $region, string $option): void {
    $select = $this->getRegion($region)->find('css', 'select');
    if (!$select) {
      throw new \InvalidArgumentException(sprintf('No select element found in the region "%s".', $region));
    }
    $select->selectOption($option);
  }

  /**
   * Presses a button inside a region.
   *
   * @param string $region
   *   The region name.
   * @param string $button
   *   The button locator, usually its value.
   */
  protected function pressButtonInRegion(string $region, string $button): void {
    $this->getRegion($region)->pressButton($button);
  }

  /**
   * Checks a checkbox inside a region.
   *
   * @param string $region
   *   The region name.
   * @param string $field
   *   The field locator, usually its label.
   */
  protected function checkFieldInRegion(string $region, string $field): void {
    $this->getRegion($region)->checkField($field);
  }

  /**
   * Asserts that a region contains the given text.
   *
   * @param string $region
   *   The region name.
   * @param string $text
   *   The text to look for.
   */
  protected function assertRegionText(string $region, string $text): void {
    $this->assertStringContainsString($text, $this->getRegion($region)->getText(), sprintf('The text "%s" was not found in the region "%s".', $text, $region));
  }

  /**
   * Asserts that a region does not contain the given text.
   *
   * @param string $region
   *   The region name.
   * @param string $text
   *   The text to look for.
   */
  protected function assertNoRegionText(string $region, string $text): void {
    $this->assertStringNotContainsString($text, $this->getRegion($region)->getText(), sprintf('The text "%s" was unexpectedly found in the region "%s".', $text, $region));
  }

  /**
   * Asserts that a region contains a link with the given label.
   *
   * @param string $region
   *   The region name.
   * @param string $link
   *   The link label or partial URL.
   */
  protected function assertRegionLink(string $region, string $link): void {
    $this->assertNotNull($this->getRegion($region)->findLink($link), sprintf('The link "%s" was not found in the region "%s".', $link, $region));
  }

}
