<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_redirect_link_field\Functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\link\LinkItemInterface;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Test event subscriber for handling redirects to URL from redirect link field.
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
  }

  /**
   * Test redirect to URL from oe_redirect_link field value.
   */
  public function testRedirectEvent(): void {
    FieldConfig::create([
      'entity_type' => 'node',
      'field_name' => 'oe_redirect_link',
      'bundle' => $this->nodeType->id(),
      'settings' => [
        'link_type' => LinkItemInterface::LINK_EXTERNAL,
        // Enable the title field so we can test it doesn't show up.
        'title' => 1,
      ],
    ])->save();
    $node = $this->drupalCreateNode([
      'type' => 'test_with_redirect_link',
      'oe_redirect_link' => 'http://example.com',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $node->save();

    $this->drupalLogin($this->nodeUser);
    $this->drupalGet('node/' . $node->id());
    // No redirect because the user has
    // 'bypass redirect link outbound rewriting' permission.
    $this->assertSession()->addressEquals('/node/' . $node->id());
    $this->drupalLogout();
    // Redirect to URL from redirect link field.
    $this->assertRedirect('node/' . $node->id(), 'http://example.com');

    $node->set('oe_redirect_link', 'http://example.com/2');
    $node->save();
    // The updated value of the redirect link field.
    $this->assertRedirect('node/' . $node->id(), 'http://example.com/2');

    $translated_node = $node->addTranslation('fr', $node->toArray());
    $translated_node->set('oe_redirect_link', 'http://example.com/fr');
    $translated_node->save();
    // Use the translated value of redirect link field.
    $this->assertRedirect('fr/node/' . $node->id(), 'http://example.com/fr');

    $node = $this->drupalCreateNode([
      'type' => 'test_with_redirect_link',
      'oe_redirect_link' => 'http://example.com',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $node->save();

    $translated_node = $node->addTranslation('fr', $node->toArray());
    $translated_node->set('oe_redirect_link', 'http://example.com/fr');
    $translated_node->save();
    $node->set('oe_redirect_link', '');
    $node->save();
    // We do not have redirect because redirect link value for the source
    // language is empty.
    $this->assertRedirect('/node/' . $node->id(), NULL, 200);
  }

  /**
   * Asserts the redirect from $path to the $expected_ending_url.
   *
   * @param string $path
   *   The request path.
   * @param string|null $expected_ending_url
   *   The path where we expect it to redirect.
   * @param int|null $expected_status_code
   *   The expected status code.
   */
  public function assertRedirect(string $path, $expected_ending_url, int $expected_status_code = 301): void {
    /** @var \GuzzleHttp\ClientInterface $client */
    $client = $this->getHttpClient();
    /** @var \Psr\Http\Message\ResponseInterface $response */
    $url = $this->getAbsoluteUrl($path);
    $response = $client->request('GET', $url, ['allow_redirects' => FALSE]);
    $this->assertEquals($expected_status_code, $response->getStatusCode());

    $ending_url = $response->getHeader('location');
    $ending_url = $ending_url ? $ending_url[0] : NULL;
    $this->assertEquals($expected_ending_url, $ending_url);
  }

}
