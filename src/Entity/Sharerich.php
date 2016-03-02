<?php

/**
 * @file
 * Contains \Drupal\sharerich\Entity\Sharerich.
 */

namespace Drupal\sharerich\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\sharerich\SharerichInterface;

/**
 * Defines the Sharerich entity.
 *
 * @ConfigEntityType(
 *   id = "sharerich",
 *   label = @Translation("Sharerich set"),
 *   handlers = {
 *     "list_builder" = "Drupal\sharerich\SharerichListBuilder",
 *     "form" = {
 *       "add" = "Drupal\sharerich\Form\SharerichForm",
 *       "edit" = "Drupal\sharerich\Form\SharerichForm",
 *       "delete" = "Drupal\sharerich\Form\SharerichDeleteForm"
 *     }
 *   },
 *   config_prefix = "sharerich",
 *   admin_permission = "administer sharerich",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/sharerich/{sharerich}",
 *     "edit-form" = "/admin/structure/sharerich/{sharerich}/edit",
 *     "delete-form" = "/admin/structure/sharerich/{sharerich}/delete",
 *     "collection" = "/admin/structure/visibility_group"
 *   }
 * )
 */
class Sharerich extends ConfigEntityBase implements SharerichInterface {
  /**
   * The Sharerich ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Sharerich label.
   *
   * @var string
   */
  protected $label;

}
