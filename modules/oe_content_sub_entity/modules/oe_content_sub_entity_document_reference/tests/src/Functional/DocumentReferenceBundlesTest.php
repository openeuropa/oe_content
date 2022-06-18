<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_sub_entity_document_reference\Functional;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\media\Entity\Media;
use Drupal\media\MediaInterface;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\field\Traits\EntityReferenceTestTrait;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Tests the document reference bundles defined in this module.
 */
class DocumentReferenceBundlesTest extends BrowserTestBase {

  use EntityReferenceTestTrait;
  use TestFileCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'oe_content_sub_entity_document_reference',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create a node type.
    NodeType::create([
      'type' => 'page',
      'name' => 'Basic page',
    ])->save();
    $this->createEntityReferenceField(
      'node',
      'page',
      'node_docs',
      // Make the title fully distinguishable from other documents fields.
      'Node docs',
      'oe_document_reference',
      'default',
      [],
      FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    );

    // Create form display.
    $node_form_display = EntityFormDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'page',
      'mode' => 'default',
      'status' => TRUE,
    ]);
    // Remove form elements that are not relevant for the test, for more
    // reliable selectors and so that we can ignore required base fields.
    foreach ($node_form_display->getComponents() as $name => $component) {
      if ($name !== 'title') {
        $node_form_display->removeComponent($name);
      }
    }
    $node_form_display->setComponent('node_docs', [
      'type' => 'inline_entity_form_complex',
    ]);
    $node_form_display->save();

    // Create view display.
    $node_view_display = EntityViewDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'page',
      'mode' => 'default',
      'status' => TRUE,
    ]);
    // Only show fields that are relevant for the test.
    foreach ($node_view_display->getComponents() as $name => $component) {
      if ($name !== 'title') {
        $node_view_display->removeComponent($name);
      }
    }
    $node_view_display->setComponent('node_docs', [
      'type' => 'entity_reference_entity_view',
    ]);
    $node_view_display->save();
  }

  /**
   * Tests document references embedded in content.
   */
  public function testContentWithDocumentReferences(): void {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    // Login with editor-like permissions.
    $this->drupalLogin($this->drupalCreateUser([
      'create page content',
    ]));

    // Create a new page.
    $this->drupalGet('node/add/page');
    $page->fillField('Title', 'Example page');

    // Find the documents field group.
    $subform = $page->find('css', '#edit-node-docs');

    // Add a single document.
    $subform->selectFieldOption('node_docs[actions][bundle]', 'Document');
    $subform->pressButton('Add new document reference');
    $document = $this->createDocumentMedia(0);
    $subform->fillField(
      'Use existing media',
      $document->label() . ' (' . $document->id() . ')',
    );
    $subform->pressButton('Create document reference');

    // Add a document group.
    $subform->selectFieldOption('node_docs[actions][bundle]', 'Document group');
    $subform->pressButton('Add new document reference');
    $subform->fillField('Title', 'Example documents group');
    $document = $this->createDocumentMedia(1);
    $subform->fillField(
      'node_docs[form][1][oe_documents][0][target_id]',
      $document->label() . ' (' . $document->id() . ')',
    );
    $subform->pressButton('Add another item');
    $document = $this->createDocumentMedia(2);
    $subform->fillField(
      'node_docs[form][1][oe_documents][1][target_id]',
      $document->label() . ' (' . $document->id() . ')',
    );
    $subform->pressButton('Create document reference');
    $page->pressButton('Save');

    // Login with visitor-like permissions.
    $this->drupalLogin($this->drupalCreateUser([
      'access content',
      'view media',
    ]));
    $this->drupalGet('node/1');

    // Check the newly created page.
    $assert_session->elementTextEquals('css', 'h1.page-title', 'Example page');
    $assert_session->elementsCount('css', '.media', 3);
    $assert_session->elementsCount('css', '.file', 3);

    // Get document reference field items.
    $references = $page->findAll('css', '.field--name-node-docs > .field__items > .field__item');

    // Check single document reference.
    $assert_session->elementsCount('css', '.media', 1, $references[0]);
    $assert_session->elementsCount('css', '.file', 1, $references[0]);
    $link = $references[0]->find('css', '.media .file a');
    $this->assertSame('text-0.txt', $link->getText());
    $this->assertStringContainsString('files/text-0', $link->getAttribute('href'));

    // Check the document group.
    $this->assertSame(
      'Example documents group',
      $assert_session
        ->elementExists('css', '.field--name-oe-title > .field__item', $references[1])
        ->getText()
    );
    $links = $references[1]->findAll('css', '.media .file a');
    $this->assertSame('text-1.txt', $links[0]->getText());
    $this->assertStringContainsString('files/text-1', $links[0]->getAttribute('href'));
    $this->assertSame('text-2.txt', $links[1]->getText());
    $this->assertStringContainsString('files/text-2', $links[1]->getAttribute('href'));
  }

  /**
   * Creates a document media entity.
   *
   * @param int $index
   *   Index to distinguish from other documents.
   *   The index will be included in all labels, and is also used to pick a
   *   specific file entity.
   *   This is limited by the number of files returned from ->getTestFiles().
   *
   * @return \Drupal\media\MediaInterface
   *   The media entity.
   */
  protected function createDocumentMedia(int $index): MediaInterface {
    $name = 'Example document ' . $index;
    $media = Media::create([
      'bundle' => 'document',
      'oe_media_file_type' => 'local',
      'name' => "$name name",
      'oe_media_file' => [
        'target_id' => $this->createFileEntity('text', $index)->id(),
        'title' => "$name title",
      ],
    ]);
    $media->save();
    return $media;
  }

  /**
   * Creates a file entity.
   *
   * @param string $type
   *   File type, e.g. 'text' or 'image'.
   * @param int $index
   *   Index to distinguish from other files of the same type.
   *   This is limited by the number of files returned from ->getTestFiles().
   *
   * @return \Drupal\file\FileInterface
   *   The file entity.
   */
  protected function createFileEntity(string $type, int $index): FileInterface {
    /** @var object[] $files */
    $files = $this->getTestFiles($type);
    $this->assertArrayHasKey($index, $files);
    $file_entity = File::create([
      'uri' => $files[$index]->uri,
    ]);
    $file_entity->save();
    return $file_entity;
  }

}
