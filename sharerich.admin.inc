<?php

/**
 * @file
 * Admin page.
 */

/**
 * Implements hook_form().
 */
function sharerich_admin_form() {
  $form['sharerich_facebook_app_id'] = array(
    '#type' => 'textfield',
    '#title' => t('Facebook App ID'),
    '#description' => t('You need to have an App ID, which you can get from Facebook.'),
    '#default_value' => variable_get('sharerich_facebook_app_id'),
  );
  $form['sharerich_facebook_site_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Facebook Site URL'),
    '#description' => t('You need to have an App ID, which you can get from Facebook.'),
    '#default_value' => variable_get('sharerich_facebook_site_url'),
  );
  $form['sharerich_youtube_username'] = array(
    '#type' => 'textfield',
    '#title' => t('YouTube Username'),
    '#description' => t('Enter your YouTube username in order for the social button to link to your YouTube channel.'),
    '#default_value' => variable_get('sharerich_youtube_username'),
  );
  $form['sharerich_github_username'] = array(
    '#type' => 'textfield',
    '#title' => t('Github Username'),
    '#description' => t('Enter your Github username in order for the social button to link to your Github profile.'),
    '#default_value' => variable_get('sharerich_github_username'),
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

  return system_settings_form($form);
}
