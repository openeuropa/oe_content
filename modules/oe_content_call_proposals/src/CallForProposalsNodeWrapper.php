<?php

declare(strict_types = 1);

namespace Drupal\oe_content_call_proposals;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_content\EntityWrapperBase;

/**
 * Wrap the "Call for proposals" content type by adding business methods.
 *
 * @method static CallForProposalsNodeWrapperInterface getInstance(\Drupal\Core\Entity\ContentEntityInterface $entity)
 */
class CallForProposalsNodeWrapper extends EntityWrapperBase implements CallForProposalsNodeWrapperInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getEntityId(): string {
    return 'node';
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityBundle(): string {
    return 'oe_call_proposals';
  }

  /**
   * {@inheritdoc}
   */
  public function hasOpeningDate(): bool {
    return !$this->entity->get('oe_call_proposals_opening_date')->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function hasDeadlineDate(): bool {
    return !$this->entity->get('oe_call_proposals_deadline')->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function getOpeningDate(): ?DrupalDateTime {
    if (!$this->hasOpeningDate()) {
      return NULL;
    }
    $opening_date = $this->entity->get('oe_call_proposals_opening_date')->date;
    // Prevent upcoming status when now & opening dates are the same.
    $opening_date->setTime(0, 0, 0);
    return $opening_date;
  }

  /**
   * {@inheritdoc}
   */
  public function getDeadlineDate(): ?DrupalDateTime {
    if (!$this->hasDeadlineDate()) {
      return NULL;
    }

    $dates = $this->entity->get('oe_call_proposals_deadline')->getValue();

    if (!is_array($dates)) {
      return NULL;
    }

    $values_column = array_column($dates, 'value');
    // Get the latest date in relation to the "closed" proposal's status.
    array_multisort($values_column, SORT_DESC, $dates);

    $date = current($dates);
    return new DrupalDateTime($date['value']);
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus(): string {
    $opening_date = $this->getOpeningDate();
    $closing_date = $this->getDeadlineDate();
    $now = $this->getNow();

    $status = static::STATUS_NOT_AVAILABLE;
    if (isset($opening_date)) {
      if ($now < $opening_date) {
        $status = static::STATUS_UPCOMING;
      }
      else {
        $status = static::STATUS_OPEN;
      }
    }
    if (isset($closing_date) && $now > $closing_date) {
      $status = static::STATUS_CLOSED;
    }

    return $status;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatusLabel(): MarkupInterface {
    switch ($this->getStatus()) {
      case static::STATUS_UPCOMING:
        $label = $this->t('Upcoming');
        break;

      case static::STATUS_OPEN:
        $label = $this->t('Open');
        break;

      case static::STATUS_CLOSED:
        $label = $this->t('Closed');
        break;

      default:
        $label = $this->t('N/A');
    }

    return $label;
  }

  /**
   * {@inheritdoc}
   */
  public function hasStatus(): bool {
    return $this->getStatus() !== static::STATUS_NOT_AVAILABLE;
  }

  /**
   * {@inheritdoc}
   */
  public function isUpcoming(): bool {
    return $this->getStatus() === static::STATUS_UPCOMING;
  }

  /**
   * {@inheritdoc}
   */
  public function isOpen(): bool {
    return $this->getStatus() === static::STATUS_OPEN;
  }

  /**
   * {@inheritdoc}
   */
  public function isClosed(): bool {
    return $this->getStatus() === static::STATUS_CLOSED;
  }

  /**
   * Gets current time.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   Current time.
   */
  public function getNow(): DrupalDateTime {
    $request_time = \Drupal::service('datetime.time')->getRequestTime();
    return DrupalDateTime::createFromTimestamp($request_time);
  }

  /**
   * {@inheritdoc}
   */
  public static function getModelsList(): array {
    return [
      CallForProposalsNodeWrapperInterface::MODEL_SINGLE_STAGE => t('Single-stage'),
      CallForProposalsNodeWrapperInterface::MODEL_TWO_STAGE => t('Two-stage'),
      CallForProposalsNodeWrapperInterface::MODEL_MULTIPLE_CUT_OFF => t('Multiple cut-off'),
      CallForProposalsNodeWrapperInterface::MODEL_PERMANENT => t('Permanent'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getModel(): ?string {
    return $this->entity->get('oe_call_proposals_model')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getModelLabel(): MarkupInterface {
    $model = $this->getModel();
    if (!empty($model)) {
      $list = static::getModelsList();
      if (isset($list[$model])) {
        return $list[$model];
      }
    }

    return $this->t('N/A');
  }

  /**
   * {@inheritdoc}
   */
  public function isDeadlineModelPermanent(): bool {
    return $this->getModel() === CallForProposalsNodeWrapperInterface::MODEL_PERMANENT;
  }

}
