<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_tender\Kernel;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\oe_content\EntityWrapperInterface;
use Drupal\oe_content_tender\TenderNodeWrapper;

/**
 * Tests tender wrapper class.
 *
 * @coversDefaultClass \Drupal\oe_content_tender\TenderNodeWrapper
 * @group oe_content_tender
 */
class TenderNodeWrapperTest extends TenderKernelTestBase {

  /**
   * Test exception thrown if an unsupported entity type is passed.
   */
  public function testUnsupportedEntityType(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("The current wrapper only accepts 'node' entities.");
    $entity = EntityTest::create();
    TenderNodeWrapper::getInstance($entity);
  }

  /**
   * Test exception thrown if an unsupported entity bundle is passed.
   */
  public function testUnsupportedEntityBundle(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("The current wrapper only accepts 'node' entities of type 'oe_tender'.");
    $node_type = NodeType::create([
      'type' => 'test',
      'name' => 'Test bundle',
    ]);
    $node_type->save();
    $entity = Node::create([
      'type' => 'test',
    ]);
    TenderNodeWrapper::getInstance($entity);
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
   * Create a wrapper object given its node entity values.
   *
   * @param array $values
   *   Entity values.
   *
   * @return \Drupal\oe_content_tender\TenderNodeWrapper
   *   Wrapper object.
   */
  protected function createWrapper(array $values): EntityWrapperInterface {
    // Create wrapper.
    $node = Node::create($values + [
      'type' => 'oe_tender',
      'title' => 'My tender',
    ]);
    $node->save();
    return TenderNodeWrapper::getInstance($node);
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
          'isNotAvailable' => TRUE,
          'isUpcoming' => FALSE,
          'isOpen' => FALSE,
          'isClosed' => FALSE,
          'hasOpeningDate' => FALSE,
          'hasDeadlineDate' => FALSE,
          'getOpeningDate' => NULL,
          'getDeadlineDate' => NULL,
        ],
      ],
      [
        'case' => 'Test upcoming status',
        'values' => [
          'oe_tender_opening_date' => [
            'value' => date('Y') + 1 . '-11-26',
          ],
        ],
        'assertions' => [
          'isNotAvailable' => FALSE,
          'isUpcoming' => TRUE,
          'isOpen' => FALSE,
          'isClosed' => FALSE,
          'hasOpeningDate' => TRUE,
          'hasDeadlineDate' => FALSE,
          'getOpeningDate' => date('Y') + 1 . '-11-26 00:00:00',
          'getDeadlineDate' => NULL,
        ],
      ],
      [
        'case' => 'Test open status',
        'values' => [
          'oe_tender_opening_date' => [
            'value' => '2020-09-01',
          ],
          'oe_tender_deadline' => [
            'value' => date('Y') + 1 . '-09-01T00:00:00',
          ],
        ],
        'assertions' => [
          'isNotAvailable' => FALSE,
          'isUpcoming' => FALSE,
          'isOpen' => TRUE,
          'isClosed' => FALSE,
          'hasOpeningDate' => TRUE,
          'hasDeadlineDate' => TRUE,
          'getOpeningDate' => '2020-09-01 00:00:00',
          'getDeadlineDate' => date('Y') + 1 . '-09-01 00:00:00',
        ],
      ],
      [
        'case' => 'Test closed status',
        'values' => [
          'oe_tender_opening_date' => [
            'value' => '2020-09-01',
          ],
          'oe_tender_deadline' => [
            'value' => '2020-09-10T00:00:00',
          ],
        ],
        'assertions' => [
          'isNotAvailable' => FALSE,
          'isUpcoming' => FALSE,
          'isOpen' => FALSE,
          'isClosed' => TRUE,
          'hasOpeningDate' => TRUE,
          'hasDeadlineDate' => TRUE,
          'getOpeningDate' => '2020-09-01 00:00:00',
          'getDeadlineDate' => '2020-09-10 00:00:00',
        ],
      ],
    ];
  }

}
