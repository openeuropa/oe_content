<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_redirect_link_field\FunctionalJavascript;

use Drupal\field\Entity\FieldConfig;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\link\LinkItemInterface;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests the redirect link field within the node add/edit form.
 */
class NodeFormRedirectLinkTest extends WebDriverTestBase {

  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'seven';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_content_redirect_link_field',
  ];

  /**
   * The Node type.
   *
   * @var \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   */
  protected $nodeType;

  /**
   * The user for interacting with node.
   *
   * @var \Drupal\user\Entity\User|false
   */
  protected $nodeUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->nodeType = NodeType::create([
      'type' => 'test_with_redirect_link',
      'title' => 'test Node type',
    ]);
    $this->nodeType->save();

    $this->nodeUser = $this->createUser([
      'create test_with_redirect_link content',
      'edit own test_with_redirect_link content',
    ]);
  }

  /**
   * Test 'oe_redirect_link' behavior.
   */
  public function testRedirectLinkForm(): void {
    FieldConfig::create([
      'entity_type' => 'node',
      'field_name' => 'oe_redirect_link',
      'bundle' => $this->nodeType->id(),
      'settings' => [
        'link_type' => LinkItemInterface::LINK_EXTERNAL,
        // Enable the title field so we can test it doesn't show up.
        'title' => 1,
      ],
    ])->save();
    /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $display_repository */
    $display_repository = \Drupal::service('entity_display.repository');
    $display_repository->getFormDisplay('node', $this->nodeType->id())
      ->setComponent('oe_redirect_link', [
        'type' => 'link_default',
        'settings' => [],
      ])
      ->save();

    $this->drupalLogin($this->nodeUser);
    $this->drupalGet('node/add/' . $this->nodeType->id());

    // Make sure that checkbox for enabling the redirect link is present.
    $page = $this->getSession()->getPage();
    $redirect_link_details = $page->findAll('css', 'div#edit-advanced details#edit-redirect-link');
    $this->assertCount(1, $redirect_link_details);
    $redirect_link_detail_block = reset($redirect_link_details);
    $this->assertEquals('open', $redirect_link_detail_block->getAttribute('open'));
    $redirect_link_enable_checkbox = $redirect_link_detail_block->findField('Redirect this page to an external link');
    $this->assertFalse($redirect_link_enable_checkbox->isChecked());
    $redirect_link_field = $redirect_link_detail_block->find('css', 'div#edit-oe-redirect-link-wrapper');
    $this->assertFalse($redirect_link_field->isVisible());

    // Make sure that 'redirect link' field is visible on checking the box.
    $redirect_link_enable_checkbox->check();
    $this->assertTrue($redirect_link_field->isVisible());
    $this->assertTrue($redirect_link_field->hasField('oe_redirect_link[0][uri]'));
    $this->assertTrue($redirect_link_field->findField('oe_redirect_link[0][uri]')->isVisible());
    // There should be no title element on the Link field regardless of
    // configuration.
    $this->assertEmpty($redirect_link_field->findField('oe_redirect_link[0][title]'));
    $this->assertEquals('This page will be redirected to this URL. All of its translations that do not have a language specific Redirect link URL filled in will also be redirected to this URL. Removing this value will prevent the redirect from happening on all of the translations as well.', $redirect_link_field->findById('edit-oe-redirect-link-0-uri--description')->getText());

    // Save node with empty 'redirect link' field.
    $page->fillField('Title', 'Test node with redirect link');
    $page->pressButton('Save');
    $this->assertSession()->pageTextContains('Test node with redirect link has been created.');
    $node = $this->drupalGetNodeByTitle('Test node with redirect link');

    // Make sure that checkbox is unchecked if the 'redirect link' field is
    // empty.
    $this->drupalGet($node->toUrl('edit-form'));
    $this->assertFalse($redirect_link_enable_checkbox->isChecked());
    $this->assertFalse($redirect_link_field->isVisible());
    $redirect_link_enable_checkbox->check();
    $uri_field = $redirect_link_field->findField('oe_redirect_link[0][uri]');
    $this->assertEquals('', $uri_field->getValue());

    // Make sure that we accept only external links.
    $uri_field->setValue('http://example.com');
    $page->pressButton('Save');
    $this->assertSession()->pageTextContains('Test node with redirect link has been updated.');

    // Make sure that the 'redirect link' field is saved.
    $this->drupalGet($node->toUrl('edit-form'));
    $this->assertTrue($redirect_link_enable_checkbox->isChecked());
    $this->assertTrue($redirect_link_enable_checkbox->isVisible());
    $this->assertEquals('http://example.com', $uri_field->getValue());

    // Make sure that the 'redirect link' field is erased
    // with unchecked checkbox.
    $redirect_link_enable_checkbox->uncheck();
    $this->assertFalse($redirect_link_field->isVisible());
    $page->pressButton('Save');
    $this->drupalGet($node->toUrl('edit-form'));
    $this->assertFalse($redirect_link_enable_checkbox->isChecked());
    $this->assertFalse($redirect_link_field->isVisible());
    $this->assertEquals('', $uri_field->getValue());
  }

}
