<?php
/**
 * @file
 * Admin page.
 */

/**
 * Implements hook_form().
 */
function sharerich_admin_form() {
  $dir = drupal_get_path('module','sharerich') . '/services';
  $services = file_scan_directory($dir, '/.inc/', $options = array(), $depth = 0);
  foreach ($services as $key => $service) {
    $options[] = $service->name;
  }
  $form['sharerich_services'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Available Services.'),
    '#options' => drupal_map_assoc($options),
    '#default_value' => variable_get('sharerich_services'),
  );

  return system_settings_form($form);

