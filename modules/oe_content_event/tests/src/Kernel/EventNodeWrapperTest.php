<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_event\Kernel;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\node\Entity\Node;
use Drupal\oe_content_event\EventNodeWrapper;

/**
 * Tests event wrapper class.
 */
class EventNodeWrapperTest extends EventKernelTestBase {

  /**
   * Test wrapper methods.
   */
  public function testWrapperMethods(): void {
    foreach ($this->wrapperGettersAssertions() as $assertion) {
      $this->assertWrapperMethods($assertion['case'], $assertion['values'], $assertion['assertions']);
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

    // Event is not over but it's cancelled, so it's considered to be over.
    $wrapper = $this->createWrapper([
      'oe_event_status' => 'cancelled',
      'oe_event_dates' => [
        'value' => '2016-05-10T12:00:00',
        'end_value' => '2016-05-15T12:00:00',
      ],
    ]);
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-12 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(TRUE, $wrapper->isOver($now));
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
   * Assert wrapper methods.
   *
   * @param string $case
   *   Test case description.
   * @param array $values
   *   Entity values to initialize the wrapper with.
   * @param array $assertions
   *   Assertions to be ran over it.
   */
  protected function assertWrapperMethods(string $case, array $values, array $assertions): void {
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
   * Create a wrapper object given its node entity values.
   *
   * @param array $values
   *   Entity values.
   *
   * @return \Drupal\oe_content_event\EventNodeWrapper
   *   Wrapper object.
   */
  protected function createWrapper(array $values): EventNodeWrapper {
    // Create wrapper.
    $node = Node::create($values + [
      'type' => 'oe_event',
      'title' => 'My event',
    ]);
    $node->save();
    return new EventNodeWrapper($node);
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
  public function wrapperGettersAssertions(): array {
    return [
      [
        'case' => 'Test default getters behaviour when no fields are set',
        'values' => [],
        'assertions' => [
          'isAsPlanned' => TRUE,
          'isCancelled' => FALSE,
          'isRescheduled' => FALSE,
          'isPostponed' => FALSE,
          'hasRegistration' => FALSE,
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
        'case' => 'Test open registration',
        'values' => [
          'oe_event_registration_status' => 'open',
        ],
        'assertions' => [
          'isRegistrationOpen' => TRUE,
          'isRegistrationClosed' => FALSE,
        ],
      ],
      [
        'case' => 'Test closed registration',
        'values' => [
          'oe_event_registration_status' => 'closed',
        ],
        'assertions' => [
          'isRegistrationOpen' => FALSE,
          'isRegistrationClosed' => TRUE,
        ],
      ],
      [
        'case' => 'Test open registration with cancelled event',
        'values' => [
          'oe_event_registration_status' => 'open',
          'oe_event_status' => 'cancelled',
        ],
        'assertions' => [
          'isRegistrationOpen' => FALSE,
          'isRegistrationClosed' => TRUE,
        ],
      ],
      [
        'case' => 'Test open registration with postponed event',
        'values' => [
          'oe_event_registration_status' => 'open',
          'oe_event_status' => 'postponed',
        ],
        'assertions' => [
          'isRegistrationOpen' => FALSE,
          'isRegistrationClosed' => TRUE,
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
        ],
        'assertions' => [
          'getStartDate' => '2016-09-20 12:00:00',
          'getEndDate' => '2016-09-21 12:00:00',
          'getRegistrationStartDate' => '2016-05-10 12:00:00',
          'getRegistrationEndDate' => '2016-05-15 12:00:00',
        ],
      ],
    ];
  }

}
