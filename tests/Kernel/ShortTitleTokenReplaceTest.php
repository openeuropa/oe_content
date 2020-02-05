<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Kernel;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;
use Drupal\Tests\rdf_entity\Traits\RdfDatabaseConnectionTrait;

/**
 * Tests the generation of the short title token.
 *
 * @group node
 */
class ShortTitleTokenReplaceTest extends RdfKernelTestBase {

  use RdfDatabaseConnectionTrait;

  /**
   * The current language.
   *
   * @var \Drupal\Core\Language\Language
   */
  protected $currentLanguage;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $tokenService;

  /**
   * The Node storage handler.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * The token we are testing.
   *
   * @var string
   */
  protected $token = '[node:short-title-fallback]';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'content_translation',
    'field',
    'language',
    'node',
    'oe_content',
    'rdf_skos',
    'system',
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
    $this->installConfig(['field', 'filter', 'node', 'system']);
    module_load_include('install', 'oe_content');
    oe_content_install();

    $this->currentLanguage = \Drupal::languageManager()->getCurrentLanguage();
    $this->tokenService = \Drupal::token();
    $this->nodeStorage = \Drupal::entityTypeManager()->getStorage('node');

    ConfigurableLanguage::createFromLangcode('fr')->save();

    // Create a node type for the test.
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

    // Add a French translation.
    $node_short_titled->addTranslation('fr', [
      'uid' => $account->id(),
      'title' => 'Je suis un titre',
      'oe_content_short_title' => 'Je suis un petit titre',
    ]);
    $node_short_titled->save();

    // Creates a node without short title.
    $node_titled = Node::create([
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

    $this->nodeStorage->resetCache();

    /** @var \Drupal\node\NodeInterface $node */
    $node_short_titled = $this->nodeStorage->load($node_short_titled->id());
    /** @var \Drupal\node\NodeInterface $node */
    $node_titled = $this->nodeStorage->load($node_titled->id());

    // Generate and test tokens.
    $tests = [];
    // Test the original language of the node that has defined short title.
    $tests[] = [
      'node' => $node_short_titled,
      'expected' => $node_short_titled->get('oe_content_short_title')->value,
      'langcode' => $this->currentLanguage->getId(),
    ];
    // Test the original language of the node that has no short title defined.
    $tests[] = [
      'node' => $node_titled,
      'expected' => $node_titled->label(),
      'langcode' => $this->currentLanguage->getId(),
    ];
    // Test the french translation of the node that has defined short title.
    $tests[] = [
      'node' => $node_short_titled,
      'expected' => $node_short_titled->getTranslation('fr')->get('oe_content_short_title')->value,
      // The source short title should be used even in another language.
      'langcode' => 'fr',
    ];
    // Test the french translation of the node that has no short title defined.
    $tests[] = [
      'node' => $node_titled,
      'expected' => $node_titled->getTranslation('fr')->label(),
      // The source title should be used even in another language.
      'langcode' => 'fr',
    ];

    // Test to make sure that we generated something for each token.
    foreach ($tests as $test) {
      // Load the translation of the node to simulate a token replacement using
      // a node in a certain language. This ensures that we are always using
      // the source language when generating the token.
      $node = $test['node']->getTranslation($test['langcode']);
      $output = $this->tokenService->replace($this->token, ['node' => $node], ['langcode' => $test['langcode']]);
      $this->assertEquals($output, $test['expected'], sprintf('Token %s was not correctly replaced.', $this->token));
    }
  }

}
