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
  $form['sharerich_facebook_app_id'] = array(
    '#type' => 'textfield',
    '#title' => t('Facebook App ID'),
    '#description' => t('You need to have an App ID, which you can get from Facebook.'),
    '#default_value' => variable_get('sharerich_facebook_app_id'),
    '#states' => array(
      'invisible' => array(
        ':input[name="sharerich_services[facebook]"]' => array('checked' => FALSE),
      ),
    ),
  );
  $form['sharerich_facebook_site_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Facebook Site URL'),
    '#description' => t('You need to have an App ID, which you can get from Facebook.'),
    '#default_value' => variable_get('sharerich_facebook_site_url'),
    '#states' => array(
      'invisible' => array(
        ':input[name="sharerich_services[facebook]"]' => array('checked' => FALSE),
      ),
    ),
  );
  $form['sharerich_youtube_username'] = array(
    '#type' => 'textfield',
    '#title' => t('YouTube Username'),
    '#description' => t('Enter your YouTube username in order for the social button to link to your YouTube channel.'),
    '#default_value' => variable_get('sharerich_youtube_username'),
    '#states' => array(
      'invisible' => array(
        ':input[name="sharerich_services[youtube]"]' => array('checked' => FALSE),
      ),
    ),
  );
  $form['sharerich_github_username'] = array(
    '#type' => 'textfield',
    '#title' => t('Github Username'),
    '#description' => t('Enter your Github username in order for the social button to link to your Github profile.'),
    '#default_value' => variable_get('sharerich_github_username'),
    '#states' => array(
      'invisible' => array(
        ':input[name="sharerich_services[github]"]' => array('checked' => FALSE),
      ),
    ),
  );

  return system_settings_form($form);

