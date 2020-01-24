<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_event\Kernel\EntityDecorator\Node;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\node\Entity\Node;
use Drupal\oe_content_event\EntityDecorator\Node\EventEntityDecorator;
use Drupal\Tests\oe_content_event\Kernel\EventKernelTestBase;

/**
 * Tests event decorator class.
 */
class EventEntityDecoratorTest extends EventKernelTestBase {

  /**
   * Test decorator methods.
   */
  public function testDecoratorMethods(): void {
    foreach ($this->decoratorGettersAssertions() as $assertion) {
      $this->assertDecoratorMethods($assertion['case'], $assertion['values'], $assertion['assertions']);
    }
  }

  /**
   * Test event ending.
   */
  public function testEventIsOver(): void {
    $decorator = $this->createDecorator([
      'oe_event_dates' => [
        'value' => '2016-05-10T12:00:00',
        'end_value' => '2016-05-15T12:00:00',
      ],
    ]);

    // Event is not over.
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-09 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(FALSE, $decorator->isOver($now));

    // Event is over.
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-30 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(TRUE, $decorator->isOver($now));

    // Event is not over but it's cancelled, so it's considered to be over.
    $decorator = $this->createDecorator([
      'oe_event_status' => 'cancelled',
      'oe_event_dates' => [
        'value' => '2016-05-10T12:00:00',
        'end_value' => '2016-05-15T12:00:00',
      ],
    ]);
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-12 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(TRUE, $decorator->isOver($now));
  }

  /**
   * Test registration period methods.
   */
  public function testRegistrationPeriodMethods(): void {
    $decorator = $this->createDecorator([
      'oe_event_registration_dates' => [
        'value' => '2016-05-10T12:00:00',
        'end_value' => '2016-05-15T12:00:00',
      ],
    ]);

    // Registration yet to come.
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-09 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(TRUE, $decorator->isRegistrationPeriodYetToCome($now));
    $this->assertEquals(FALSE, $decorator->isRegistrationPeriodActive($now));
    $this->assertEquals(FALSE, $decorator->isRegistrationPeriodOver($now));

    // Registration just started.
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-10 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(FALSE, $decorator->isRegistrationPeriodYetToCome($now));
    $this->assertEquals(TRUE, $decorator->isRegistrationPeriodActive($now));
    $this->assertEquals(FALSE, $decorator->isRegistrationPeriodOver($now));

    // Registration in progress.
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-12 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(FALSE, $decorator->isRegistrationPeriodYetToCome($now));
    $this->assertEquals(TRUE, $decorator->isRegistrationPeriodActive($now));
    $this->assertEquals(FALSE, $decorator->isRegistrationPeriodOver($now));

    // Registration just ended.
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-15 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(FALSE, $decorator->isRegistrationPeriodYetToCome($now));
    $this->assertEquals(FALSE, $decorator->isRegistrationPeriodActive($now));
    $this->assertEquals(TRUE, $decorator->isRegistrationPeriodOver($now));

    // Registration is over.
    $now = \DateTime::createFromFormat(DrupalDateTime::FORMAT, '2016-05-20 12:00:00', new \DateTimeZone('UTC'));
    $this->assertEquals(FALSE, $decorator->isRegistrationPeriodYetToCome($now));
    $this->assertEquals(FALSE, $decorator->isRegistrationPeriodActive($now));
    $this->assertEquals(TRUE, $decorator->isRegistrationPeriodOver($now));
  }

  /**
   * Assert decorator methods.
   *
   * @param string $case
   *   Test case description.
   * @param array $values
   *   Entity values to initialize the decorator with.
   * @param array $assertions
   *   Assertions to be ran over it.
   */
  protected function assertDecoratorMethods(string $case, array $values, array $assertions): void {
    // Create decorator.
    $decorator = $this->createDecorator($values);

    // Run assertions.
    foreach ($assertions as $method => $expected) {
      $actual = $decorator->{$method}();
      if ($actual instanceof DrupalDateTime) {
        $actual = $actual->format(DrupalDateTime::FORMAT);
      }
      $this->assertEquals($expected, $actual, "Test case '{$case}' failed: method {$method} did not return what was expected.");
    }
  }

  /**
   * Create a decorator object given its node entity values.
   *
   * @param array $values
   *   Entity values.
   *
   * @return \Drupal\oe_content_event\EntityDecorator\Node\EventEntityDecorator
   *   Decorator object.
   */
  protected function createDecorator(array $values): EventEntityDecorator {
    // Create decorator.
    $node = Node::create($values + [
      'type' => 'oe_event',
      'title' => 'My event',
    ]);
    $node->save();
    return new EventEntityDecorator($node);
  }

  /**
   * Assertion for decorator getters.
   *
   * This would normally be a data provider but it would require a full test
   * bootstrap for each test case, which will add minutes to test runs.
   *
   * Since we are testing a simple entity decorator we will instead run it in
   * the same test.
   *
   * @return array
   *   List of assertion for decorator test.
   */
  public function decoratorGettersAssertions(): array {
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
