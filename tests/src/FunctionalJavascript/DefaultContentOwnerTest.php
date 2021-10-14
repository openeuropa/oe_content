<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\sparql_entity_storage\Traits\SparqlConnectionTrait;

/**
 * Tests the default content owner field values.
 */
class DefaultContentOwnerTest extends WebDriverTestBase {

  use SparqlConnectionTrait;

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
    'oe_corporate_site_info',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->setUpSparql();
  }

  /**
   * Tests the content owner values on new nodes.
   */
  public function testContentOwnerValues() {
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
      'view published skos concept entities',
    ]);

    $this->drupalLogin($user);

    $this->drupalGet('/node/add/oe_page');

    $assert_session = $this->assertSession();

    // Assert that default owner values are set when creating a new node.
    $assert_session->fieldValueEquals('oe_content_content_owner[0][target_id]', 'Directorate-General for Agriculture and Rural Development (http://publications.europa.eu/resource/authority/corporate-body/AGRI)');
    $assert_session->fieldValueEquals('oe_content_content_owner[1][target_id]', 'Directorate-General for Budget (http://publications.europa.eu/resource/authority/corporate-body/BUDG)');

    // Unset the default content owner values.
    $this->container->get('config.factory')->getEditable('oe_corporate_site_info.settings')
      ->set('content_owners', [])
      ->save();

    $this->drupalGet('/node/add/oe_page');

    // Assert that no default content owners are set when creating a new node.
    $assert_session->fieldValueEquals('oe_content_content_owner[0][target_id]', '');

    // Uninstall the "Corporate Site Information" module.
    $this->container->get('module_installer')->uninstall(['oe_corporate_site_info']);

    $this->drupalGet('/node/add/oe_page');

    // Assert that the Content Owner form fields are displayed.
    $assert_session->fieldValueEquals('oe_content_content_owner[0][target_id]', '');
  }

  /**
   * Tests the author values on new nodes.
   */
  public function testAuthorsDefaultValues() {
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
