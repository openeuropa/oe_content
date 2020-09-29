<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content\Contact;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceTrait;

/**
 * Context to create press contact corporate entities.
 */
class PressContactContext extends RawDrupalContext {

  use EntityReferenceTrait;
  use EntityLoadingTrait;

  /**
   * Run before fields are parsed by Drupal Behat extension.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope $scope
   *   Behat scope.
   *
   * @BeforeParseEntityFields(oe_contact,oe_press)
   */
  public function alterPressContactFields(BeforeParseEntityFieldsScope $scope): void {
    // Maps human readable field names to their Behat parsable machine names.
    $mapping = [
      'Name' => 'name',
      'Body text' => 'oe_body',
      'Organisation' => 'oe_organisation',
      'Address' => 'oe_address',
      'Email' => 'oe_email',
      'Image' => 'oe_image',
      'Image caption' => 'oe_image:caption',
      'Phone number' => 'oe_phone',
      'Mobile number' => 'oe_mobile',
      'Fax number' => 'oe_fax',
      'Press contact' => 'oe_press_contact_url',
      'Website' => 'oe_website',
      'Social media links' => 'oe_social_media',
      'Published' => 'status',
    ];

    foreach ($scope->getFields() as $key => $value) {
      switch ($key) {
        // Set Media entity reference fields.
        case 'Image':
          $fields = $this->getReferenceField($mapping[$key], 'media', $value);
          $scope->addFields($fields)->removeField($key);
          break;

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
  }

}
