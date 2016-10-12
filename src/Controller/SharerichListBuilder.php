<?php

/**
 * @file
 * Contains \Drupal\sharerich\sharerichListBuilder.
 */

namespace Drupal\sharerich\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of sharerich optionset entities.
 */
class sharerichListBuilder extends ConfigEntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Name');
    $header['id'] = $this->t('Machine name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $ops = parent::getDefaultOperations($entity);
    // Do not allow deletion of the default configuration.
    if ($entity->id() == 'default') {
      unset($ops['delete']);
    }
    return $ops;
  }

}
