<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_persistent\Kernel;

use Drupal\filter\FilterPluginCollection;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;

/**
 * Tests Persistent url related controller and service.
 *
 * @group path
 */
class PersistentUrlFilterTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'filter',
    'field',
    'text',
    'path',
    'node',
    'user',
    'language',
    'content_translation',
    'oe_content_persistent',
    'path_alias',
  ];

  /**
   * All available filters.
   *
   * @var \Drupal\filter\Plugin\FilterInterface[]
   */
  protected $filters;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('configurable_language');
    $this->installEntitySchema('path_alias');
    $this->installConfig([
      'oe_content_persistent',
      'filter',
      'node',
      'system',
      'language',
      'user',
    ]);
    $this->installSchema('node', ['node_access']);

    ConfigurableLanguage::create(['id' => 'fr'])->save();

    $manager = $this->container->get('plugin.manager.filter');
    $bag = new FilterPluginCollection($manager, []);
    $this->filters = $bag->getAll();

    /** @var \Drupal\Core\PathProcessor\PathProcessorManager $path_processor_manager */
    $path_processor_manager = $this->container->get('path_processor_manager');
    /** @var \Drupal\Core\PathProcessor\PathProcessorAlias $path_processor */
    $path_processor = $this->container->get('path_processor_alias');
    $path_processor_manager->addOutbound($path_processor);
  }

  /**
   * Test return of ContentUuidResolver service.
   */
  public function testPersistentUrlFilter(): void {
    /** @var \Drupal\oe_content_persistent\ContentUuidResolver $uuid_resolver */
    $uuid_resolver = $this->container->get('oe_content_persistent.resolver');

    $filter = $this->filters['filter_purl'];
    $base_url = $this->config('oe_content_persistent.settings')->get('base_url');
    $test = function ($input, $langcode = 'en') use ($filter) {
      return $filter->process($input, $langcode);
    };

    /** @var \Drupal\node\NodeInterface $node */
    $node = Node::create([
      'title' => $this->randomString(),
      'type' => 'page',
    ]);
    $node->save();

    $input = '<a href="' . $base_url . $node->uuid() . '">test</a>';
    $expected = '<a href="/node/' . $node->id() . '">test</a>';
    $output = $test($input, 'en');
    $this->assertSame($expected, $output->getProcessedText());

    $uuid_resolver->resetStaticCache();
    $node->path->alias = '/alias1';
    $node->save();

    $expected = '<a href="/alias1">test</a>';
    $output = $test($input, 'en');
    $this->assertSame($expected, $output->getProcessedText());

    $uuid_resolver->resetStaticCache();
    $node->path->alias = '/alias2';
    $node->save();

    $expected = '<a href="/alias2">test</a>';
    $output = $test($input, 'en');
    $this->assertSame($expected, $output->getProcessedText());

    $translation = $node->addTranslation('fr', $node->toArray());
    $translation->path->alias = '/alias_fr';
    $translation->save();

    $uuid_resolver->resetStaticCache();
    $expected = '<a href="/alias_fr">test</a>';
    $output = $test($input, 'fr');
    $this->assertSame($expected, $output->getProcessedText());

    $uuid_resolver->resetStaticCache();
    $expected = '<a href="/alias2">test</a>';
    $output = $test($input, 'en');
    $this->assertSame($expected, $output->getProcessedText());

    $uuid_service = $this->container->get('uuid');
    $uuid = $uuid_service->generate();

    $input = '<a href="' . $base_url . $uuid . '">test</a>';
    $expected = '<a href="/system/404">test</a>';
    $output = $test($input, 'fr');
    $this->assertSame($expected, $output->getProcessedText());

    $system_config = $this->container->get('config.factory')->getEditable('system.site');
    $system_config->set('page.404', '/custom/404');
    $system_config->save();

    $expected = '<a href="/custom/404">test</a>';
    $output = $test($input, 'fr');
    $this->assertSame($expected, $output->getProcessedText());

    // Make sure that we don't loose query parameters and fragments.
    $input = '<a href="' . $base_url . $node->uuid() . '?param1=value1&param2=value2#hashexampl">test</a>';
    $expected = '<a href="/alias2?param1=value1&amp;param2=value2#hashexampl">test</a>';
    $output = $test($input, 'en');
    $this->assertSame($expected, $output->getProcessedText());

    // Make sure that we don't loose query parameters.
    $input = '<a href="' . $base_url . $node->uuid() . '?param1=value1&param2=value2">test</a>';
    $expected = '<a href="/alias2?param1=value1&amp;param2=value2">test</a>';
    $output = $test($input, 'en');
    $this->assertSame($expected, $output->getProcessedText());

    // Make sure that we don't loose fragments.
    $input = '<a href="' . $base_url . $node->uuid() . '#hashexampl">test</a>';
    $expected = '<a href="/alias2#hashexampl">test</a>';
    $output = $test($input, 'en');
    $this->assertSame($expected, $output->getProcessedText());
  }

}
