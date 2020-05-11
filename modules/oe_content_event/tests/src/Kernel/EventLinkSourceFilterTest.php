<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_event\Kernel;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\node\Entity\Node;
use Drupal\oe_content_event\Plugin\InternalLinkSourceFilter\EventPeriodFilter;

/**
 * Tests the internal link source filters related to Events.
 *
 * @covers \Drupal\oe_content_event\Plugin\InternalLinkSourceFilter\EventPeriodFilter
 */
class EventLinkSourceFilterTest extends EventKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_link_lists',
    'oe_link_lists_internal_source',
  ];

  /**
   * Test that the event filter works as intended.
   */
  public function testEventLinkSourceFilter(): void {

    // Create an event that ended in the far past.
    $values = [
      'type' => 'oe_event',
      'title' => 'My node title',
      'oe_event_dates' => [
        'value' => '1999-05-10T12:00:00',
        'end_value' => '1999-05-15T12:00:00',
      ],
    ];
    $ancient_event = Node::create($values);
    $ancient_event->save();

    // Create an event that ended in the near past.
    $values = [
      'type' => 'oe_event',
      'title' => 'My node title',
      'oe_event_dates' => [
        'value' => '2016-05-10T12:00:00',
        'end_value' => '2016-05-15T12:00:00',
      ],
    ];
    $past_event = Node::create($values);
    $past_event->save();

    // Create an event starts on the near future.
    $values = [
      'type' => 'oe_event',
      'title' => 'My node title',
      'oe_event_dates' => [
        'value' => '2050-05-10T12:00:00',
        'end_value' => '2050-05-15T12:00:00',
      ],
    ];
    $upcoming_event = Node::create($values);
    $upcoming_event->save();

    // Create an event starts on the far future.
    $values = [
      'type' => 'oe_event',
      'title' => 'My node title',
      'oe_event_dates' => [
        'value' => '2200-05-10T12:00:00',
        'end_value' => '2200-05-15T12:00:00',
      ],
    ];
    $future_event = Node::create($values);
    $future_event->save();

    /** @var \Drupal\oe_link_lists_internal_source\InternalLinkSourceFilterPluginManager $plugin_manager */
    $plugin_manager = $this->container->get('plugin.manager.oe_link_lists.internal_source_filter');
    /** @var \Drupal\oe_link_lists_internal_source\InternalLinkSourceFilterInterface $plugin */
    $plugin = $plugin_manager->createInstance('oe_content_event_period', []);

    // Plugin applies to events.
    $this->assertTrue($plugin->isApplicable('node', 'oe_event'));
    // Plugin does not apply to news.
    $this->assertFalse($plugin->isApplicable('node', 'oe_news'));

    $cache = new CacheableMetadata();
    /** @var \Drupal\node\NodeStorage $storage */
    $storage = $this->container->get('entity_type.manager')->getStorage('node');

    // Default filter will only return upcoming events.
    $query = $storage->getQuery();
    $plugin->apply($query, [], $cache);
    $query_results = $query->execute();
    $future_events = [
      $upcoming_event->id() => $upcoming_event->id(),
      $future_event->id() => $future_event->id(),
    ];
    $this->assertEqual($query_results, $future_events);

    // Configuring the filter for past events will only return finished events.
    $query = $storage->getQuery();
    $plugin->setConfiguration(['period' => EventPeriodFilter::PAST]);
    $plugin->apply($query, [], $cache);
    $query_results = $query->execute();
    $past_events = [
      $past_event->id() => $past_event->id(),
      $ancient_event->id() => $ancient_event->id(),
    ];
    $this->assertEqual($query_results, $past_events);
  }

}
