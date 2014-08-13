<?php
/**
 * @file
 * Admin page.
 */

/**
 * Implements hook_form().
 */
function sharerich_admin_form() {
  $dir = drupal_get_path('module', 'sharerich') . '/services';
  $services = file_scan_directory($dir, '/.inc/', array(), 0);
  foreach ($services as $key => $service) {
    $options[] = $service->name;
  }
  $form['sharerich_services'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Available Services.'),
    '#options' => drupal_map_assoc($options),
    '#default_value' => sharerich_get_services(),
  );
  foreach ($options as $key => $service_name) {
    $var_name = 'sharerich_custom_' . $service_name;
    $form[$var_name] = array(
      '#type' => 'textarea',
      '#title' => t('Custom code for %name', array('%name' => $service_name)),
      '#description' => t('You can customize the code for this service.'),
      '#default_value' => sharerich_get_service_content($service_name),
      '#states' => array(
        'invisible' => array(
          ':input[name="sharerich_services[' . $service_name . ']"]' => array('checked' => FALSE),
        ),
      ),
    );
  }
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
  $form['sharerich_add_js'] = array(
    '#type' => 'textarea',
    '#title' => t('Add js'),
    '#description' => t('Enter the url for each js file.'),
    '#default_value' => variable_get('sharerich_add_js'),
  );
  $form['sharerich_add_css'] = array(
    '#type' => 'textarea',
    '#title' => t('Add CSS'),
    '#description' => t('Enter the url for each CSS file.'),
    '#default_value' => variable_get('sharerich_add_css'),
  );
  $form['#submit'][] = 'sharerich_admin_form_submit';

  return system_settings_form($form);
}

/**
 * Implements sharerich_admin_form_submit().
 */
function sharerich_admin_form_submit(&$form, $form_state) {
  $reset = FALSE;
  foreach ($form_state['values']['sharerich_services'] as $service_name) {
    // If the file is not stored in the variables, read the file, otherwise
    // save the changes on existing variable.
    $var_name = 'sharerich_custom_' . $service_name;
    $var_get = variable_get($var_name, '');
    if (empty($var_get) || $reset) {
      variable_set($var_name, sharerich_load_service($service_name));
    }
    else {
      if (isset($form_state['values']['sharerich_custom_' . $service_name])) {
        variable_set($var_name, $form_state['values']['sharerich_custom_' . $service_name]);
      }
    }
  }
}
