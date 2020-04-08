<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_event\Kernel;

use Drupal\node\Entity\Node;

/**
 * Test event creation business logic.
 */
class EventBusinessLogicTest extends EventKernelTestBase {

  /**
   * Test that organiser fields are correctly saved.
   */
  public function testOrganiserFields(): void {
    $values = [
      'type' => 'oe_event',
      'title' => 'My node title',
      'oe_event_organiser_name' => 'Organisation',
      'oe_event_organiser_internal' => 'http://publications.europa.eu/resource/authority/corporate-body/DIGIT',
    ];

    // An un-checked checkbox inherits its default value, "TRUE" in this case.
    $node = Node::create($values);
    $node->save();

    // Assert that only the internal organiser value has been kept.
    $this->assertTrue($node->get('oe_event_organiser_name')->isEmpty());
    $this->assertEquals('Directorate-General for Informatics', $node->get('oe_event_organiser_internal')->entity->label());

    // Test internal organiser to be checked.
    $node = Node::create([
      'oe_event_organiser_is_internal' => 1,
    ] + $values);
    $node->save();

    // Assert that only the internal organiser value has been kept.
    $this->assertTrue($node->get('oe_event_organiser_name')->isEmpty());
    $this->assertEquals('Directorate-General for Informatics', $node->get('oe_event_organiser_internal')->entity->label());

    // Test internal organiser to be un-checked.
    $node = Node::create([
      'oe_event_organiser_is_internal' => 0,
    ] + $values);
    $node->save();

    // Assert that only the external organiser value has been kept.
    $this->assertTrue($node->get('oe_event_organiser_internal')->isEmpty());
    $this->assertEquals('Organisation', $node->get('oe_event_organiser_name')->value);
  }

}
