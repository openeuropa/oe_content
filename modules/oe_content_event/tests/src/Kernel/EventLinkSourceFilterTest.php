<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_event\Kernel;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
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
    'datetime_testing',
    'oe_time_caching',
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

    // Create an event that starts on the near future.
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

    // Create an event that starts on the far future.
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

    // Create an event that is ongoing.
    $values = [
      'type' => 'oe_event',
      'title' => 'My node title',
      'oe_event_dates' => [
        'value' => '2016-05-10T12:00:00',
        'end_value' => '2050-05-15T12:00:00',
      ],
    ];
    $ongoing_event = Node::create($values);
    $ongoing_event->save();

    /** @var \Drupal\oe_link_lists_internal_source\InternalLinkSourceFilterPluginManager $plugin_manager */
    $plugin_manager = $this->container->get('plugin.manager.oe_link_lists.internal_source_filter');
    /** @var \Drupal\oe_link_lists_internal_source\InternalLinkSourceFilterInterface $plugin */
    $plugin = $plugin_manager->createInstance('oe_content_event_period', []);

    // Plugin applies to events.
    $plugins = $plugin_manager->getApplicablePlugins('node', 'oe_event');
    $this->assertTrue(reset($plugins) instanceof EventPeriodFilter);
    // Plugin does not apply to news.
    $plugins = $plugin_manager->getApplicablePlugins('node', 'oe_news');
    $this->assertEmpty($plugins);

    // Freeze the time at a specific point.
    $static_time = new DrupalDateTime('2020-02-17 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    /** @var \Drupal\Component\Datetime\TimeInterface $datetime */
    $time = $this->container->get('datetime.time');
    $time->freezeTime();
    $time->setTime($static_time->getTimestamp());

    $cache = new CacheableMetadata();
    /** @var \Drupal\node\NodeStorage $storage */
    $storage = $this->container->get('entity_type.manager')->getStorage('node');

    // Default filter will only return upcoming events.
    $query = $storage->getQuery();
    $plugin->apply($query, [], $cache);
    $query_results = $query->execute();
    $future_events = [
      $ongoing_event->id() => $ongoing_event->id(),
      $upcoming_event->id() => $upcoming_event->id(),
      $future_event->id() => $future_event->id(),
    ];
    $this->assertEquals($future_events, $query_results);
    // Time based caches have been added and they correspond to the closest
    // upcoming event start date.
    $date_cache_tags = [
      'oe_time_caching_date:2050',
      'oe_time_caching_date:2050-05',
      'oe_time_caching_date:2050-05-15',
      'oe_time_caching_date:2050-05-15-12',
    ];
    $this->assertEquals($date_cache_tags, $cache->getCacheTags());

    // Configuring the filter for past events will only return finished events.
    $query = $storage->getQuery();
    $plugin->setConfiguration(['period' => EventPeriodFilter::PAST]);
    $cache = new CacheableMetadata();
    $plugin->apply($query, [], $cache);
    $query_results = $query->execute();
    $past_events = [
      $past_event->id() => $past_event->id(),
      $ancient_event->id() => $ancient_event->id(),
    ];
    $this->assertEquals($past_events, $query_results);
    // Time based caches have been added and they correspond to the closest
    // event end date.
    $date_cache_tags = [
      'oe_time_caching_date:2050',
      'oe_time_caching_date:2050-05',
      'oe_time_caching_date:2050-05-15',
      'oe_time_caching_date:2050-05-15-12',
    ];
    $this->assertEquals($date_cache_tags, $cache->getCacheTags());

    // If we move back in time, the query updates its results.
    $static_time = new DrupalDateTime('2015-02-17 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $time->setTime($static_time->getTimestamp());
    $cache = new CacheableMetadata();
    $query = $storage->getQuery();
    $plugin->apply($query, [], $cache);
    $query_results = $query->execute();
    $past_events = [
      $ancient_event->id() => $ancient_event->id(),
    ];
    $this->assertEquals($past_events, $query_results);
    // Time based caches have been added and they correspond to the closest
    // event end date.
    $date_cache_tags = [
      'oe_time_caching_date:2016',
      'oe_time_caching_date:2016-05',
      'oe_time_caching_date:2016-05-15',
      'oe_time_caching_date:2016-05-15-12',
    ];
    $this->assertEquals($date_cache_tags, $cache->getCacheTags());

    // Set the time to the end date of an ongoing event.
    $static_time = new DrupalDateTime('2050-05-15 12:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $time->setTime($static_time->getTimestamp());
    $query = $storage->getQuery();
    // Configuring the filter for upcoming events.
    $plugin->setConfiguration(['period' => EventPeriodFilter::UPCOMING]);
    $cache = new CacheableMetadata();
    $plugin->apply($query, [], $cache);
    $query_results = $query->execute();
    $ongoing_events = [
      // Only future event is visible because the time is set to the
      // end date of the ongoing and upcoming event.
      $future_event->id() => $future_event->id(),
    ];
    $this->assertEquals($ongoing_events, $query_results);
    // Time based caches have been added and they correspond to the closest
    // event end date.
    $date_cache_tags = [
      'oe_time_caching_date:2200',
      'oe_time_caching_date:2200-05',
      'oe_time_caching_date:2200-05-15',
      'oe_time_caching_date:2200-05-15-12',
    ];
    $this->assertEquals($date_cache_tags, $cache->getCacheTags());

    // Configuring the filter to show all events shows all events.
    $query = $storage->getQuery();
    $plugin->setConfiguration(['period' => '']);
    $cache = new CacheableMetadata();
    $plugin->apply($query, [], $cache);
    $query_results = $query->execute();
    $all_events = [
      $future_event->id() => $future_event->id(),
      $upcoming_event->id() => $upcoming_event->id(),
      $ongoing_event->id() => $ongoing_event->id(),
      $past_event->id() => $past_event->id(),
      $ancient_event->id() => $ancient_event->id(),
    ];
    $this->assertEquals($all_events, $query_results);
    // Because we are showing all events time based cache tags are not
    // necessary.
    $this->assertEquals([], $cache->getCacheTags());
  }

}
