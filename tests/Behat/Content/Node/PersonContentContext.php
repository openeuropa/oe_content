<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content\Node;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Content\Traits\GatherSubEntityContextTrait;
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
  use GatherSubEntityContextTrait;

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
      'Contacts' => 'oe_person_contacts',
      'CV upload' => 'oe_person_cv',
      'Declaration of interests file' => 'oe_person_interests_file',
      'Declaration of interests introduction' => 'oe_person_interests_intro',
      'Displayed name' => 'oe_person_displayed_name',
      'Documents' => 'oe_person_documents',
      'First name' => 'oe_person_first_name',
      'Gender' => 'oe_person_gender',
      'Introduction' => 'oe_summary',
      'Jobs' => 'oe_person_jobs',
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
        // Set entity reference fields.
        case 'Gender':
        case 'Departments':
        case 'CV upload':
        case 'Declaration of interests file':
        case 'Media':
        case 'Portrait photo':
          $fields = $this->getReferenceField($scope->getEntityType(), $scope->getBundle(), $mapping[$key], $value);
          $scope->addFields($fields)->removeField($key);
          break;

        case 'Organisation':
          $fields = $this->getReferenceRevisionField($mapping[$key], 'oe_organisation', $value);
          $scope->addFields($fields)->removeField($key);
          break;

        case 'Contacts':
          $fields = $this->getReferenceRevisionField($mapping[$key], 'oe_contact', $value);
          $scope->addFields($fields)->removeField($key);
          break;

        // Set entity reference revision fields for sub entities.
        case 'Documents':
        case 'Jobs':
          $ids = [];
          $revision_ids = [];
          $names = explode(', ', $value);
          foreach ($names as $name) {
            $entity = $this->subEntityContext->getSubEntityByName($name);
            $ids[] = $entity->id();
            $revision_ids[] = $entity->getRevisionId();
          }
          $scope->addFields([
            $mapping[$key] . ':target_id' => implode(',', $ids),
            $mapping[$key] . ':target_revision_id' => implode(',', $revision_ids),
          ]);
          $scope->removeField($key);
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
      'oe_subject' => 'http://data.europa.eu/uxp/1010',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM',
    ]);
  }

  /**
   * Fills in a oe_role_reference field.
   *
   * @When I fill in :delta person job role reference field with :value
   */
  public function fillPersonJobRoleReferenceField($row, $value): void {
    $row_map = [
      'first' => '0',
      'second' => '1',
      'third' => '2',
      'fourth' => '3',
      'fifth' => '4',
      'sixth' => '5',
    ];
    $delta = $row_map[$row];
    $this->getSession()->getPage()->fillField("oe_person_jobs[form][$delta][oe_role_reference][0][target_id]", $value);
  }

}
