<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_redirect_link_field\Functional;

use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\link\LinkItemInterface;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * Testing redirects to redirect link field value.
 */
class RedirectSubscriberTest extends BrowserTestBase {

  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stable';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_content_redirect_link_field',
    'language',
    'content_translation',
    'dynamic_page_cache',
    'page_cache',
    'path',
    'path_alias',
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
      'access content',
      'bypass redirect link outbound rewriting',
    ]);

    $language = ConfigurableLanguage::createFromLangcode('fr');
    $language->save();

    FieldConfig::create([
      'entity_type' => 'node',
      'field_name' => 'oe_redirect_link',
      'bundle' => $this->nodeType->id(),
      'translatable' => TRUE,
      'settings' => [
        'link_type' => LinkItemInterface::LINK_EXTERNAL,
        // Enable the title field so we can test it doesn't show up.
        'title' => 1,
      ],
    ])->save();

    $this->setContainerParameter('http.response.debug_cacheability_headers', TRUE);
    $this->rebuildContainer();
    $this->resetAll();
  }

  /**
   * Test redirect to URL from oe_redirect_link field value.
   */
  public function testRedirectEvent(): void {
    // Create 4 nodes, 3 with redirect links and 1 without.
    $external_node = $this->drupalCreateNode([
      'type' => 'test_with_redirect_link',
      'oe_redirect_link' => 'http://example.com',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $external_node->save();

    $internal_node = $this->drupalCreateNode([
      'type' => 'test_with_redirect_link',
      'oe_redirect_link' => 'internal:/admin',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $internal_node->save();

    $no_redirect_node = $this->drupalCreateNode([
      'type' => 'test_with_redirect_link',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $no_redirect_node->save();

    // Assert that a user with the bypass permission doesn't get redirected.
    $this->drupalLogin($this->nodeUser);
    $this->drupalGet('node/' . $external_node->id());
    $this->assertSession()->addressEquals('/node/' . $external_node->id());
    $this->drupalLogout();

    // Navigate to all the nodes in a row and assert that we are redirecting
    // correctly.
    $nodes = [
      'http://example.com' => $external_node,
      Url::fromUri('internal:/admin')->setAbsolute()->toString() => $internal_node,
    ];

    foreach ($nodes as $expected => $node) {
      $this->assertRedirect($expected, $node, 'MISS');
    }
    $this->drupalGet('node/' . $no_redirect_node->id());
    $this->assertSession()->addressEquals('/node/' . $no_redirect_node->id());

    // Run through these again to assert that the redirect was cached.
    $nodes = [
      'http://example.com' => $external_node,
      Url::fromUri('internal:/admin')->setAbsolute()->toString() => $internal_node,
    ];

    foreach ($nodes as $expected => $node) {
      $this->assertRedirect($expected, $node, 'HIT');
    }

    // Update one of the redirect links and run the assertions again.
    $external_node->set('oe_redirect_link', 'http://example.com/2');
    $external_node->save();
    $this->assertRedirect('http://example.com/2', $external_node, 'MISS');
    $external_node->set('oe_redirect_link', 'http://example.com/3');
    $external_node->save();
    $this->assertRedirect('http://example.com/3', $external_node, 'MISS');

    $this->assertRedirect(Url::fromUri('internal:/admin')->setAbsolute()->toString(), $internal_node, 'HIT');
    $this->drupalGet('node/' . $no_redirect_node->id());
    $this->assertSession()->addressEquals('/node/' . $no_redirect_node->id());

    // Add a translation and assert the redirect to the correct path.
    $translated_node = $external_node->addTranslation('fr', $external_node->toArray());
    $translated_node->set('oe_redirect_link', 'http://example.com/french');
    $translated_node->save();
    $this->assertRedirect('http://example.com/french', $external_node, 'MISS', 301, 'fr');
    $this->assertRedirect('http://example.com/3', $external_node, 'MISS');

    // Make another request to check the response was cached.
    $this->assertRedirect('http://example.com/french', $external_node, 'HIT', 301, 'fr');
    $this->assertRedirect('http://example.com/3', $external_node, 'HIT');

    // Unset the redirect link from the source translation.
    $external_node->set('oe_redirect_link', NULL);
    $external_node->save();
    $this->drupalGet('/node/' . $external_node->id());
    $this->assertSession()->addressEquals('/node/' . $external_node->id());
    $this->drupalGet('/fr/node/' . $translated_node->id());
    $this->assertSession()->addressEquals('/fr/node/' . $translated_node->id());

    // Create a node that redirects to another node whose alias changes and
    // assert the redirect is cached correctly.
    $target_node = $this->drupalCreateNode([
      'type' => 'test_with_redirect_link',
      'path' => ['alias' => '/target-node'],
      'status' => NodeInterface::PUBLISHED,
    ]);

    $target_node->save();

    $redirect_node = $this->drupalCreateNode([
      'type' => 'test_with_redirect_link',
      'oe_redirect_link' => 'entity:node/' . $target_node->id(),
      'status' => NodeInterface::PUBLISHED,
    ]);
    $redirect_node->save();
    $this->assertRedirect(Url::fromUserInput('/target-node')->setAbsolute()->toString(), $redirect_node, 'MISS');
    $response = $this->assertRedirect(Url::fromUserInput('/target-node')->setAbsolute()->toString(), $redirect_node, 'HIT');
    $this->assertContains('node:' . $target_node->id(), explode(' ', $response->getHeaders()['X-Drupal-Cache-Tags'][0]));

    $target_node->set('path', ['alias' => '/new-path']);
    $target_node->save();
    $this->assertRedirect(Url::fromUserInput('/new-path')->setAbsolute()->toString(), $redirect_node, 'MISS');
  }

  /**
   * Asserts the redirect of a node to a certain path.
   *
   * @param string $expected
   *   The expected redirect path.
   * @param \Drupal\node\NodeInterface $node
   *   The node being tested for redirect.
   * @param string $cache
   *   Whether the Drupal cache was HIT or MISS-ed.
   * @param int|null $expected_status_code
   *   The expected status code.
   * @param string|null $language
   *   The expected path language.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   */
  public function assertRedirect(string $expected, NodeInterface $node, string $cache, int $expected_status_code = 301, string $language = NULL): ResponseInterface {
    /** @var \GuzzleHttp\ClientInterface $client */
    $client = $this->getHttpClient();
    /** @var \Psr\Http\Message\ResponseInterface $response */
    $path = '';
    if ($language) {
      $path = $language . '/';
    }
    $path .= 'node/' . $node->id();
    $url = $this->getAbsoluteUrl($path);
    $options = [
      'allow_redirects' => FALSE,
    ];

    $response = $client->request('GET', $url, $options);
    $this->assertEquals($expected_status_code, $response->getStatusCode());

    $ending_url = $response->getHeader('location');
    $ending_url = $ending_url ? $ending_url[0] : NULL;
    $this->assertEquals($expected, $ending_url);
    $headers = $response->getHeaders();
    $cache_tags = explode(' ', $headers['X-Drupal-Cache-Tags'][0]);
    $cache_contexts = explode(' ', $headers['X-Drupal-Cache-Contexts'][0]);
    $this->assertContains('node:' . $node->id(), $cache_tags);
    $this->assertContains('user.permissions', $cache_contexts);
    $this->refreshVariables();

    return $response;
  }

}
