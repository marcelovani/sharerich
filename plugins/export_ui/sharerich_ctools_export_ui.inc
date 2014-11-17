<?php

/**
 * @file
 * A Ctools Export UI plugin for Sharerich.
 */
$plugin = array(
  'schema' => 'sharerich_sets', // As defined in hook_schema().
  'access' => 'administer sharerich', // Define a permission users must have to access these pages.
  // Define the menu item.
  'menu' => array(
    'menu item' => 'sharerich',
    'menu title' => 'Sharerich',
    'menu description' => 'Administer Sharerich sets.',
  ),
  // Define user interface texts.
  'title singular' => t('set'),
  'title plural' => t('sets'),
  'title singular proper' => t('Sharerich set'),
  'title plural proper' => t('Sharerich sets'),
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
  $services = sharerich_get_services();

  $form['settings'] = array(
    '#type' => 'vertical_tabs',
  );

  $form['default_sets'] = array(
    '#type' => 'fieldset',
    '#title' => t('Default Sets'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#group' => 'settings',
  );

  $form['custom_sets'] = array(
    '#type' => 'fieldset',
    '#title' => t('Custom Sets'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#group' => 'settings',
    '#description' => '<strong>' . t('In order to edit the markup, you need to enable the services in the "Default Sets tab".') . '</strong>',
  );

  $form['default_sets']['title'] = array(
    '#type' => 'textfield',
    '#title' => t('Title'),
    '#description' => t('This is the title that appears above the share buttons.'),
    '#default_value' => !empty($item->title) ? $item->title : '',
  );

  $form['default_sets']['block'] = array(
    '#type' => 'checkbox',
    '#title' => t('Create a block for this button set.'),
    '#description' => '',
    '#default_value' => $item->block,
  );
  
  // Use our dragable table theme function.
  $form['default_sets']['services'] = array(
    '#theme' => 'sharerich_manage_services',
    '#tree' => TRUE,
  );

  foreach ($services as $service_name) {

    $form['default_sets']['services'][$service_name]['service'] = array(
      '#markup' => $service_name,
    );

    $form['default_sets']['services'][$service_name]['enabled'] = array(
      '#type' => 'checkbox',
      '#default_value' => $item->services[$service_name]['enabled'],
    );

    // Store the buttons markup along other data.
    $form['default_sets']['services'][$service_name]['markup'] = array(
      '#type' => 'hidden',
      '#value' => sharerich_get_service_content($service_name, $item->machinename),
    );

    // The field contains sort info (weights).
    $form['default_sets']['services'][$service_name]['weight'] = array(
      '#type' => 'weight',
      '#title' => t('Weight'),
      '#title_display' => 'invisible',
      '#default_value' => $item->services[$service_name]['weight'],
    );
  }

  // Sort the services.
  uasort($form['default_sets']['services'], 'drupal_sort_weight');

  // Custom widgets.
  foreach ($services as $service_name) {
    $var_name = 'sharerich_custom_' . $service_name;

    $form['custom_sets'][$var_name] = array(
      '#type' => 'textarea',
      '#title' => t('Custom code for %name', array('%name' => $service_name)),
      '#description' => '',
      '#default_value' => sharerich_get_service_content($service_name, $item->machinename),
      '#states' => array(
        'invisible' => array(
          ':input[name="services[' . $service_name . '][enabled]"]' => array('checked' => FALSE),
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
    $custom_key = 'sharerich_custom_' . $key;

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
