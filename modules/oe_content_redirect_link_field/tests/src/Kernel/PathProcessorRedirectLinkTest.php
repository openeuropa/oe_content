<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_redirect_link_field\Kernel;

use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\link\LinkItemInterface;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\Role;

/**
 * Test outbound path processor for redirect links.
 */
class PathProcessorRedirectLinkTest extends KernelTestBase {

  use NodeCreationTrait;
  use ContentTypeCreationTrait;
  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'node',
    'field',
    'text',
    'link',
    'filter',
    'language',
    'content_translation',
    'oe_content_redirect_link_field',
  ];

  /**
   * The node type.
   *
   * @var array|\Drupal\node\Entity\NodeType|mixed|null
   */
  protected $nodeType;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installSchema('system', 'sequences');
    $this->installSchema('node', 'node_access');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('configurable_language');
    $this->installConfig([
      'node',
      'oe_content_redirect_link_field',
      'filter',
      'language',
    ]);
    ConfigurableLanguage::create(['id' => 'fr'])->save();

    $this->nodeType = $this->createContentType([
      'type' => 'node_with_redirect',
    ]);
    $this->nodeType->save();

    FieldConfig::create([
      'entity_type' => 'node',
      'field_name' => 'oe_redirect_link',
      'bundle' => 'node_with_redirect',
      'settings' => ['link_type' => LinkItemInterface::LINK_GENERIC],
    ])->save();

  }

  /**
   * Test callback.
   */
  public function testPathOutboundRedirectLink() {
    $user = $this->createUser([
      'access content',
      'create node_with_redirect content',
    ], NULL, FALSE, ['uid' => 2]);

    $this->container->get('current_user')->setAccount($user);

    $node = $this->createNode([
      'type' => 'node_with_redirect',
      'oe_redirect_link' => 'http://example.com',
    ]);
    $node->save();

    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $this->assertEquals('http://example.com', $url);

    $node->set('oe_redirect_link', 'http://example2.com');
    $node->save();

    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $this->assertEquals('http://example2.com', $url);

    Role::load($user->getRoles()[1])->grantPermission('bypass redirect link outbound rewriting');
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $this->assertEquals($url, $url);

    Role::load($user->getRoles()[1])->revokePermission('bypass redirect link outbound rewriting');
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $this->assertEquals('http://example2.com', $url);

    $node->set('oe_redirect_link', '');
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $this->assertEquals($url, $url);

    $language = \Drupal::languageManager()->getLanguage('fr');

    $translated_node = $node->addTranslation('fr', $node->toArray());
    $translated_node->save();
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['language' => $language])->toString();
    $this->assertEquals($url, $url);

    $node->set('oe_redirect_link', 'http://example.com');
    $node->save();
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['language' => $language])->toString();
    $this->assertEquals('http://example.com', $url);

    $translated_node->set('oe_redirect_link', 'http://example.com/fr');
    $translated_node->save();
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['language' => $language])->toString();
    $this->assertEquals('http://example.com/fr', $url);
  }

}
