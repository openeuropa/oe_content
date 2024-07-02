<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Kernel;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\sparql_entity_storage\Kernel\SparqlKernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Tests the generation of the short title token.
 *
 * @group node
 */
class ShortTitleTokenReplaceTest extends SparqlKernelTestBase {

  use UserCreationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'content_translation',
    'field',
    'language',
    'link',
    'node',
    'oe_content',
    'rdf_skos',
    'system',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->setUpSparql();

    $this->installSchema('system', 'sequences');
    $this->installSchema('user', 'users_data');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', 'node_access');
    $this->installConfig(['field', 'filter', 'node', 'system']);
    module_load_include('install', 'oe_content');
    oe_content_install(FALSE);

    ConfigurableLanguage::createFromLangcode('fr')->save();

    // Create a node type for the test.
    $node_type = NodeType::create([
      'type' => 'oe_content_token_test',
      'name' => 'OE Content token test',
    ]);
    $node_type->save();
  }

  /**
   * Creates a node, then tests the tokens generated from it.
   */
  public function testShortTitleTokenReplacement() {
    $node_storage = $this->container->get('entity_type.manager')->getStorage('node');
    $current_language = $this->container->get('language_manager')->getCurrentLanguage();

    // Create a user and a node with short title.
    $account = $this->createUser();
    /** @var \Drupal\node\NodeInterface $node */
    $node_short_titled = $node_storage->create([
      'type' => 'oe_content_token_test',
      'tnid' => 0,
      'uid' => $account->id(),
      'title' => 'I am a title',
      'oe_content_short_title' => 'I am a short title',
    ]);
    $node_short_titled->save();

    // Add a French translation.
    $node_short_titled->addTranslation('fr', [
      'uid' => $account->id(),
      'title' => 'Je suis un titre',
      'oe_content_short_title' => 'Je suis un petit titre',
    ]);
    $node_short_titled->save();

    // Creates a node without short title.
    $node_titled = $node_storage->create([
      'type' => 'oe_content_token_test',
      'tnid' => 0,
      'uid' => $account->id(),
      'title' => 'I am a title',
    ]);
    $node_titled->save();

    // Add a French translation.
    $node_titled->addTranslation('fr', [
      'uid' => $account->id(),
      'title' => 'Je suis un titre',
    ]);
    $node_titled->save();

    $node_storage->resetCache();

    /** @var \Drupal\node\NodeInterface $node */
    $node_short_titled = $node_storage->load($node_short_titled->id());
    /** @var \Drupal\node\NodeInterface $node */
    $node_titled = $node_storage->load($node_titled->id());

    $tests = [];
    $tests['short title available in original language'] = [
      'node' => $node_short_titled,
      'langcode' => $current_language->getId(),
      'expected' => $node_short_titled->get('oe_content_short_title')->value,
    ];
    $tests['short title not available in original language'] = [
      'node' => $node_titled,
      'langcode' => $current_language->getId(),
      'expected' => $node_titled->label(),
    ];
    $tests['short title available in French translation'] = [
      'node' => $node_short_titled,
      'langcode' => 'fr',
      'expected' => $node_short_titled->getTranslation('fr')->get('oe_content_short_title')->value,
    ];
    $tests['short title not available in French translation'] = [
      'node' => $node_titled,
      'langcode' => 'fr',
      'expected' => $node_titled->getTranslation('fr')->label(),
    ];

    $token_service = $this->container->get('token');
    foreach ($tests as $scenario => $test) {
      // Load the translation of the node to simulate a token replacement using
      // a node in a certain language. This ensures that we are always using
      // the source language when generating the token.
      $node = $test['node']->getTranslation($test['langcode']);
      $output = $token_service->replace('[node:short-title-fallback]', ['node' => $node], ['langcode' => $test['langcode']]);
      $this->assertEquals($output, $test['expected'], sprintf('Token was not correctly replaced for test case "%s".', $scenario));
    }
  }

}
