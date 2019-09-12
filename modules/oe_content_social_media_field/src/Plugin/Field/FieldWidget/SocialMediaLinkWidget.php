<?php

declare(strict_types = 1);

namespace Drupal\oe_content_social_media_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Plugin implementation of the 'social_media_link_widget' widget.
 *
 * @FieldWidget(
 *   id = "social_media_link_widget",
 *   label = @Translation("Social media links widget"),
 *   field_types = {
 *     "social_media_link"
 *   }
 * )
 */
class SocialMediaLinkWidget extends WidgetBase implements WidgetInterface {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#default_value' => $items[$delta]->type ?? NULL,
      '#options' => $this->getSocialMediaOptions(),
      '#required' => $element['#required'],
    ];
    $element['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $items[$delta]->url ?? NULL,
      '#size' => 60,
      '#maxlength' => 255,
      '#required' => $element['#required'],
    ];
    $element['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link title'),
      '#default_value' => $items[$delta]->title ?? NULL,
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => $element['#required'],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    return $element[$violation->arrayPropertyPath[0]];
  }

  /**
   * Returns the social media link type options.
   *
   * @return array
   *   Array of social medias.
   */
  public function getSocialMediaOptions() {
    return [
      'email' => 'Email',
      'facebook' => 'Facebook',
      'flickr' => 'Flickr',
      'instagram' => 'Instagram',
      'linkedin' => 'Linkedin',
      'pinterest' => 'Pinterest',
      'rss' => 'RSS',
      'storify' => 'Storify',
      'twitter' => 'Twitter',
      'yammer' => 'Yammer',
      'youTube' => 'YouTube',
    ];
  }

}
