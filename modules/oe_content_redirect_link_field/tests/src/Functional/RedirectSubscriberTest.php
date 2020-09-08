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
  }

  /**
   * Test redirect to URL from oe_redirect_link field value.
   */
  public function testRedirectEvent(): void {
    // Create 3 nodes, 2 with redirect links and 1 without.
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

    // Navigate to all the nodes in a row twice, and assert that even after a
    // redirect happened and got cached, we are redirecting correctly.
    $nodes = [
      'http://example.com' => $external_node,
      Url::fromUri('internal:/admin')->setAbsolute()->toString() => $internal_node,
    ];

    foreach ($nodes as $expected => $node) {
      $this->assertRedirect($expected, $node);
    }
    $this->drupalGet('node/' . $no_redirect_node->id());
    $this->assertSession()->addressEquals('/node/' . $no_redirect_node->id());
    foreach ($nodes as $expected => $node) {
      $this->assertRedirect($expected, $node);
    }
    $this->drupalGet('node/' . $no_redirect_node->id());
    $this->assertSession()->addressEquals('/node/' . $no_redirect_node->id());

    // Update one of the redirect links and run the assertions again.
    $external_node->set('oe_redirect_link', 'http://example.com/2');
    $external_node->save();
    $this->assertRedirect('http://example.com/2', $external_node);
    $external_node->set('oe_redirect_link', 'http://example.com/3');
    $external_node->save();
    $this->assertRedirect('http://example.com/3', $external_node);

    $this->assertRedirect(Url::fromUri('internal:/admin')->setAbsolute()->toString(), $internal_node);
    $this->drupalGet('node/' . $no_redirect_node->id());
    $this->assertSession()->addressEquals('/node/' . $no_redirect_node->id());

    // Add a translation and assert the redirect to the correct path.
    $translated_node = $external_node->addTranslation('fr', $external_node->toArray());
    $translated_node->set('oe_redirect_link', 'http://example.com/french');
    $translated_node->save();
    $this->assertRedirect('http://example.com/french', $external_node, 301, 'fr');
    $this->assertRedirect('http://example.com/3', $external_node);
    $this->assertRedirect('http://example.com/french', $external_node, 301, 'fr');
    $this->assertRedirect('http://example.com/3', $external_node);

    // Unset the redirect link from the source translation.
    $external_node->set('oe_redirect_link', NULL);
    $external_node->save();
    $this->drupalGet('/node/' . $external_node->id());
    $this->assertSession()->addressEquals('/node/' . $external_node->id());
    $this->drupalGet('/node/' . $translated_node->id());
    $this->assertSession()->addressEquals('/fr/node/' . $translated_node->id());
  }

  /**
   * Asserts the redirect of a node to a certain path.
   *
   * @param string $expected
   *   The expected redirect path.
   * @param \Drupal\node\NodeInterface $node
   *   The node being tested for redirect.
   * @param int|null $expected_status_code
   *   The expected status code.
   * @param string|null $language
   *   The expected path language.
   */
  public function assertRedirect(string $expected, NodeInterface $node, int $expected_status_code = 301, string $language = NULL): void {
    /** @var \GuzzleHttp\ClientInterface $client */
    $client = $this->getHttpClient();
    /** @var \Psr\Http\Message\ResponseInterface $response */
    $path = '';
    if ($language) {
      $path = $language . '/';
    }
    $path .= 'node/' . $node->id();
    $url = $this->getAbsoluteUrl($path);
    $response = $client->request('GET', $url, ['allow_redirects' => FALSE]);
    $this->assertEquals($expected_status_code, $response->getStatusCode());

    $ending_url = $response->getHeader('location');
    $ending_url = $ending_url ? $ending_url[0] : NULL;
    $this->assertEquals($expected, $ending_url);
  }

}
