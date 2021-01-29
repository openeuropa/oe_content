<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content\DocumentReference;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceTrait;

/**
 * Context to create publication document reference entities.
 */
class PublicationDocumentReferenceContext extends RawDrupalContext {

  use EntityReferenceTrait;
  use EntityLoadingTrait;

  /**
   * Run before fields are parsed by Drupal Behat extension.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope $scope
   *   Behat scope.
   *
   * @BeforeParseEntityFields(oe_document_reference,oe_publication)
   */
  public function alterDocumentReferenceFields(BeforeParseEntityFieldsScope $scope): void {
    // Maps human readable field names to their Behat parsable machine names.
    $mapping = [
      'Publication' => 'oe_publication',
      'Published' => 'status',
    ];

    foreach ($scope->getFields() as $key => $value) {
      switch ($key) {
        // Set Node entity reference fields.
        case 'Publication':
          $fields = $this->getReferenceField($mapping[$key], 'node', $value);
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
