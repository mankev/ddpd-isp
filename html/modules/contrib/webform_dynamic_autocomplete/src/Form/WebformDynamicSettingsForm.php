<?php

namespace Drupal\webform_dynamic_autocomplete\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure WebformDynamic settings for this module.
 */
class WebformDynamicSettingsForm extends ConfigFormBase {

  /** 
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'webform_dynamic.settings';

  /** 
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'webform_dynamic_admin_settings';
  }

  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['webform_dynamic_endpoint_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Endpoint Url'),
      '#required' => true,
      '#default_value' => $config->get('webform_dynamic_endpoint_url'),
    ];  

    $form['webform_dynamic_query_parameter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Query Parameter'),
      '#required' => true,
      '#default_value' => $config->get('webform_dynamic_query_parameter'),
    ]; 

    return parent::buildForm($form, $form_state);
  }

  /** 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('webform_dynamic_endpoint_url', $form_state->getValue('webform_dynamic_endpoint_url'))
      ->set('webform_dynamic_query_parameter', $form_state->getValue('webform_dynamic_query_parameter'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
