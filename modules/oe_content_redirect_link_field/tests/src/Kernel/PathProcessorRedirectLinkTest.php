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
      'translatable' => TRUE,
      'settings' => ['link_type' => LinkItemInterface::LINK_GENERIC],
    ])->save();
  }

  /**
   * Test the outbound processing of entities with redirect link.
   */
  public function testPathOutboundRedirectLink() {
    $permissions = [
      'access content',
      'create node_with_redirect content',
    ];
    $this->setUpCurrentUser([], $permissions);

    $node = $this->createNode([
      'type' => 'node_with_redirect',
      'title' => 'Node that should be redirected',
    ]);
    $node->save();

    $target_node = $this->createNode([
      'type' => 'node_with_redirect',
      'title' => 'Just a random internal link node.',
    ]);
    $target_node->save();

    // Set an external URL.
    $node->set('oe_redirect_link', 'http://example.com');
    $node->save();
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $this->assertEquals('http://example.com', $url);
    // Assert also the URL generation via the EntityBase class.
    $this->assertEquals('http://example.com', $node->toUrl()->toString());

    // Set an external URL with a query parameter and fragment.
    $node->set('oe_redirect_link', 'http://example.com?bobo=1#frag');
    $node->save();
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $this->assertEquals('http://example.com?bobo=1#frag', $url);
    $this->assertEquals('http://example.com?bobo=1#frag', $node->toUrl()->toString());

    // Set a link to another node.
    $node->set('oe_redirect_link', 'entity:node/' . $target_node->id());
    $node->save();
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $this->assertEquals('/node/' . $target_node->id(), $url);
    $this->assertEquals('/node/' . $target_node->id(), $node->toUrl()->toString());

    // Set a link to another node with query params.
    $node->set('oe_redirect_link', 'entity:node/' . $target_node->id() . '?bobo=1');
    $node->save();
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $this->assertEquals('/node/' . $target_node->id() . '?bobo=1', $url);
    $this->assertEquals('/node/' . $target_node->id() . '?bobo=1', $node->toUrl()->toString());

    // Set an internal URL.
    $node->set('oe_redirect_link', 'internal:/admin');
    $node->save();
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $this->assertEquals('/admin', $url);
    $this->assertEquals('/admin', $node->toUrl()->toString());

    // Set an internal URL with query params.
    $node->set('oe_redirect_link', 'internal:/admin?bobo=1');
    $node->save();
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $this->assertEquals('/admin?bobo=1', $url);
    $this->assertEquals('/admin?bobo=1', $node->toUrl()->toString());

    // Set another external URL.
    $node->set('oe_redirect_link', 'http://example2.com');
    $node->save();
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $this->assertEquals('http://example2.com', $url);
    $this->assertEquals('http://example2.com', $node->toUrl()->toString());

    $permissions['bypass'] = 'bypass redirect link outbound rewriting';
    $this->setUpCurrentUser([], $permissions);
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    // Users with the permission to bypass the rewriting should not see a
    // different URL.
    $this->assertEquals('/node/' . $node->id(), $url);

    unset($permissions['bypass']);
    $this->setUpCurrentUser([], $permissions);
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $this->assertEquals('http://example2.com', $url);

    // If there is no redirect link, there should be no bypassing.
    $node->set('oe_redirect_link', '');
    $node->save();
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $this->assertEquals('/node/' . $node->id(), $url);

    // Check that language-specific conditions are respected.
    $language = \Drupal::languageManager()->getLanguage('fr');

    $translated_node = $node->addTranslation('fr', $node->toArray());
    $translated_node->save();

    // Set a redirect link on the untranslated version. At this point the
    // translation exists already and does not have a value so it should not
    // redirect.
    $node->set('oe_redirect_link', 'http://example.com');
    $node->save();

    // The source translation has the redirect link.
    $source_url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $this->assertEquals('http://example.com', $source_url);
    $this->assertEquals('http://example.com', $node->toUrl()->toString());
    // But not the translation so we default to the source translation link.
    $translation_url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['language' => $language])->toString();
    $this->assertEquals('http://example.com', $translation_url);
    $this->assertEquals('http://example.com', $translated_node->toUrl()->toString());

    // Add a redirect link to the translation.
    $translated_node->set('oe_redirect_link', 'http://example.com/fr');
    $translated_node->save();
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['language' => $language])->toString();
    $this->assertEquals('http://example.com/fr', $url);
    $this->assertEquals('http://example.com/fr', $translated_node->toUrl()->toString());
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $this->assertEquals('http://example.com', $url);
    $this->assertEquals('http://example.com', $node->toUrl()->toString());

    // Remove the redirect link from the source translation and assert that
    // the translation no longer resolves one.
    $node->set('oe_redirect_link', '');
    $node->save();
    $this->container->get('entity_type.manager')->getStorage('node')->resetCache();
    $node = $this->container->get('entity_type.manager')->getStorage('node')->load($node->id());
    $translated_node = $node->getTranslation('fr');
    $this->assertEquals('http://example.com/fr', $translated_node->get('oe_redirect_link')->uri);
    $this->assertTrue($node->get('oe_redirect_link')->isEmpty());
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['language' => $language])->toString();
    $this->assertEquals('/node/' . $translated_node->id(), $url);
    $this->assertEquals('/node/' . $translated_node->id(), $translated_node->toUrl()->toString());
  }

}
