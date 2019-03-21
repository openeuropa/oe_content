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
    'datetime',
    'field',
    'language',
    'link',
    'node',
    'oe_content',
    'rdf_draft',
    'rdf_entity',
    'rdf_entity_test',
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

    // Copied from @TokenReplaceKernelTestBase.
    $this->installConfig(['system']);
    \Drupal::service('router.builder')->rebuild();

    $this->interfaceLanguage = \Drupal::languageManager()->getCurrentLanguage();
    $this->tokenService = \Drupal::token();

    // Create french language.
    ConfigurableLanguage::createFromLangcode('fr')->save();

    $node_type = NodeType::create(['type' => 'oe_content_token_test', 'name' => 'OE Content token test']);
    $node_type->save();

  }

  /**
   * Creates a node, then tests the tokens generated from it.
   */
  public function testShortTitleTokenReplacement() {

    // Create a user and a node.
    $account = $this->createUser();
    /* @var $node \Drupal\node\NodeInterface */
    $node = Node::create([
      'type' => 'oe_content_token_test',
      'tnid' => 0,
      'uid' => $account->id(),
      'title' => 'I am a title',
      'oe_content_short_title' => 'I am a short title',
    ]);

    $node->save();

    $entity_type_manager = \Drupal::entityTypeManager()->getStorage('node');
    $entity_type_manager->resetCache();
    /** @var \Drupal\node\NodeInterface $node */
    $node = $entity_type_manager->load($node->id());

    // Generate and test tokens.
    $tests = [];
    $tests['[node:short-title-fallback]'] = $node->get('oe_content_short_title')->value ?? $node->label();

    $base_metadata = BubbleableMetadata::createFromObject($node);

    $metadata_tests = [];
    $metadata_tests['[node:short-title-fallback]'] = $base_metadata;

    // Test to make sure that we generated something for each token.
    $this->assertFalse(in_array(0, array_map('strlen', $tests)), 'No empty tokens generated.');

    foreach ($tests as $input => $expected) {
      $bubbleable_metadata = new BubbleableMetadata();
      $output = $this->tokenService->replace($input, ['node' => $node], ['langcode' => $this->interfaceLanguage->getId()], $bubbleable_metadata);
      $this->assertEqual($output, $expected, format_string('Node token %token replaced.', ['%token' => $input]));
      $this->assertEqual($bubbleable_metadata, $metadata_tests[$input]);
    }

    // Adds a french translation.
    $node->addTranslation('fr', [
      'uid' => $account->id(),
      'title' => 'Je suis un titre',
      'oe_content_short_title' => 'Je suis un petite titre',
    ]);

    $node->save();

    $tests = [];
    $tests['[node:short-title-fallback]'] = $node->getUntranslated()->get('oe_content_short_title')->value ?? $node->label();

    foreach ($tests as $input => $expected) {
      $bubbleable_metadata = new BubbleableMetadata();
      $output = $this->tokenService->replace($input, ['node' => $node], ['langcode' => $this->interfaceLanguage->getId()], $bubbleable_metadata);
      $this->assertEqual($output, $expected, format_string('Node token %token replaced.', ['%token' => $input]));
      $this->assertEqual($bubbleable_metadata, $metadata_tests[$input]);
    }

  }

}
