<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content\DocumentReference;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Content\CollectSubEntityTrait;
use Drupal\Tests\oe_content\Behat\Hook\Scope\AfterSaveEntityScope;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceTrait;

/**
 * Context to create document document reference entities.
 */
class DocumentDocumentReferenceContext extends RawDrupalContext {

  use EntityReferenceTrait;
  use EntityLoadingTrait;
  use CollectSubEntityTrait;

  /**
   * Run before fields are parsed by Drupal Behat extension.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope $scope
   *   Behat scope.
   *
   * @BeforeParseEntityFields(oe_document_reference,oe_document)
   */
  public function alterDocumentReferenceFields(BeforeParseEntityFieldsScope $scope): void {
    // Process name field if it exists to store entity in the content storage.
    $this->collectSubEntityName($scope);

    // Maps human readable field names to their Behat parsable machine names.
    $mapping = [
      'Document' => 'oe_document',
      'Published' => 'status',
    ];

    foreach ($scope->getFields() as $key => $value) {
      switch ($key) {
        // Set Media entity reference fields.
        case 'Document':
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

  /**
   * {@inheritdoc}
   *
   * @AfterSaveEntity(oe_document_reference,oe_document)
   */
  public function entitySaved(AfterSaveEntityScope $scope): void {
    $this->storeSubEntityObject($scope);
  }

}
