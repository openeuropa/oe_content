<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_call_proposals\Kernel;

use Drupal\node\Entity\Node;
use Drupal\oe_content_call_proposals\CallForProposalsNodeWrapperInterface;

/**
 * Tests general logic related to Call for proposal nodes.
 */
class CallForProposalsTest extends CallForProposalsKernelTestBase {

  /**
   * Tests the CFP permanent deadline model.
   */
  public function testPermanentDeadlineModel(): void {
    // Create a node with a single-stage deadline model.
    $node = Node::create([
      'oe_call_proposals_deadline' => [
        'value' => '2050-09-01T00:00:00',
      ],
      'oe_call_proposals_model' => CallForProposalsNodeWrapperInterface::MODEL_SINGLE_STAGE,
      'type' => 'oe_call_proposals',
      'title' => 'My call for proposal',
    ]);
    $node->save();

    /** @var \Drupal\node\NodeInterface $node */
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->assertFalse($node->get('oe_call_proposals_deadline')->isEmpty());

    // Switch the deadline model to permanent and assert the deadline gets
    // cleared.
    $node->set('oe_call_proposals_model', CallForProposalsNodeWrapperInterface::MODEL_PERMANENT);
    $node->save();

    \Drupal::entityTypeManager()->getStorage('node')->resetCache();
    /** @var \Drupal\node\NodeInterface $node */
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->assertTrue($node->get('oe_call_proposals_deadline')->isEmpty());
  }

}
