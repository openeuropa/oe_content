<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_event\Kernel;

/**
 * Test event composite entity reference fields.
 */
class EventCompositeReferenceTest extends EventKernelTestBase {

  /**
   * Test the composite entity delete when an event is deleted.
   */
  public function testEventDelete(): void {
    // Prepare composite entities for event.
    $values = [
      'name' => 'Venue',
      'bundle' => 'default',
    ];
    $venue_storage = $this->entityTypeManager->getStorage('oe_venue');
    $venue = $venue_storage->create($values);
    $venue->save();

    $values = [
      'name' => 'Press contact 1',
      'bundle' => 'press',
    ];
    $contact_storage = $this->entityTypeManager->getStorage('oe_contact');
    $press_contact_one = $contact_storage->create($values);
    $press_contact_one->save();

    $values = [
      'name' => 'Press contact 2',
      'bundle' => 'press',
    ];
    $press_contact_two = $contact_storage->create($values);
    $press_contact_two->save();

    // Prepare an event with the composite entities referenced.
    $values = [
      'title' => 'First event',
      'type' => 'oe_event',
      'oe_event_venue' => [
        'target_id' => $venue->id(),
        'target_revision_id' => $venue->getLoadedRevisionId(),
      ],
      'oe_event_contact' => [
        [
          'target_id' => $press_contact_one->id(),
          'target_revision_id' => $press_contact_one->getLoadedRevisionId(),
        ],
        [
          'target_id' => $press_contact_two->id(),
          'target_revision_id' => $press_contact_two->getLoadedRevisionId(),
        ],
      ],
    ];
    $node_storage = $this->entityTypeManager->getStorage('node');
    $event_one = $node_storage->create($values);
    $event_one->save();

    // Create another event where we reference only one of the Contacts.
    $values['title'] = 'Second event';
    unset($values['oe_event_contact'][1]);
    $event_two = $node_storage->create($values);
    $event_two->save();

    // Delete the first event where the same venue and one of the contacts were
    // referenced.
    $event_one->delete();

    // Assert that the Venue and Press contact 1 were not deleted because they
    // are referenced by another event node.
    $venue_storage->resetCache();
    $this->assertNotEmpty($venue_storage->load($venue->id()));
    $contact_storage->resetCache();
    $this->assertNotEmpty($contact_storage->load($press_contact_one->id()));

    // Assert the Press contact 2 is deleted because it was not referenced
    // by another event node.
    $this->assertEmpty($contact_storage->load($press_contact_two->id()));

    // Delete the second event.
    $event_two->delete();

    // Assert both Venue and Press contact 1 were deleted because no other
    // event nodes referencing them.
    $venue_storage->resetCache();
    $this->assertEmpty($venue_storage->load($venue->id()));
    $contact_storage->resetCache();
    $this->assertEmpty($contact_storage->load($press_contact_one->id()));
  }

}
