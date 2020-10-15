<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content\Node;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceRevisionTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceTrait;

/**
 * Context to create news content entities.
 *
 * @SuppressWarnings(PHPMD)
 */
class NewsContentContext extends RawDrupalContext {

  use EntityReferenceRevisionTrait;
  use EntityReferenceTrait;
  use EntityLoadingTrait;

  /**
   * Run before fields are parsed by Drupal Behat extension.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope $scope
   *   Behat hook scope.
   *
   * @BeforeParseEntityFields(node,oe_news)
   */
  public function alterEventFields(BeforeParseEntityFieldsScope $scope): void {
    // Map human readable field names to their Behat parsable machine names.
    $mapping = [
      'Title' => 'title',
      'News type' => 'oe_news_types',
      'Reference' => 'oe_reference_code',
      'Location' => 'oe_news_location',
      'Body text' => 'body',
      'Featured media' => 'oe_news_featured_media',
      'Related links' => 'oe_related_links',
      'Contacts' => 'oe_news_contacts',
      'Introduction' => 'oe_summary',
      'Publication date' => 'oe_publication_date',
      'Teaser' => 'oe_teaser',
    ];

    foreach ($scope->getFields() as $key => $value) {

      // Handle entity references.
      switch ($key) {
        case 'Contacts':
          $fields = $this->getReferenceRevisionField('oe_news_contacts', 'oe_contact', $value);
          $scope->addFields($fields)->removeField($key);
          break;

        case 'Featured media':
          $fields = $this->getReferenceField($mapping[$key], 'media', $value);
          $scope->addFields($fields)->removeField($key);
          break;

        case 'News type':
        case 'Location':
          $fields = $this->getReferenceField($mapping[$key], 'skos_concept', $value);
          $scope->addFields($fields)->removeField($key);
          break;

        // Convert dates to UTC so that they can be expressed in site timezone.
        case 'Publication date':
          $date = DrupalDateTime::createFromFormat(DateTimePlus::FORMAT, $value)
            ->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, [
              'timezone' => DateTimeItemInterface::STORAGE_TIMEZONE,
            ]);
          $scope->addFields([$mapping[$key] => $date])->removeField($key);
          break;

        default:
          if (isset($mapping[$key])) {
            $scope->renameField($key, $mapping[$key]);
          }
      }
    }

    // Set default fields.
    $scope->addFields([
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM',
    ]);
  }

}
