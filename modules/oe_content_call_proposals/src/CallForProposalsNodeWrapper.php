<?php

declare(strict_types=1);

namespace Drupal\oe_content_call_proposals;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\oe_content\CallEntityWrapperBase;

/**
 * Wrap the "Call for proposals" content type by adding business methods.
 *
 * @method static CallForProposalsNodeWrapperInterface getInstance(\Drupal\Core\Entity\ContentEntityInterface $entity)
 */
class CallForProposalsNodeWrapper extends CallEntityWrapperBase implements CallForProposalsNodeWrapperInterface {

  /**
   * Contains the entity bundle id.
   *
   * @var string
   */
  protected $entityBundle = 'oe_call_proposals';

  /**
   * Contains the Opening Date field id.
   *
   * @var string
   */
  protected $openingDate = 'oe_call_proposals_opening_date';

  /**
   * Contains the Deadline date field id.
   *
   * @var string
   */
  protected $deadline = 'oe_call_proposals_deadline';

  /**
   * {@inheritdoc}
   */
  public function getDeadlineDate(): ?DrupalDateTime {
    if (!$this->hasDeadlineDate()) {
      return NULL;
    }

    $dates = $this->entity->get($this->deadline)->getValue();

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
