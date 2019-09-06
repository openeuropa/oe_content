<?php

namespace Drupal\oe_content_event\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Drupal\oe_content_event\Entity\EventVenueInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EventVenueController.
 *
 *  Returns responses for Event venue routes.
 */
class EventVenueController extends ControllerBase implements ContainerInjectionInterface {


  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Constructs a new EventVenueController.
   *
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The renderer.
   */
  public function __construct(DateFormatter $date_formatter, Renderer $renderer) {
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer')
    );
  }

  /**
   * Displays a Event venue revision.
   *
   * @param int $event_venue_revision
   *   The Event venue revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($event_venue_revision) {
    $event_venue = $this->entityTypeManager()->getStorage('event_venue')
      ->loadRevision($event_venue_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('event_venue');

    return $view_builder->view($event_venue);
  }

  /**
   * Page title callback for a Event venue revision.
   *
   * @param int $event_venue_revision
   *   The Event venue revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($event_venue_revision) {
    $event_venue = $this->entityTypeManager()->getStorage('event_venue')
      ->loadRevision($event_venue_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $event_venue->label(),
      '%date' => $this->dateFormatter->format($event_venue->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Event venue.
   *
   * @param \Drupal\oe_content_event\Entity\EventVenueInterface $event_venue
   *   A Event venue object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(EventVenueInterface $event_venue) {
    $account = $this->currentUser();
    $event_venue_storage = $this->entityTypeManager()->getStorage('event_venue');

    $langcode = $event_venue->language()->getId();
    $langname = $event_venue->language()->getName();
    $languages = $event_venue->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $event_venue->label()]) : $this->t('Revisions for %title', ['%title' => $event_venue->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all event venue revisions") || $account->hasPermission('administer event venue entities')));
    $delete_permission = (($account->hasPermission("delete all event venue revisions") || $account->hasPermission('administer event venue entities')));

    $rows = [];

    $vids = $event_venue_storage->revisionIds($event_venue);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\oe_content_event\EventVenueInterface $revision */
      $revision = $event_venue_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $event_venue->getRevisionId()) {
          $link = $this->l($date, new Url('entity.event_venue.revision', [
            'event_venue' => $event_venue->id(),
            'event_venue_revision' => $vid,
          ]));
        }
        else {
          $link = $event_venue->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.event_venue.translation_revert', [
                'event_venue' => $event_venue->id(),
                'event_venue_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.event_venue.revision_revert', [
                'event_venue' => $event_venue->id(),
                'event_venue_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.event_venue.revision_delete', [
                'event_venue' => $event_venue->id(),
                'event_venue_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['event_venue_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
