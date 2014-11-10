<?php

/**
 * @file
 * A Ctools Export UI plugin for Sharerich.
 */
$plugin = array(
  'schema' => 'sharerich_widgets', // As defined in hook_schema().
  'access' => 'administer sharerich', // Define a permission users must have to access these pages.
  // Define the menu item.
  'menu' => array(
    'menu item' => 'sharerich',
    'menu title' => 'Sharerich',
    'menu description' => 'Administer Sharerich presets.',
  ),
  // Define user interface texts.
  'title singular' => t('widget'),
  'title plural' => t('widgets'),
  'title singular proper' => t('Sharerich widget'),
  'title plural proper' => t('Sharerich widgets'),
  // Define the names of the functions that provide the add/edit forms.
  'form' => array(
    'settings' => 'sharerich_ctools_export_ui_form',
  // 'submit' and 'validate' are also valid callbacks.
  ),
);

/**
 * Define the preset add/edit form.
 */
function sharerich_ctools_export_ui_form(&$form, &$form_state) {
  $item = $form_state['item'];

  // Generate the list of available services.
  $dir = drupal_get_path('module', 'sharerich') . '/services';
  $services = file_scan_directory($dir, '/.inc/', array(), 0);
  $options = array_map(function($service) {
    return $service->name;
  }, $services);

  $form['settings'] = array(
    '#type' => 'vertical_tabs',
  );

  $form['main_widget_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Main Widget Settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#group' => 'settings',
  );

  $form['custom_widget_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Custom Widget Settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#group' => 'settings',
    '#description' => '<strong>' . t('In order to edit the markup, you need to enable respective service in "Main Widget Settings".') . '</strong>',
  );

  $form['main_widget_settings']['title'] = array(
    '#type' => 'textfield',
    '#title' => t('Title'),
    '#description' => t('This is the title that appears above the share buttons.'),
    '#default_value' => !empty($item->title) ? $item->title : '',
  );

  // Use our dragable table theme function.
  $form['main_widget_settings']['services'] = array(
    '#theme' => 'sharerich_manage_services',
    '#tree' => TRUE,
  );

  foreach ($options as $service_name) {

    // Unique identifier for the service.
    $service_identifier = 'sharerich_' . $service_name;

    $form['main_widget_settings']['services'][$service_identifier]['service'] = array(
      '#markup' => $service_name,
    );

    $form['main_widget_settings']['services'][$service_identifier]['enabled'] = array(
      '#type' => 'checkbox',
      '#default_value' => $item->services[$service_identifier]['enabled'],
    );

    // Store the buttons markup along other data.
    $form['main_widget_settings']['services'][$service_identifier]['markup'] = array(
      '#type' => 'hidden',
      '#value' => sharerich_get_service_content($service_name, $item->machinename),
    );

    // The field contains sort info (weights).
    $form['main_widget_settings']['services'][$service_identifier]['weight'] = array(
      '#type' => 'weight',
      '#title' => t('Weight'),
      '#title_display' => 'invisible',
      '#default_value' => $item->services[$service_identifier]['weight'],
    );
  }

  // Sort the services.
  uasort($form['main_widget_settings']['services'], 'drupal_sort_weight');

  foreach ($options as $service_name) {
    $var_name = 'sharerich_custom_' . $service_name;

    $form['custom_widget_settings'][$var_name] = array(
      '#type' => 'textarea',
      '#title' => t('Custom code for %name', array('%name' => $service_name)),
      '#description' => '',
      '#default_value' => sharerich_get_service_content($service_name, $item->machinename),
        '#states' => array(
        'invisible' => array(
          ':input[name="services[sharerich_' . $service_name . '][enabled]"]' => array('checked' => FALSE),
        ),
      ),
    );
  }
}

function sharerich_ctools_export_ui_form_submit(&$form, &$form_state) {
  $services_values = &$form_state['values']['services'];
  uasort($services_values, 'drupal_sort_weight');

  // Rewrite the valuees from the custom service code (coming from default).
  foreach ($services_values as $key => &$service) {
    $custom_key = str_replace('sharerich_', 'sharerich_custom_', $key);

    if (empty($form_state['values'][$custom_key])) {
      continue;
    }

    $service['markup'] = $form_state['values'][$custom_key];
  }
}

// Theme function for sharerich_manage_services().
function theme_sharerich_manage_services($variables) {
  $form = $variables['form'];
  $rows = array();

  foreach (element_children($form) as $service_identifier) {
    $form[$service_identifier]['weight']['#attributes']['class'] = array('services-order-weight');
    $rows[] = array(
      'data' => array(
        array('class' => array('service-cross')),
        drupal_render($form[$service_identifier]['service']),
        drupal_render($form[$service_identifier]['enabled']),
        drupal_render($form[$service_identifier]['weight']),
      ),
      'class' => array('draggable'),
    );
  }

  $header = array('', t('Service'), t('Enabled/Disabled'), t('Weight'));
  $output = drupal_render($form['note']);
  $output .= theme('table', array('header' => $header, 'rows' => $rows, 'attributes' => array('id' => 'service-order')));
  $output .= drupal_render_children($form);

  drupal_add_tabledrag('service-order', 'order', 'sibling', 'services-order-weight');

  return $output;
}
