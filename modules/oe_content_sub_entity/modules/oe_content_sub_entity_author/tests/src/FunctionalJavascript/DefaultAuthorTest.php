<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_sub_entity_author\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\oe_content\Traits\EntityReferenceRevisionTrait;
use Drupal\Tests\sparql_entity_storage\Traits\SparqlConnectionTrait;

/**
 * Tests the default content owner field values.
 */
class DefaultAuthorTest extends WebDriverTestBase {

  use SparqlConnectionTrait;
  use EntityReferenceRevisionTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_content',
    'oe_content_page',
    'oe_content_sub_entity_author',
    'oe_corporate_site_info',
    'inline_entity_form',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->setUpSparql();
  }

  /**
   * Tests the author values on new nodes.
   */
  public function testAuthorsDefaultValues() {
    $this->createEntityReferenceField('node', 'oe_page', 'oe_authors', 'Authors', 'oe_author');
    \Drupal::entityTypeManager()->getStorage('entity_form_display')
      ->load('node.oe_page.default')
      ->setComponent('oe_authors', [
        'type' => 'inline_entity_form_complex',
        'settings' => [
          'match_operator' => 'CONTAINS',
        ],
      ])
      ->save();

    // Set the default content owner values.
    $this->container->get('config.factory')->getEditable('oe_corporate_site_info.settings')
      ->set('content_owners', [
        'http://publications.europa.eu/resource/authority/corporate-body/AGRI',
        'http://publications.europa.eu/resource/authority/corporate-body/BUDG',
      ])
      ->save();

    $user = $this->drupalCreateUser([
      'access content',
      'create oe_page content',
      'edit any oe_page content',
      'view published skos concept entities',
    ]);

    $this->drupalLogin($user);

    $this->drupalGet('/node/add/oe_page');

    $assert_session = $this->assertSession();

    // Assert that Corporate body author values are set
    // when creating a new node.
    $this->getSession()->getPage()->pressButton('ief-oe_authors-form-entity-edit-0');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $assert_session->fieldValueEquals('oe_authors[form][inline_entity_form][entities][0][form][oe_skos_reference][0][target_id]', 'Directorate-General for Agriculture and Rural Development (http://publications.europa.eu/resource/authority/corporate-body/AGRI)');
    $assert_session->fieldValueEquals('oe_authors[form][inline_entity_form][entities][0][form][oe_skos_reference][1][target_id]', 'Directorate-General for Budget (http://publications.europa.eu/resource/authority/corporate-body/BUDG)');
    $this->getSession()->getPage()->fillField('oe_authors[form][inline_entity_form][entities][0][form][oe_skos_reference][1][target_id]', 'Arab Common Market');
    $this->getSession()->getPage()->fillField('Page title', 'Title');
    $this->getSession()->getPage()->fillField('Teaser', 'Teaser');
    $this->getSession()->getPage()->fillField('Subject', 'financing');
    $this->getSession()->getPage()->pressButton('Save');
    $this->drupalGet('/node/1/edit');
    $this->getSession()->getPage()->pressButton('ief-oe_authors-form-entity-edit-0');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $assert_session->fieldValueEquals('oe_authors[form][inline_entity_form][entities][0][form][oe_skos_reference][0][target_id]', 'Directorate-General for Agriculture and Rural Development (http://publications.europa.eu/resource/authority/corporate-body/AGRI)');
    $assert_session->fieldValueEquals('oe_authors[form][inline_entity_form][entities][0][form][oe_skos_reference][1][target_id]', 'Arab Common Market (http://publications.europa.eu/resource/authority/corporate-body/ACM)');

    // Unset the default content owner values.
    $this->container->get('config.factory')->getEditable('oe_corporate_site_info.settings')
      ->set('content_owners', [])
      ->save();

    $this->drupalGet('/node/add/oe_page');

    // Assert that no default Corporate body authors are set
    // when creating a new node.
    $assert_session->buttonNotExists('ief-oe_authors-form-entity-edit-0');

    // Uninstall the "Corporate Site Information" module.
    $this->container->get('module_installer')->uninstall(['oe_corporate_site_info']);

    $this->drupalGet('/node/add/oe_page');

    // Assert that no default Corporate body authors are set
    // when creating a new node.
    $assert_session->buttonNotExists('ief-oe_authors-form-entity-edit-0');
  }

}
