<?php

/**
 * @file
 * Contains \Drupal\sharerich\Form\AdminSettingsForm.
 */

namespace Drupal\sharerich\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AdminSettingsForm.
 *
 * @package Drupal\sharerich\Form
 */
class AdminSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'sharerich.settings'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('sharerich.settings');
    //@todo delete this
    $form['profile_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Profile name'),
      '#description' => $this->t('Video sharerich name'),
      '#default_value' => $config->get('profile_name'),
    );
    //@todo delete
    $form['enable_transcoding'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable transcoding'),
      '#description' => $this->t('Enables video transcoding'),
      '#default_value' => $config->get('enable_transcoding'),
    );

    $form['social'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Social networks'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );
    $form['social']['sharerich_facebook_app_id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Facebook App ID'),
      '#description' => $this->t('You need to have an App ID, which you can get from Facebook.'),
      '#default_value' => $config->get('sharerich_facebook_app_id'),
    );
    $form['social']['sharerich_facebook_site_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Facebook Site URL'),
      '#description' => $this->t('You need to have an App ID, which you can get from Facebook.'),
      '#default_value' => $config->get('sharerich_facebook_site_url'),
    );
    $form['social']['sharerich_youtube_username'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('YouTube Username'),
      '#description' => $this->t('Enter your YouTube username in order for the social button to link to your YouTube channel.'),
      '#default_value' => $config->get('sharerich_youtube_username'),
    );
    $form['social']['sharerich_github_username'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Github Username'),
      '#description' => $this->t('Enter your Github username in order for the social button to link to your Github profile.'),
      '#default_value' => $config->get('sharerich_github_username'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('sharerich.settings')
      ->set('profile_name', $form_state->getValue('profile_name'))//@todo remove
      ->set('enable_transcoding', $form_state->getValue('enable_transcoding')) //@todo remove
      ->set('sharerich_facebook_app_id', $form_state->getValue('sharerich_facebook_app_id'))
      ->set('sharerich_facebook_site_url', $form_state->getValue('sharerich_facebook_site_url'))
      ->set('sharerich_youtube_username', $form_state->getValue('sharerich_youtube_username'))
      ->set('sharerich_github_username', $form_state->getValue('sharerich_github_username'))
      ->save();
  }

}
