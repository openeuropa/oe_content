<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_person\Kernel;

/**
 * Tests the Person content type.
 */
class PersonEntityTest extends PersonEntityTestBase {

  /**
   * Tests the label of the Person content type is generated automatically.
   */
  public function testPersonEntityLabel(): void {
    $person = $this->entityTypeManager->getStorage('node')->create([
      'type' => 'oe_person',
    ]);
    $person->save();
    // We are missing the first and last name.
    $this->assertEquals(' ', $person->label());

    // Set a first and last name.
    $person->set('oe_person_first_name', 'Jacques');
    $person->set('oe_person_last_name', 'Delors');
    $person->save();

    $this->assertEquals('Jacques Delors', $person->label());

    // Set a displayed name.
    $person->set('oe_person_displayed_name', 'Delors Jacques');
    $person->save();

    $this->assertEquals('Delors Jacques', $person->label());

    // Remove the displayed name.
    $person->set('oe_person_displayed_name', NULL);
    $person->save();
    $this->assertEquals('Jacques Delors', $person->label());
  }

  /**
   * Test that the entity person values are stored properly.
   */
  public function testPersonEntityValues(): void {

    $file = \Drupal::service('file.repository')->writeData(file_get_contents(\Drupal::service('extension.list.module')->getPath('oe_content') . '/tests/fixtures/sample.pdf'), 'public://sample.pdf');
    $file->save();
    $document_media = $this->entityTypeManager->getStorage('media')->create([
      'bundle' => 'document',
      'name' => 'Document title',
      'oe_media_file' => [
        'target_id' => $file->id(),
      ],
    ]);
    $document_media->save();

    $image = \Drupal::service('file.repository')->writeData(file_get_contents(\Drupal::service('extension.list.module')->getPath('oe_content') . '/tests/fixtures/example_1.jpeg'), 'public://example_1.jpeg');
    $image->save();
    $image_media = $this->entityTypeManager->getStorage('media')->create([
      'bundle' => 'image',
      'name' => 'Image media title',
      'oe_media_image' => [
        [
          'target_id' => $image->id(),
          'alt' => 'default alt',
        ],
      ],
    ]);
    $image_media->save();

    $organisation = $this->entityTypeManager->getStorage('node')->create([
      'type' => 'oe_organisation',
      'title' => 'Organisation title',
    ]);
    $organisation->save();

    $person = $this->entityTypeManager->getStorage('node')->create([
      'type' => 'oe_person',
      'oe_person_first_name' => 'John',
      'oe_person_last_name' => 'Doe',
      'oe_person_type' => 'eu',
      'oe_departments' => [
        [
          'target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC',
        ],
      ],
      'oe_person_media' => [
        'target_id' => $image_media->id(),
      ],
      'oe_social_media_links' => [
        [
          'uri' => 'http://example.com',
          'title' => 'Social link',
          'link_type' => 'facebook',
        ],
      ],
      'oe_person_transparency_intro' => 'Transparency introduction text',
      'oe_person_transparency_links' => [
        [
          'uri' => 'http://example.com',
          'title' => 'Transparency link',
        ],
      ],
      'oe_person_biography_intro' => 'Biography introduction text',
      'oe_person_biography_timeline' => [
        [
          'title' => 'Timeline Title 1',
          'label' => 'Timeline Label 1',
          'value' => 'Timeline Value 1',
        ],
      ],
      'oe_person_cv' => [
        'target_id' => $document_media->id(),
      ],
      'oe_person_interests_intro' => 'Interests introduction text',
      'oe_person_interests_file' => [
        'target_id' => $document_media->id(),
      ],
      'oe_person_organisation' => [
        'target_id' => $organisation->id(),
        'target_revision_id' => $organisation->getRevisionId(),
      ],
    ]);
    $person->save();

    // Assert the values of a UE person are saved properly.
    $this->assertEquals('John Doe', $person->label());
    $this->assertEmpty($person->get('oe_person_organisation')->entity);
    $this->assertEquals('http://publications.europa.eu/resource/authority/corporate-body/ABEC', $person->get('oe_departments')->target_id);
    $this->assertEquals($image_media->id(), $person->get('oe_person_media')->target_id);
    $this->assertEquals('http://example.com', $person->get('oe_social_media_links')->uri);
    $this->assertEquals('Transparency introduction text', $person->get('oe_person_transparency_intro')->value);
    $this->assertEquals('http://example.com', $person->get('oe_person_transparency_links')->uri);
    $this->assertEquals('Biography introduction text', $person->get('oe_person_biography_intro')->value);
    $this->assertEquals('Timeline Value 1', $person->get('oe_person_biography_timeline')->value);
    $this->assertEquals($document_media->id(), $person->get('oe_person_cv')->target_id);
    $this->assertEquals('Interests introduction text', $person->get('oe_person_interests_intro')->value);

    // Update the person to be non-eu and assert that the values
    // are updated properly.
    $person->set('oe_person_type', 'non_eu');
    $person->set('oe_person_organisation', [
      'target_id' => $organisation->id(),
      'target_revision_id' => $organisation->getRevisionId(),
    ]);
    $person->save();
    $this->assertEquals('Organisation title', $person->get('oe_person_organisation')->entity->label());
    $this->assertEmpty($person->get('oe_departments')->target_id);
    $this->assertEmpty($person->get('oe_person_media')->target_id);
    $this->assertEmpty($person->get('oe_social_media_links')->uri);
    $this->assertEmpty($person->get('oe_person_transparency_intro')->value);
    $this->assertEmpty($person->get('oe_person_transparency_links')->uri);
    $this->assertEmpty($person->get('oe_person_biography_intro')->value);
    $this->assertEmpty($person->get('oe_person_biography_timeline')->value);
    $this->assertEmpty($person->get('oe_person_cv')->target_id);
    $this->assertEmpty($person->get('oe_person_interests_intro')->value);
  }

}
