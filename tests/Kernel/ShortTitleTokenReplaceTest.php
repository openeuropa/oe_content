<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Kernel;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;
use Drupal\Tests\rdf_entity\Traits\RdfDatabaseConnectionTrait;

/**
 * Generates short title token.
 *
 * @group node
 */
class ShortTitleTokenReplaceTest extends RdfKernelTestBase {

  use RdfDatabaseConnectionTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'content_translation',
    'field',
    'language',
    'link',
    'node',
    'oe_content',
    'rdf_skos',
    'system',
    'text',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {

    parent::setUp();
    $this->setUpSparql();

    $this->installSchema('user', 'users_data');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', 'node_access');
    $this->installConfig(['field', 'filter', 'node']);
    module_load_include('install', 'oe_content');
    oe_content_install();

    // Copied from \Drupal\Tests\system\Kernel\Token\TokenReplaceKernelTestBase.
    $this->installConfig(['system']);
    \Drupal::service('router.builder')->rebuild();
    $this->interfaceLanguage = \Drupal::languageManager()->getCurrentLanguage();
    $this->tokenService = \Drupal::token();
    $this->entityTypeManager = \Drupal::entityTypeManager()->getStorage('node');
    // Create french language.
    ConfigurableLanguage::createFromLangcode('fr')->save();

    // Create node type for the test.
    $node_type = NodeType::create(['type' => 'oe_content_token_test', 'name' => 'OE Content token test']);
    $node_type->save();

  }

  /**
   * Creates a node, then tests the tokens generated from it.
   */
  public function testShortTitleTokenReplacement() {

    // Create a user and a node with short title.
    $account = $this->createUser();
    /* @var $node \Drupal\node\NodeInterface */
    $node_short_titled = Node::create([
      'type' => 'oe_content_token_test',
      'tnid' => 0,
      'uid' => $account->id(),
      'title' => 'I am a title',
      'oe_content_short_title' => 'I am a short title',
    ]);
    $node_short_titled->save();

    // Creates a node without short title.
    $node_titled = Node::create([
      'type' => 'oe_content_token_test',
      'tnid' => 0,
      'uid' => $account->id(),
      'title' => 'I am a title',
      'oe_content_short_title' => '',
    ]);
    $node_titled->save();

    $this->entityTypeManager->resetCache();
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->entityTypeManager->load($node_short_titled->id());

    // Generate and test tokens.
    $tests = [];
    $tests['[node:short-title-fallback]'] = $node_short_titled->get('oe_content_short_title')->value;
    $base_metadata = BubbleableMetadata::createFromObject($node_short_titled);
    $bubbleable_metadata = new BubbleableMetadata();
    $metadata_tests = [];
    $metadata_tests['[node:short-title-fallback]'] = $base_metadata;

    // Test to make sure that we generated something for each token.
    $this->assertFalse(in_array(0, array_map('strlen', $tests)), 'No empty tokens generated.');

    foreach ($tests as $input => $expected) {

      $output = $this->tokenService->replace($input, ['node' => $node_short_titled], ['langcode' => $this->interfaceLanguage->getId()], $bubbleable_metadata);
      $this->assertEqual($output, $expected, format_string('Node token %token replaced.', ['%token' => $input]));
      $this->assertEqual($bubbleable_metadata, $metadata_tests[$input]);
    }

    // Adds a french translation and tests if value from source translation is
    // still used.
    $node_short_titled->addTranslation('fr', [
      'uid' => $account->id(),
      'title' => 'Je suis un titre',
      'oe_content_short_title' => 'Je suis un petit titre',
    ]);

    $node_short_titled->save();

    $tests['[node:short-title-fallback]'] = $node_short_titled->getUntranslated()->get('oe_content_short_title')->value;
    foreach ($tests as $input => $expected) {
      $output = $this->tokenService->replace($input, ['node' => $node_short_titled], ['langcode' => $this->interfaceLanguage->getId()], $bubbleable_metadata);
      $this->assertEqual($output, $expected, format_string('Node token %token replaced.', ['%token' => $input]));
      $this->assertEqual($bubbleable_metadata, $metadata_tests[$input]);
    }

    // Tests the token with a node without short title field value.
    $tests['[node:short-title-fallback]'] = $node_titled->getUntranslated()->getTitle();
    $bubbleable_metadata = new BubbleableMetadata();
    $base_metadata = BubbleableMetadata::createFromObject($node_titled);
    $metadata_tests['[node:short-title-fallback]'] = $base_metadata;
    foreach ($tests as $input => $expected) {
      $output = $this->tokenService->replace($input, ['node' => $node_titled], ['langcode' => $this->interfaceLanguage->getId()], $bubbleable_metadata);
      $this->assertEqual($output, $expected, format_string('Node token %token replaced.', ['%token' => $input]));
      $this->assertEqual($bubbleable_metadata, $metadata_tests[$input]);
    }

  }

}
