<?php

namespace Drupal\customers\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\customers\Entity\CustomerEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CustomerEntityController.
 *
 *  Returns responses for Customers routes.
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
   * Displays a Customers revision.
   *
   * @param int $customer_entity_revision
   *   The Customers revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($customer_entity_revision) {
    $customer_entity = $this->entityTypeManager()->getStorage('customer_entity')
      ->loadRevision($customer_entity_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('customer_entity');

    return $view_builder->view($customer_entity);
  }

  /**
   * Page title callback for a Customers revision.
   *
   * @param int $customer_entity_revision
   *   The Customers revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($customer_entity_revision) {
    $customer_entity = $this->entityTypeManager()->getStorage('customer_entity')
      ->loadRevision($customer_entity_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $customer_entity->label(),
      '%date' => $this->dateFormatter->format($customer_entity->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Customers.
   *
   * @param \Drupal\customers\Entity\CustomerEntityInterface $customer_entity
   *   A Customers object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(CustomerEntityInterface $customer_entity) {
    $account = $this->currentUser();
    $customer_entity_storage = $this->entityTypeManager()->getStorage('customer_entity');

    $langcode = $customer_entity->language()->getId();
    $langname = $customer_entity->language()->getName();
    $languages = $customer_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $customer_entity->label()]) : $this->t('Revisions for %title', ['%title' => $customer_entity->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all customers revisions") || $account->hasPermission('administer customers entities')));
    $delete_permission = (($account->hasPermission("delete all customers revisions") || $account->hasPermission('administer customers entities')));

    $rows = [];

    $vids = $customer_entity_storage->revisionIds($customer_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\customers\CustomerEntityInterface $revision */
      $revision = $customer_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $customer_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.customer_entity.revision', [
            'customer_entity' => $customer_entity->id(),
            'customer_entity_revision' => $vid,
          ]));
        }
        else {
          $link = $customer_entity->link($date);
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
              Url::fromRoute('entity.customer_entity.translation_revert', [
                'customer_entity' => $customer_entity->id(),
                'customer_entity_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.customer_entity.revision_revert', [
                'customer_entity' => $customer_entity->id(),
                'customer_entity_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.customer_entity.revision_delete', [
                'customer_entity' => $customer_entity->id(),
                'customer_entity_revision' => $vid,
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

    $build['customer_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
