<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content\Node;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceRevisionTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceTrait;

/**
 * Context to create person content entities.
 *
 * @SuppressWarnings(PHPMD)
 */
class PersonContentContext extends RawDrupalContext {

  use EntityReferenceRevisionTrait;
  use EntityReferenceTrait;
  use EntityLoadingTrait;

  /**
   * Run before fields are parsed by Drupal Behat extension.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope $scope
   *   Behat hook scope.
   *
   * @BeforeParseEntityFields(node,oe_person)
   */
  public function alterPersonFields(BeforeParseEntityFieldsScope $scope): void {
    // Map human readable field names to their Behat parsable machine names.
    $mapping = [
      'Title' => 'title',
      'Biography' => 'oe_person_biography_timeline',
      'Biography introduction' => 'oe_person_biography_intro',
      'Contact' => 'oe_person_contacts',
      'CV upload' => 'oe_person_cv',
      'Declaration of interests file' => 'oe_person_interests_file',
      'Declaration of interests introduction' => 'oe_person_interests_intro',
      'Displayed name' => 'oe_person_displayed_name',
      'First name' => 'oe_person_first_name',
      'Gender' => 'oe_person_gender',
      'Introduction' => 'oe_summary',
      'Last name' => 'oe_person_last_name',
      'Media' => 'oe_person_media',
      'Organisation' => 'oe_person_organisation',
      'Portrait photo' => 'oe_person_photo',
      'Departments' => 'oe_departments',
      'Social media links' => 'oe_social_media_links',
      'Subject' => 'oe_subject',
      'Teaser' => 'oe_teaser',
      'Transparency introduction' => 'oe_person_transparency_intro',
      'Transparency links' => 'oe_person_transparency_links',
      'What type of person are you adding?' => 'oe_person_type',
      'Alternative title' => 'oe_content_short_title',
      'Published' => 'status',
    ];

    foreach ($scope->getFields() as $key => $value) {
      switch ($key) {
        // Set SKOS Concept entity reference fields.
        case 'Gender':
        case 'Departments':
        case 'Subject':
          $fields = $this->getReferenceField($mapping[$key], 'skos_concept', $value);
          $scope->addFields($fields)->removeField($key);
          break;

        // Set Media entity reference fields.
        case 'CV upload':
        case 'Declaration of interests file':
        case 'Media':
        case 'Portrait photo':
          $fields = $this->getReferenceField($mapping[$key], 'media', $value);
          $scope->addFields($fields)->removeField($key);
          break;

        case 'Organisation':
          $fields = $this->getReferenceRevisionField($mapping[$key], 'oe_organisation', $value);
          $scope->addFields($fields)->removeField($key);
          break;

        case 'Contact':
          $fields = $this->getReferenceRevisionField($mapping[$key], 'oe_contact', $value);
          $scope->addFields($fields)->removeField($key);
          break;

        // Set content published status.
        case 'Published':
          $scope->addFields([
            $mapping[$key] => (int) ($value === 'Yes'),
          ])->removeField($key);
          break;

        default:
          if (isset($mapping[$key])) {
            $scope->renameField($key, $mapping[$key]);
          }
      }
    }

    // Set default fields.
    $scope->addFields([
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM',
    ]);
  }

}
