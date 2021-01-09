<?php

namespace Drupal\customer_entity\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\customer_entity\Entity\CustomerEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CustomerEntityController.
 *
 *  Returns responses for Customer routes.
 */
class CustomerEntityController extends ControllerBase implements ContainerInjectionInterface {

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a Customer revision.
   *
   * @param int $customer_entities_revision
   *   The Customer revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($customer_entities_revision) {
    $customer_entities = $this->entityTypeManager()->getStorage('customer_entities')
      ->loadRevision($customer_entities_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('customer_entities');

    return $view_builder->view($customer_entities);
  }

  /**
   * Page title callback for a Customer revision.
   *
   * @param int $customer_entities_revision
   *   The Customer revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($customer_entities_revision) {
    $customer_entities = $this->entityTypeManager()->getStorage('customer_entities')
      ->loadRevision($customer_entities_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $customer_entities->label(),
      '%date' => $this->dateFormatter->format($customer_entities->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Customer.
   *
   * @param \Drupal\customer_entity\Entity\CustomerEntityInterface $customer_entities
   *   A Customer object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(CustomerEntityInterface $customer_entities) {
    $account = $this->currentUser();
    $customer_entities_storage = $this->entityTypeManager()->getStorage('customer_entities');

    $langcode = $customer_entities->language()->getId();
    $langname = $customer_entities->language()->getName();
    $languages = $customer_entities->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $customer_entities->label()]) : $this->t('Revisions for %title', ['%title' => $customer_entities->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all customer revisions") || $account->hasPermission('administer customer entities')));
    $delete_permission = (($account->hasPermission("delete all customer revisions") || $account->hasPermission('administer customer entities')));

    $rows = [];

    $vids = $customer_entities_storage->revisionIds($customer_entities);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\customer_entity\CustomerEntityInterface $revision */
      $revision = $customer_entities_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $customer_entities->getRevisionId()) {
          $link = $this->l($date, new Url('entity.customer_entities.revision', [
            'customer_entities' => $customer_entities->id(),
            'customer_entities_revision' => $vid,
          ]));
        }
        else {
          $link = $customer_entities->link($date);
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
              Url::fromRoute('entity.customer_entities.translation_revert', [
                'customer_entities' => $customer_entities->id(),
                'customer_entities_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.customer_entities.revision_revert', [
                'customer_entities' => $customer_entities->id(),
                'customer_entities_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.customer_entities.revision_delete', [
                'customer_entities' => $customer_entities->id(),
                'customer_entities_revision' => $vid,
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

    $build['customer_entities_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
