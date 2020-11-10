<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_call_proposals\Kernel;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\oe_content_call_proposals\CallForProposalsNodeWrapper;
use Drupal\oe_content_call_proposals\CallForProposalsNodeWrapperInterface;
use Drupal\oe_content\CallEntityWrapperInterface;

/**
 * Tests "Call for proposals" wrapper class.
 *
 * @coversDefaultClass \Drupal\oe_content_call_proposals\CallForProposalsNodeWrapper
 * @group oe_content_call_proposals
 */
class CallForProposalsNodeWrapperTest extends CallForProposalsKernelTestBase {

  /**
   * Test exception thrown if an unsupported entity type is passed.
   */
  public function testUnsupportedEntityType(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("The current wrapper only accepts 'node' entities.");
    $entity = EntityTest::create();
    CallForProposalsNodeWrapper::getInstance($entity);
  }

  /**
   * Test exception thrown if an unsupported entity bundle is passed.
   */
  public function testUnsupportedEntityBundle(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("The current wrapper only accepts 'node' entities of type 'oe_call_proposals'.");
    $node_type = NodeType::create([
      'type' => 'test',
      'name' => 'Test bundle',
    ]);
    $node_type->save();
    $entity = Node::create([
      'type' => 'test',
    ]);
    CallForProposalsNodeWrapper::getInstance($entity);
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
   * @return \Drupal\oe_content_call_proposals\CallForProposalsNodeWrapper
   *   Wrapper object.
   */
  protected function createWrapper(array $values): CallForProposalsNodeWrapperInterface {
    // Create wrapper.
    $node = Node::create($values + [
      'type' => 'oe_call_proposals',
      'title' => 'My call for proposal',
    ]);
    $node->save();
    return CallForProposalsNodeWrapper::getInstance($node);
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
          'hasOpeningDate' => FALSE,
          'hasDeadlineDate' => FALSE,
          'getOpeningDate' => NULL,
          'getDeadlineDate' => NULL,
          'getStatus' => CallEntityWrapperInterface::STATUS_NOT_AVAILABLE,
          'hasStatus' => FALSE,
          'isUpcoming' => FALSE,
          'isOpen' => FALSE,
          'isClosed' => FALSE,
          'getStatusLabel' => 'N/A',
          'getModelLabel' => 'N/A',
        ],
      ],
      [
        'case' => 'Test upcoming status',
        'values' => [
          'oe_call_proposals_opening_date' => [
            'value' => date('Y') + 1 . '-11-26',
          ],
        ],
        'assertions' => [
          'hasStatus' => TRUE,
          'isUpcoming' => TRUE,
          'isOpen' => FALSE,
          'isClosed' => FALSE,
          'getStatus' => CallEntityWrapperInterface::STATUS_UPCOMING,
          'getStatusLabel' => 'Upcoming',
          'hasOpeningDate' => TRUE,
          'hasDeadlineDate' => FALSE,
          'getOpeningDate' => date('Y') + 1 . '-11-26 00:00:00',
          'getDeadlineDate' => NULL,
          'getModelLabel' => 'N/A',
        ],
      ],
      [
        'case' => 'Test open status',
        'values' => [
          'oe_call_proposals_opening_date' => [
            'value' => '2020-09-01',
          ],
          'oe_call_proposals_deadline' => [
            'value' => date('Y') + 1 . '-09-01T00:00:00',
          ],
        ],
        'assertions' => [
          'hasStatus' => TRUE,
          'isUpcoming' => FALSE,
          'isOpen' => TRUE,
          'isClosed' => FALSE,
          'getStatus' => CallEntityWrapperInterface::STATUS_OPEN,
          'getStatusLabel' => 'Open',
          'hasOpeningDate' => TRUE,
          'hasDeadlineDate' => TRUE,
          'getOpeningDate' => '2020-09-01 00:00:00',
          'getDeadlineDate' => date('Y') + 1 . '-09-01 00:00:00',
          'getModelLabel' => 'N/A',
        ],
      ],
      [
        'case' => 'Test closed status with opening date set',
        'values' => [
          'oe_call_proposals_opening_date' => [
            'value' => '2020-09-01',
          ],
          'oe_call_proposals_deadline' => [
            'value' => '2020-09-10T00:00:00',
          ],
        ],
        'assertions' => [
          'hasStatus' => TRUE,
          'isUpcoming' => FALSE,
          'isOpen' => FALSE,
          'isClosed' => TRUE,
          'getStatus' => CallEntityWrapperInterface::STATUS_CLOSED,
          'getStatusLabel' => 'Closed',
          'hasOpeningDate' => TRUE,
          'hasDeadlineDate' => TRUE,
          'getOpeningDate' => '2020-09-01 00:00:00',
          'getDeadlineDate' => '2020-09-10 00:00:00',
          'getModelLabel' => 'N/A',
        ],
      ],
      [
        'case' => 'Test closed status without opening date set',
        'values' => [
          'oe_call_proposals_deadline' => [
            'value' => '2020-09-10T00:00:00',
          ],
        ],
        'assertions' => [
          'hasStatus' => TRUE,
          'isUpcoming' => FALSE,
          'isOpen' => FALSE,
          'isClosed' => TRUE,
          'getStatus' => CallEntityWrapperInterface::STATUS_CLOSED,
          'getStatusLabel' => 'Closed',
          'hasOpeningDate' => FALSE,
          'hasDeadlineDate' => TRUE,
          'getOpeningDate' => NULL,
          'getDeadlineDate' => '2020-09-10 00:00:00',
          'getModel' => NULL,
          'getModelLabel' => 'N/A',
        ],
      ],
      [
        'case' => 'Test open status with Single-stage model',
        'values' => [
          'oe_call_proposals_opening_date' => [
            'value' => '2020-09-01',
          ],
          'oe_call_proposals_deadline' => [
            'value' => date('Y') + 1 . '-09-01T00:00:00',
          ],
          'oe_call_proposals_model' => [
            'value' => CallForProposalsNodeWrapperInterface::MODEL_SINGLE_STAGE,
          ],
        ],
        'assertions' => [
          'hasStatus' => TRUE,
          'isUpcoming' => FALSE,
          'isOpen' => TRUE,
          'isClosed' => FALSE,
          'getStatus' => CallEntityWrapperInterface::STATUS_OPEN,
          'getStatusLabel' => 'Open',
          'hasOpeningDate' => TRUE,
          'hasDeadlineDate' => TRUE,
          'getOpeningDate' => '2020-09-01 00:00:00',
          'getDeadlineDate' => date('Y') + 1 . '-09-01 00:00:00',
          'getModel' => CallForProposalsNodeWrapperInterface::MODEL_SINGLE_STAGE,
          'getModelLabel' => 'Single-stage',
          'getModelsList' => [
            CallForProposalsNodeWrapperInterface::MODEL_SINGLE_STAGE => t('Single-stage'),
            CallForProposalsNodeWrapperInterface::MODEL_TWO_STAGE => t('Two-stage'),
            CallForProposalsNodeWrapperInterface::MODEL_MULTIPLE_CUT_OFF => t('Multiple cut-off'),
            CallForProposalsNodeWrapperInterface::MODEL_PERMANENT => t('Permanent'),
          ],
        ],
      ],
      [
        'case' => 'Test open status with Two-stage model',
        'values' => [
          'oe_call_proposals_opening_date' => [
            'value' => '2020-09-01',
          ],
          'oe_call_proposals_deadline' => [
            'value' => date('Y') + 1 . '-09-01T00:00:00',
          ],
          'oe_call_proposals_model' => [
            'value' => CallForProposalsNodeWrapperInterface::MODEL_TWO_STAGE,
          ],
        ],
        'assertions' => [
          'hasStatus' => TRUE,
          'isUpcoming' => FALSE,
          'isOpen' => TRUE,
          'isClosed' => FALSE,
          'getStatus' => CallEntityWrapperInterface::STATUS_OPEN,
          'getStatusLabel' => 'Open',
          'hasOpeningDate' => TRUE,
          'hasDeadlineDate' => TRUE,
          'getOpeningDate' => '2020-09-01 00:00:00',
          'getDeadlineDate' => date('Y') + 1 . '-09-01 00:00:00',
          'getModel' => CallForProposalsNodeWrapperInterface::MODEL_TWO_STAGE,
          'getModelLabel' => 'Two-stage',
          'getModelsList' => [
            CallForProposalsNodeWrapperInterface::MODEL_SINGLE_STAGE => t('Single-stage'),
            CallForProposalsNodeWrapperInterface::MODEL_TWO_STAGE => t('Two-stage'),
            CallForProposalsNodeWrapperInterface::MODEL_MULTIPLE_CUT_OFF => t('Multiple cut-off'),
            CallForProposalsNodeWrapperInterface::MODEL_PERMANENT => t('Permanent'),
          ],
        ],
      ],
      [
        'case' => 'Test open status with Multiple cut-off model',
        'values' => [
          'oe_call_proposals_opening_date' => [
            'value' => '2020-09-01',
          ],
          'oe_call_proposals_deadline' => [
            'value' => date('Y') + 1 . '-09-01T00:00:00',
          ],
          'oe_call_proposals_model' => [
            'value' => CallForProposalsNodeWrapperInterface::MODEL_MULTIPLE_CUT_OFF,
          ],
        ],
        'assertions' => [
          'hasStatus' => TRUE,
          'isUpcoming' => FALSE,
          'isOpen' => TRUE,
          'isClosed' => FALSE,
          'getStatus' => CallEntityWrapperInterface::STATUS_OPEN,
          'getStatusLabel' => 'Open',
          'hasOpeningDate' => TRUE,
          'hasDeadlineDate' => TRUE,
          'getOpeningDate' => '2020-09-01 00:00:00',
          'getDeadlineDate' => date('Y') + 1 . '-09-01 00:00:00',
          'getModel' => CallForProposalsNodeWrapperInterface::MODEL_MULTIPLE_CUT_OFF,
          'getModelLabel' => 'Multiple cut-off',
          'getModelsList' => [
            CallForProposalsNodeWrapperInterface::MODEL_SINGLE_STAGE => t('Single-stage'),
            CallForProposalsNodeWrapperInterface::MODEL_TWO_STAGE => t('Two-stage'),
            CallForProposalsNodeWrapperInterface::MODEL_MULTIPLE_CUT_OFF => t('Multiple cut-off'),
            CallForProposalsNodeWrapperInterface::MODEL_PERMANENT => t('Permanent'),
          ],
        ],
      ],
      [
        'case' => 'Test upcoming status with Permanent model',
        'values' => [
          'oe_call_proposals_opening_date' => [
            'value' => date('Y') + 1 . '-11-26',
          ],
          'oe_call_proposals_model' => [
            'value' => CallForProposalsNodeWrapperInterface::MODEL_PERMANENT,
          ],
        ],
        'assertions' => [
          'hasStatus' => TRUE,
          'isUpcoming' => TRUE,
          'isOpen' => FALSE,
          'isClosed' => FALSE,
          'getStatus' => CallEntityWrapperInterface::STATUS_UPCOMING,
          'getStatusLabel' => 'Upcoming',
          'hasOpeningDate' => TRUE,
          'hasDeadlineDate' => FALSE,
          'getOpeningDate' => date('Y') + 1 . '-11-26 00:00:00',
          'getDeadlineDate' => NULL,
          'getModel' => CallForProposalsNodeWrapperInterface::MODEL_PERMANENT,
          'getModelLabel' => 'Permanent',
          'getModelsList' => [
            CallForProposalsNodeWrapperInterface::MODEL_SINGLE_STAGE => t('Single-stage'),
            CallForProposalsNodeWrapperInterface::MODEL_TWO_STAGE => t('Two-stage'),
            CallForProposalsNodeWrapperInterface::MODEL_MULTIPLE_CUT_OFF => t('Multiple cut-off'),
            CallForProposalsNodeWrapperInterface::MODEL_PERMANENT => t('Permanent'),
          ],
        ],
      ],
      [
        'case' => 'Test open status with Two-stage model and two Deadline dates',
        'values' => [
          'oe_call_proposals_opening_date' => [
            'value' => '2020-09-01',
          ],
          'oe_call_proposals_deadline' => [
            [
              'value' => date('Y') + 1 . '-09-01T00:00:00',
            ],
            [
              'value' => '2020-09-01 00:00:00',
            ],
          ],
          'oe_call_proposals_model' => [
            'value' => CallForProposalsNodeWrapperInterface::MODEL_TWO_STAGE,
          ],
        ],
        'assertions' => [
          'hasStatus' => TRUE,
          'isUpcoming' => FALSE,
          'isOpen' => TRUE,
          'isClosed' => FALSE,
          'getStatus' => CallEntityWrapperInterface::STATUS_OPEN,
          'getStatusLabel' => 'Open',
          'hasOpeningDate' => TRUE,
          'hasDeadlineDate' => TRUE,
          'getOpeningDate' => '2020-09-01 00:00:00',
          'getDeadlineDate' => date('Y') + 1 . '-09-01 00:00:00',
          'getModel' => CallForProposalsNodeWrapperInterface::MODEL_TWO_STAGE,
          'getModelLabel' => 'Two-stage',
          'getModelsList' => [
            CallForProposalsNodeWrapperInterface::MODEL_SINGLE_STAGE => t('Single-stage'),
            CallForProposalsNodeWrapperInterface::MODEL_TWO_STAGE => t('Two-stage'),
            CallForProposalsNodeWrapperInterface::MODEL_MULTIPLE_CUT_OFF => t('Multiple cut-off'),
            CallForProposalsNodeWrapperInterface::MODEL_PERMANENT => t('Permanent'),
          ],
        ],
      ],
    ];
  }

}
