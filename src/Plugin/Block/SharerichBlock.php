<?php

/**
 * @file
 * Contains \Drupal\sharerich\Plugin\Block\SharerichBlock.
 */

namespace Drupal\sharerich\Plugin\Block;

use Drupal\Core\Block\BlockBase;

use Drupal\Core\Routing\RedirectDestinationTrait;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
/**
 * Provides a Sharerich block.
 *
 * @Block(
 *   id = "sharerich",
 *   admin_label = @Translation("Sharerich"),
 * )
 */
class SharerichBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $configuration = $this->configuration;

    $options = array();
    $entity_storage = \Drupal::entityTypeManager()->getStorage('sharerich');
    foreach ($entity_storage->loadMultiple() as $entity) {
      $entity_id = $entity->id();
      $options[$entity_id] = $entity->label();
    }

    $form['sharerich_set'] = array(
      '#type' => 'select',
      '#title' => t('Sharerich Set'),
      '#options' => $options,
      '#default_value' => isset($configuration['sharerich_set']) ? $configuration['sharerich_set'] : array(),
    );

    return $form;
  }

  /**
   * Overrides \Drupal\block\BlockBase::blockSubmit().
   */
  public function blockSubmit($form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $this->configuration['sharerich_set'] = $form_state->getValue('sharerich_set');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $entity_storage = \Drupal::entityTypeManager()->getStorage('sharerich');

    if ($sharerich_set = $entity_storage->load($this->configuration['sharerich_set'])) {
      // Get list of allowed tags.
      $allowed_tags = \Drupal::config('sharerich.settings')->get('allowed_html');
      $allowed_tags = str_replace(['<', '>'], '', $allowed_tags);
      $allowed_tags = \Drupal\Component\Utility\Html::escape($allowed_tags);
      $allowed_tags = explode(' ', $allowed_tags);

      $buttons = array();
      foreach ($sharerich_set->getServices() as $name => $service) {
        $buttons[$name] = [
          '#attributes' => ['class' => ['sharerich-buttons-wrapper', 'rrssb-buttons-wrapper']],
          '#wrapper_attributes' => ['class' => ['rrssb-' . $name]],
          '#markup' => $service['markup'],
          '#allowed_tags' => $allowed_tags,
        ];
      }

      $route = \Drupal::request()->attributes->get(RouteObjectInterface::ROUTE_NAME);
      switch ($route) {
        case 'entity.node.canonical':
          $context = ['node' => \Drupal::request()->attributes->get('node')];
          break;

        case 'entity.taxonomy_term.canonical':
          $context = ['taxonomy_term' => \Drupal::request()->attributes->get('taxonomy_term')];
          break;

        case 'entity.user.canonical':
          $context = ['user' => \Drupal::request()->attributes->get('user')];
          break;

        default:
          $context = [];
      }

      // Allow other modules to alter the buttons markup.
      \Drupal::moduleHandler()->alter('sharerich_buttons', $buttons, $context);

      $build = [
        '#theme' => 'item_list',
        '#items' => $buttons,
        '#type' => 'ul',
        '#wrapper_attributes' => ['class' => ['sharerich-wrapper', 'share-container']],
        '#attributes' => ['class' => ['sharerich-buttons', 'rrssb-buttons']],
        '#attached' => [
          'library' => [
            'sharerich/rrssb',
            'sharerich/sharerich'
          ]
        ],
      ];

      // Rendering $build here because render() in \Drupal\Core\Theme\ThemeManager
      // doesn't add the attributes to the UL if we return the renderable array $build.
      // This happens on the line that contains "if (isset($info['variables'])) {"/
      $markup = \Drupal::service('renderer')->render($build);

      // Replace tokens.
      $markup = \Drupal::token()->replace($markup, $context);

      return [
        '#markup' => $markup,
        '#allowed_tags' => $allowed_tags,
        '#cache' => [
          'contexts' => ['url.path']
        ],
      ];
    }
  }

}
