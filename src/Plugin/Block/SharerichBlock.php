<?php

/**
 * @file
 * Contains \Drupal\sharerich\Plugin\Block\SharerichBlock.
 */

namespace Drupal\sharerich\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
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

    $form['orientation'] = array(
      '#type' => 'select',
      '#title' => t('Orientation'),
      '#options' => array('horizontal' => t('Horizontal'), 'vertical' => t('Vertical')),
      '#default_value' => isset($configuration['orientation']) ? $configuration['orientation'] : array(),
      '#description' => t('If you set to vertical and place the block on the top of the main content area, it will float on the side.'),
    );

    $form['sticky'] = array(
      '#type' => 'checkbox',
      '#title' => t('Sticky'),
      '#default_value' => $configuration['sticky'],
      '#description' => t('Stick to the top when scrolling.'),
      '#states' => array(
        'visible' => array(
          ':input[name="settings[orientation]"]' => array('value' => 'vertical'),
        ),
      ),
    );

    return $form;
  }

  /**
   * Overrides \Drupal\block\BlockBase::blockSubmit().
   */
  public function blockSubmit($form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $this->configuration['sharerich_set'] = $form_state->getValue('sharerich_set');
    $this->configuration['orientation'] = $form_state->getValue('orientation');
    $this->configuration['sticky'] = $form_state->getValue('sticky');
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

      $bb[] = array(
        'data' => t('Colour: !c', array('!c' => 'White')),
        'class' => array('exterior-colour'),
      );
      $bb[] = array(
        'data' => t('Colour: !c', array('!c' => 'Blue')),
        'class' => array('exterior-colour'),
      );

      $list = [
        '#theme' => 'item_list',
        '#items' => $bb,
        '#type' => 'ul',
        '#wrapper_attributes' => [
          'class' => [
            'sharerich-wrapper',
            'share-container',
            $this->configuration['sharerich_set'],
            $this->configuration['orientation'],
            ($this->configuration['sticky']) ? 'sticky' : '',
          ]
        ],
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
      $markup = \Drupal::service('renderer')->render($list);

      $build['content'] = array(
        '#theme' => 'sharerich',
        '#buttons' => $markup,
      );
return $build;

      // Replace tokens.
      $markup = \Drupal::token()->replace($markup, $context);

      return [
        '#items_list' => $build,
        '#allowed_tags' => $allowed_tags,
//        '#cache' => [
//          'contexts' => ['url.path']
//        ],
      ];
    }
  }

}
