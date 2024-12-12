<?php

/**
 * @file
 * Contains \Drupal\sharerich\Plugin\Block\SharerichBlock.
 */

namespace Drupal\sharerich\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Routing\RedirectDestinationTrait;

/**
 * Provides a Sharerich block.
 *
 * @Block(
 *   id = "sharerich",
 *   admin_label = @Translation("Sharerich"),
 * )
 */
class SharerichBlock extends BlockBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $configuration = $this->configuration;

    $options = [];
    $entity_storage = \Drupal::entityTypeManager()->getStorage('sharerich');
    foreach ($entity_storage->loadMultiple() as $entity) {
      $entity_id = $entity->id();
      $options[$entity_id] = $entity->label();
    }

    $form['sharerich_set'] = [
      '#type' => 'select',
      '#title' => $this->t('Sharerich Set'),
      '#options' => $options,
      '#default_value' => isset($configuration['sharerich_set']) ? $configuration['sharerich_set'] : [],
    ];

    $form['orientation'] = [
      '#type' => 'select',
      '#title' => $this->t('Orientation'),
      '#options' => ['horizontal' => t('Horizontal'), 'vertical' => t('Vertical')],
      '#default_value' => isset($configuration['orientation']) ? $configuration['orientation'] : [],
      '#description' => t('If you set to vertical and place the block on the top of the main content area, it will float on the side.'),
    ];

    $form['sticky'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Sticky'),
      '#default_value' => isset($configuration['sticky']) ? $configuration['sticky'] : 0,
      '#description' => $this->t('Stick to the top when scrolling.'),
      '#states' => [
        'visible' => [
          ':input[name="settings[orientation]"]' => ['value' => 'vertical'],
        ],
      ],
    ];

    return $form;
  }

  /**
   * Overrides \Drupal\block\BlockBase::blockSubmit().
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
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
      $buttons = array();
      foreach ($sharerich_set->getServices() as $name => $service) {
        $buttons[$name] = [
          '#attributes' => ['class' => ['sharerich-buttons-wrapper', 'rrssb-buttons-wrapper']],
          '#wrapper_attributes' => ['class' => ['rrssb-' . $name]],
          '#markup' => $service['markup'],
          '#allowed_tags' => sharerich_allowed_tags(),
        ];
      }
      // Allow other modules to alter the buttons before they are rendered.
      $context = _sharerich_get_token_data();
      \Drupal::moduleHandler()->alter('sharerich_buttons', $buttons, $context);

      // Render tokens.
      foreach ($buttons as &$button) {
        $button['#markup'] = \Drupal::token()->replace($button['#markup'], _sharerich_get_token_data());
      }

      $item_list = [
        '#theme' => 'item_list',
        '#items' => $buttons,
        '#type' => 'ul',
        '#wrapper_attributes' => [
          'class' => [
            'sharerich-wrapper',
            'share-container',
            'sharerich-' . $this->configuration['sharerich_set'],
            'sharerich-' . $this->configuration['orientation'],
            ($this->configuration['sticky']) ? 'sharerich-sticky' : '',
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

      $build['content'] = [
        '#theme' => 'sharerich',
        '#buttons' => $item_list,
        '#cache' => [
          'contexts' => ['url.path']
        ],
      ];

      return $build;
    }
  }
}
