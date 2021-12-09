<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_event\Kernel;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\oe_content\EntityWrapperInterface;
use Drupal\oe_content_event\EventNodeWrapper;

/**
 * Tests event wrapper class.
 */
class EventNodeWrapperTest extends EventKernelTestBase {

  /**
   * Test exception thrown if an unsupported entity type is passed.
   */
  public function testUnsupportedEntityType() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("The current wrapper only accepts 'node' entities.");
    $entity = EntityTest::create();
    EventNodeWrapper::getInstance($entity);
  }

  /**
   * Test exception thrown if an unsupported entity bundle is passed.
   */
  public function testUnsupportedEntityBundle() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("The current wrapper only accepts 'node' entities of type 'oe_event'.");
    $node_type = NodeType::create([
      'type' => 'test',
      'name' => 'Test bundle',
    ]);
    $node_type->save();
    $entity = Node::create([
      'type' => 'test',
    ]);
    EventNodeWrapper::getInstance($entity);
  }

  /**
   * Test getter methods.
   */
  public function testGetters(): void {
    foreach ($this->getterAssertions() as $assertion) {
      $this->assertGetters($assertion['case'], $assertion['values'], $assertion['assertions']);
    }
  }

  /**
   * Test registration status.
   */
  public function testRegistrationStatus(): void {
    foreach ($this->registrationStatusAssertions() as $assertion) {
      $this->assertRegistrationStatus($assertion['case'], $assertion['values'], $assertion['now'], $assertion['assertions']);
    }
  }

  /**
   * Test online status.
   */
  public function testOnlineStatus(): void {
    foreach ($this->onlineStatusAssertions() as $assertion) {
      $this->assertOnlineStatus($assertion['case'], $assertion['values'], $assertion['now'], $assertion['assertions']);
    }
  }

  /**
   * Test event ending.
   */
  public function testEventIsOver(): void {
    $wrapper = $this->createWrapper([
      'oe_event_dates' => [
        'value' => '2016-05-10T12:00:00',
        'end_value' => '2016-05-15T12:00:00',
      ],
    ]);

    // Event is not over.
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-09 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(FALSE, $wrapper->isOver($now));

    // Event is over.
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-30 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(TRUE, $wrapper->isOver($now));
  }

  /**
   * Test ongoing event.
   */
  public function testEventIsOngoing(): void {
    $wrapper = $this->createWrapper([
      'oe_event_dates' => [
        'value' => '2016-05-10T12:00:00',
        'end_value' => '2016-05-15T12:00:00',
      ],
    ]);

    // Event is ongoing.
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-15 11:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(TRUE, $wrapper->isOngoing($now));

    // Event is not ongoing.
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-30 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(FALSE, $wrapper->isOngoing($now));
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-09 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(FALSE, $wrapper->isOngoing($now));
  }

  /**
   * Test online is over.
   */
  public function testOnlineIsOver(): void {
    $wrapper = $this->createWrapper([
      'oe_event_online_dates' => [
        'value' => '2016-05-10T12:00:00',
        'end_value' => '2016-05-15T12:00:00',
      ],
    ]);

    // Online is not over.
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-09 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(FALSE, $wrapper->isOnlinePeriodOver($now));

    // Online is over.
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-30 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(TRUE, $wrapper->isOnlinePeriodOver($now));
  }

  /**
   * Test registration period methods.
   */
  public function testRegistrationPeriodMethods(): void {
    $wrapper = $this->createWrapper([
      'oe_event_registration_dates' => [
        'value' => '2016-05-10T12:00:00',
        'end_value' => '2016-05-15T12:00:00',
      ],
    ]);

    // Registration yet to come.
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-09 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(TRUE, $wrapper->isRegistrationPeriodYetToCome($now));
    $this->assertEquals(FALSE, $wrapper->isRegistrationPeriodActive($now));
    $this->assertEquals(FALSE, $wrapper->isRegistrationPeriodOver($now));

    // Registration just started.
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-10 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(FALSE, $wrapper->isRegistrationPeriodYetToCome($now));
    $this->assertEquals(TRUE, $wrapper->isRegistrationPeriodActive($now));
    $this->assertEquals(FALSE, $wrapper->isRegistrationPeriodOver($now));

    // Registration in progress.
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-12 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(FALSE, $wrapper->isRegistrationPeriodYetToCome($now));
    $this->assertEquals(TRUE, $wrapper->isRegistrationPeriodActive($now));
    $this->assertEquals(FALSE, $wrapper->isRegistrationPeriodOver($now));

    // Registration just ended.
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-15 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(FALSE, $wrapper->isRegistrationPeriodYetToCome($now));
    $this->assertEquals(FALSE, $wrapper->isRegistrationPeriodActive($now));
    $this->assertEquals(TRUE, $wrapper->isRegistrationPeriodOver($now));

    // Registration is over.
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-20 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(FALSE, $wrapper->isRegistrationPeriodYetToCome($now));
    $this->assertEquals(FALSE, $wrapper->isRegistrationPeriodActive($now));
    $this->assertEquals(TRUE, $wrapper->isRegistrationPeriodOver($now));
  }

  /**
   * Assert getter methods.
   *
   * @param string $case
   *   Test case description.
   * @param array $values
   *   Entity values to initialize the wrapper with.
   * @param array $assertions
   *   Assertions to be ran over it.
   */
  protected function assertGetters(string $case, array $values, array $assertions): void {
    // Create wrapper.
    $wrapper = $this->createWrapper($values);

    // Run assertions.
    foreach ($assertions as $method => $expected) {
      $actual = $wrapper->{$method}();
      if ($actual instanceof DrupalDateTime) {
        $actual = $actual->format(DrupalDateTime::FORMAT);
      }
      $this->assertEquals($expected, $actual, "Test case '{$case}' failed: method {$method} did not return what was expected.");
    }
  }

  /**
   * Assert registration status related methods.
   *
   * @param string $case
   *   Test case description.
   * @param array $values
   *   Entity values to initialize the wrapper with.
   * @param string $now
   *   Current time in \DrupalDateTime::FORMAT.
   * @param array $assertions
   *   Assertions to be ran over it.
   */
  protected function assertRegistrationStatus(string $case, array $values, string $now, array $assertions): void {
    // Create wrapper.
    $wrapper = $this->createWrapper($values);

    // Run assertions.
    foreach ($assertions as $method => $expected) {
      $datetime = \DateTime::createFromFormat(DrupalDateTime::FORMAT, $now, new \DateTimeZone('UTC'));
      $actual = $wrapper->{$method}($datetime);
      if ($actual instanceof DrupalDateTime) {
        $actual = $actual->format(DrupalDateTime::FORMAT);
      }
      $this->assertEquals($expected, $actual, "Test case '{$case}' failed: method {$method} did not return what was expected.");
    }
  }

  /**
   * Assert online status related methods.
   *
   * @param string $case
   *   Test case description.
   * @param array $values
   *   Entity values to initialize the wrapper with.
   * @param string $now
   *   Current time in \DrupalDateTime::FORMAT.
   * @param array $assertions
   *   Assertions to be ran over it.
   */
  protected function assertOnlineStatus(string $case, array $values, string $now, array $assertions): void {
    // Create wrapper.
    $wrapper = $this->createWrapper($values);

    // Run assertions.
    foreach ($assertions as $method => $expected) {
      $datetime = \DateTime::createFromFormat(DrupalDateTime::FORMAT, $now, new \DateTimeZone('UTC'));
      $actual = $wrapper->{$method}($datetime);
      if ($actual instanceof DrupalDateTime) {
        $actual = $actual->format(DrupalDateTime::FORMAT);
      }
      $this->assertEquals($expected, $actual, "Test case '{$case}' failed: method {$method} did not return what was expected.");
    }
  }

  /**
   * Create a wrapper object given its node entity values.
   *
   * @param array $values
   *   Entity values.
   *
   * @return \Drupal\oe_content_event\EventNodeWrapper
   *   Wrapper object.
   */
  protected function createWrapper(array $values): EntityWrapperInterface {
    // Create wrapper.
    $node = Node::create($values + [
      'type' => 'oe_event',
      'title' => 'My event',
    ]);
    $node->save();
    return EventNodeWrapper::getInstance($node);
  }

  /**
   * Assertion for wrapper getters.
   *
   * This would normally be a data provider but it would require a full test
   * bootstrap for each test case, which will add minutes to test runs.
   *
   * Since we are testing a simple entity wrapper we will instead run it in
   * the same test.
   *
   * @return array
   *   List of assertion for wrapper test.
   */
  public function getterAssertions(): array {
    return [
      [
        'case' => 'Test default getters behaviour when no fields are set',
        'values' => [],
        'assertions' => [
          'isAsPlanned' => TRUE,
          'isCancelled' => FALSE,
          'isRescheduled' => FALSE,
          'isPostponed' => FALSE,
          'hasOnlineDates' => FALSE,
          'hasOnlineLink' => FALSE,
          'hasOnlineType' => FALSE,
          'hasRegistration' => FALSE,
          'getRegistrationStartDate' => NULL,
          'getRegistrationEndDate' => NULL,
          'getOnlineStartDate' => NULL,
          'getOnlineEndDate' => NULL,
        ],
      ],
      [
        'case' => 'Test hasOnlineType and hasOnlineLink methods',
        'values' => [
          'oe_event_online_link' => [
            'uri' => 'http://example.com',
          ],
          'oe_event_online_type' => [
            'value' => 'livestream',
          ],
        ],
        'assertions' => [
          'hasOnlineLink' => TRUE,
          'hasOnlineType' => TRUE,
        ],
      ],
      [
        'case' => 'Test hasRegistration behaviour',
        'values' => [
          'oe_event_registration_url' => [
            'uri' => 'http://example.com',
          ],
        ],
        'assertions' => [
          'isAsPlanned' => TRUE,
          'isCancelled' => FALSE,
          'isRescheduled' => FALSE,
          'isPostponed' => FALSE,
          'hasRegistration' => TRUE,
          'getRegistrationStartDate' => NULL,
          'getRegistrationEndDate' => NULL,
        ],
      ],
      [
        'case' => 'Test cancelled event',
        'values' => [
          'oe_event_status' => 'cancelled',
        ],
        'assertions' => [
          'isAsPlanned' => FALSE,
          'isCancelled' => TRUE,
          'isRescheduled' => FALSE,
          'isPostponed' => FALSE,
        ],
      ],
      [
        'case' => 'Test rescheduled event',
        'values' => [
          'oe_event_status' => 'rescheduled',
        ],
        'assertions' => [
          'isAsPlanned' => FALSE,
          'isCancelled' => FALSE,
          'isRescheduled' => TRUE,
          'isPostponed' => FALSE,
        ],
      ],
      [
        'case' => 'Test postponed event',
        'values' => [
          'oe_event_status' => 'postponed',
        ],
        'assertions' => [
          'isAsPlanned' => FALSE,
          'isCancelled' => FALSE,
          'isRescheduled' => FALSE,
          'isPostponed' => TRUE,
        ],
      ],
      [
        'case' => 'Test date getters',
        'values' => [
          'oe_event_dates' => [
            'value' => '2016-09-20T12:00:00',
            'end_value' => '2016-09-21T12:00:00',
          ],
          'oe_event_registration_dates' => [
            'value' => '2016-05-10T12:00:00',
            'end_value' => '2016-05-15T12:00:00',
          ],
          'oe_event_online_dates' => [
            'value' => '2016-09-20T12:00:00',
            'end_value' => '2016-09-21T12:00:00',
          ],
        ],
        'assertions' => [
          'getStartDate' => '2016-09-20 12:00:00',
          'getEndDate' => '2016-09-21 12:00:00',
          'getRegistrationStartDate' => '2016-05-10 12:00:00',
          'getRegistrationEndDate' => '2016-05-15 12:00:00',
          'getOnlineStartDate' => '2016-09-20 12:00:00',
          'getOnlineEndDate' => '2016-09-21 12:00:00',
          'hasOnlineDates' => TRUE,
        ],
      ],
    ];
  }

  /**
   * Assertion for registration status.
   *
   * This would normally be a data provider but it would require a full test
   * bootstrap for each test case, which will add minutes to test runs.
   *
   * Since we are testing a simple entity wrapper we will instead run it in
   * the same test.
   *
   * @return array
   *   List of assertion for wrapper test.
   */
  protected function registrationStatusAssertions() {
    return [
      [
        'case' => 'Test open registration',
        'values' => [
          'oe_event_registration_url' => [
            'uri' => 'http://example.com',
          ],
        ],
        'now' => '2016-09-20 12:00:00',
        'assertions' => [
          'isRegistrationOpen' => TRUE,
          'isRegistrationClosed' => FALSE,
        ],
      ],
      [
        'case' => 'Test closed registration',
        'values' => [],
        'now' => '2016-09-20 12:00:00',
        'assertions' => [
          'isRegistrationOpen' => FALSE,
          'isRegistrationClosed' => TRUE,
        ],
      ],
      [
        'case' => 'Test open registration with cancelled event',
        'values' => [
          'oe_event_registration_url' => [
            'uri' => 'http://example.com',
          ],
          'oe_event_status' => 'cancelled',
        ],
        'now' => '2016-09-20 12:00:00',
        'assertions' => [
          'isRegistrationOpen' => FALSE,
          'isRegistrationClosed' => TRUE,
        ],
      ],
      [
        'case' => 'Test open registration with postponed event',
        'values' => [
          'oe_event_registration_url' => [
            'uri' => 'http://example.com',
          ],
          'oe_event_status' => 'postponed',
        ],
        'now' => '2016-09-20 12:00:00',
        'assertions' => [
          'isRegistrationOpen' => FALSE,
          'isRegistrationClosed' => TRUE,
        ],
      ],
      [
        'case' => 'Test registration is considered opened when inside the active period',
        'values' => [
          'oe_event_registration_url' => [
            'uri' => 'http://example.com',
          ],
          'oe_event_registration_dates' => [
            'value' => '2016-05-10T12:00:00',
            'end_value' => '2016-05-15T12:00:00',
          ],
        ],
        'now' => '2016-05-12 12:00:00',
        'assertions' => [
          'isRegistrationOpen' => TRUE,
          'isRegistrationClosed' => FALSE,
        ],
      ],
      [
        'case' => 'Test registration is considered closed when outside the active period',
        'values' => [
          'oe_event_registration_url' => [
            'uri' => 'http://example.com',
          ],
          'oe_event_registration_dates' => [
            'value' => '2016-05-10T12:00:00',
            'end_value' => '2016-05-15T12:00:00',
          ],
        ],
        'now' => '2016-05-01 12:00:00',
        'assertions' => [
          'isRegistrationOpen' => FALSE,
          'isRegistrationClosed' => TRUE,
        ],
      ],
    ];
  }

  /**
   * Assertion for online status.
   *
   * This would normally be a data provider but it would require a full test
   * bootstrap for each test case, which will add minutes to test runs.
   *
   * Since we are testing a simple entity wrapper we will instead run it in
   * the same test.
   *
   * @return array
   *   List of assertion for wrapper test.
   */
  protected function onlineStatusAssertions() {
    return [
      [
        'case' => 'Test open online status',
        'values' => [
          'oe_event_online_dates' => [
            'value' => '2016-09-20T12:00:00',
            'end_value' => '2016-09-21T12:00:00',
          ],
        ],
        'now' => '2016-09-20 12:00:01',
        'assertions' => [
          'isOnlinePeriodActive' => TRUE,
          'isOnlinePeriodOver' => FALSE,
          'isOnlinePeriodYetToCome' => FALSE,
        ],
      ],
      [
        'case' => 'Test closed online status',
        'values' => [],
        'now' => '2016-09-20 12:00:00',
        'assertions' => [
          'isOnlinePeriodActive' => FALSE,
          'isOnlinePeriodOver' => FALSE,
          'isOnlinePeriodYetToCome' => FALSE,
        ],
      ],
      [
        'case' => 'Test online period is over if the date is out of range',
        'values' => [
          'oe_event_online_dates' => [
            'value' => '2016-09-20T12:00:00',
            'end_value' => '2016-09-21T12:00:00',
          ],
        ],
        'now' => '2016-09-22 12:00:00',
        'assertions' => [
          'isOnlinePeriodActive' => FALSE,
          'isOnlinePeriodOver' => TRUE,
          'isOnlinePeriodYetToCome' => FALSE,
        ],
      ],
      [
        'case' => 'Test online period is yet to come if the date is before range',
        'values' => [
          'oe_event_online_dates' => [
            'value' => '2016-09-20T12:00:00',
            'end_value' => '2016-09-21T12:00:00',
          ],
        ],
        'now' => '2016-09-19 12:00:00',
        'assertions' => [
          'isOnlinePeriodActive' => FALSE,
          'isOnlinePeriodOver' => FALSE,
          'isOnlinePeriodYetToCome' => TRUE,
        ],
      ],
    ];
  }

}
