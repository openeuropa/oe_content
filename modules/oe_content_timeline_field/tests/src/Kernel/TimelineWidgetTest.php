<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Kernel;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormState;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Entity\FilterFormat;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests the timeline field widget.
 */
class TimelineWidgetTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_content_timeline_field',
    'oe_content_timeline_test_constraint',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    FieldStorageConfig::create([
      'field_name' => 'timeline',
      'entity_type' => 'entity_test',
      'type' => 'timeline_field',
    ])->save();

    FieldConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'timeline',
      'bundle' => 'entity_test',
      'required' => TRUE,
    ])->save();

    FilterFormat::create([
      'format' => 'plain_text',
      'name' => 'Plain text',
    ])->save();

    $entity = EntityTest::create();

    $entity_form_display = EntityFormDisplay::collectRenderDisplay($entity, 'default');
    $entity_form_display->setComponent('timeline', [
      'weight' => 1,
      'region' => 'content',
      'type' => 'timeline_widget',
      'settings' => [],
      'third_party_settings' => [],
    ]);
    $entity_form_display->save();

    $user = $this->createUser();
    $this->setCurrentUser($user);
  }

  /**
   * Tests that errors are correctly reported on the widget.
   *
   * Tests both the flagErrors() and the errorElement() methods.
   *
   * @see \Drupal\oe_content_timeline_field\Plugin\Field\FieldWidget\TimelineFieldWidget::errorElement()
   * @see \Drupal\oe_content_timeline_field\Plugin\Field\FieldWidget\TimelineFieldWidget::flagErrors()
   */
  public function testWidgetErrors(): void {
    // Retrieve the entity default entity form.
    $entity = EntityTest::create();
    $form_object = $this->container->get('entity_type.manager')->getFormObject('entity_test', 'default');
    $form_object->setEntity($entity);

    // Prepare the values to be submitted, structured as they would be in a
    // form.
    $values = [
      'title' => [
        0 => ['value' => 'My page'],
      ],
      'timeline' => [
        0 => [
          'body' => [
            'value' => 'Body text',
            'format' => 'plain_text',
          ],
        ],
      ],
    ];

    // Mark the whole widget item at delta 0 for the error.
    // @see \Drupal\oe_content_timeline_test_constraint\Plugin\Validation\Constraint\TestConstraintValidator::validate()
    $state = \Drupal::state();
    $state->set('oe_content_timeline_test_constraint.error_paths', [0 => 0]);

    $form = $this->buildForm($form_object, $values);
    // The test constraint uses different values for the placeholders.
    // The widget will change them to use the same labels used in the widget
    // form.
    $expected_message = '<em class="placeholder">Label</em> and <em class="placeholder">Title</em> fields cannot be empty when <em class="placeholder">Content</em> is specified.';
    // Errors are placed in the elements they are generated for. Verify that
    // the expected message is set in the whole delta of the widget.
    // Children of elements with errors inherit the errors from the parent, so
    // we stop at verifying that the whole widget item is marked.
    $this->assertEquals($expected_message, (string) $form['timeline']['widget'][0]['#errors']);

    // Mark only the label as element with violation.
    $state->set('oe_content_timeline_test_constraint.error_paths', [0 => '0.label']);
    $form = $this->buildForm($form_object, $values);
    // The label element should contain the error.
    $this->assertEquals($expected_message, (string) $form['timeline']['widget'][0]['label']['#errors']);
    // The parent item and the other widget form elements should have no errors.
    $this->assertEquals(NULL, $form['timeline']['widget'][0]['#errors']);
    $this->assertEquals(NULL, $form['timeline']['widget'][0]['title']['#errors']);
    $this->assertEquals(NULL, $form['timeline']['widget'][0]['body']['#errors']);

    // Mark an non-existent element as the one with violation. The whole item
    // should be marked.
    $state->set('oe_content_timeline_test_constraint.error_paths', [0 => '0.nonexistent']);
    $form = $this->buildForm($form_object, $values);
    $this->assertEquals($expected_message, (string) $form['timeline']['widget'][0]['#errors']);

    // Test that no violation replacement is done when no parameters are
    // replaced.
    // The timeline field instance is required. If left empty, a Symfony
    // validation will be triggered, which contains a non-Drupal placeholder
    // "{{ value }}". The constraint validator of Drupal will handle this case,
    // but if we replace this violation this will trigger a fatal error.
    // @see \Drupal\oe_content_timeline_field\Plugin\Field\FieldWidget\TimelineFieldWidget::flagErrors()
    // @see \Drupal\Core\Validation\DrupalTranslator::processParameters()
    // @see \Drupal\Component\Render\FormattableMarkup::placeholderFormat()
    unset($values['timeline']);
    $this->buildForm($form_object, $values);
  }

  /**
   * Builds a form given the user input.
   *
   * @param \Drupal\Core\Form\FormInterface $form_object
   *   The form object.
   * @param array $user_input
   *   The user input array.
   *
   * @return array
   *   The built form.
   */
  protected function buildForm(FormInterface $form_object, array $user_input): array {
    $form_state = new FormState();
    $form_state->setUserInput($user_input);
    $form_state->setProgrammed();

    return $this->container->get('form_builder')->buildForm($form_object, $form_state);
  }

}
