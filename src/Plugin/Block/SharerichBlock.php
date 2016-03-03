<?php

/**
 * @file
 * Contains \Drupal\sharerich\Plugin\Block\SharerichBlock.
 */

namespace Drupal\sharerich\Plugin\Block;

use Drupal\Core\Block\BlockBase;

use Drupal\Core\Routing\RedirectDestinationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Provides a Sharerich block.
 *
 * @Block(
 *   id = "sharerich_block",
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
    $this->setConfigurationValue('sharerich_set', $form_state->getValue('sharerich_set'));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $entity_storage = \Drupal::entityTypeManager()->getStorage('sharerich');

    if ($sharerich_set = $entity_storage->load($this->configuration['sharerich_set'])) {
      $services = array();
      foreach ($sharerich_set->services as $name => $service) {
        $services[] = [
          '#attributes' => ['class' => ['sharerich-buttons-wrapper', 'rrssb-buttons-wrapper']],
          '#wrapper_attributes' => ['class' => ['rrssb-' . $name]],
          '#markup' => $service['markup'],
          '#allowed_tags' => ['a', 'svg', 'path', 'span'],
        ];
      }
    }
    $build = array(
      '#theme' => 'item_list',
      '#items' => $services,
      '#type' => 'ul',
      '#wrapper_attributes' => ['class' => ['sharerich-wrapper', 'share-container']],
      '#attributes' => ['class' => ['sharerich-buttons', 'rrssb-buttons']],
      '#attached' => array(
        'library' => array(
          'sharerich/rrssb',
          'sharerich/sharerich'
        )
      ),
    );

    // Rendering $build here because render() in \Drupal\Core\Theme\ThemeManager
    // doesn't add the attributes to the UL if we return the renderable array $build.
    // This happens on the line that contains "if (isset($info['variables'])) {"/
    return ['#markup' =>  \Drupal::service('renderer')->render($build)];
  }

}
