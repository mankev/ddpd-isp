<?php

namespace Drupal\gcext\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\gcext\Util\ConfigUtil;

/**
 * Configure example settings for this site.
 */
class GcextSettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gcext_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'gcext.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('gcext.settings');

    $form['org_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Organization name'),
      '#description' => $this->t('Organization name to show on by-line (under the H1).'),
      '#required' => TRUE,
      '#default_value' => is_null($config->get('org_name')) ? 'DrupalWxT' : $config->get('org_name'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $org_value = $form_state->getValue('org_name');
    $drone_value = $form_state->getValue('drone_default_contacts');
    $interval_value = $form_state->getValue('news_check_interval');
    $url_value = $form_state->getValue('news_check_url');
    $this->config('gcext.settings')
      ->set('org_name', $org_value)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
