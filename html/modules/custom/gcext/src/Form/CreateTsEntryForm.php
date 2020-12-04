<?php
namespace Drupal\gcext\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CreateTsEntryForm.
 *
 * @package Drupal\gcext\Form
 */
class CreateTsEntryForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'create_tsentry_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $args = array()) {

    //$user = \Drupal\user\Entity\User::load($uid);
  
    $node = $args['node'];

    $form['nid'] = array(
      '#type' => 'hidden',
      '#value' => $node->id(),
    );

    // @TODO add team?

    $form['add_tsentry'] = [ 
      '#type' => 'details',
      '#title' => '<i class="fa fa-clock-o" aria-hidden="true"></i> ' . t('Log time'),
      //'#open' => TRUE,
    ];

    $form['add_tsentry']['summary'] = [ 
      '#type' => 'textarea',
      '#title' => t('Activity summary'),
      '#rows' => 3,
      '#required' => TRUE,
    ];

    $form['add_tsentry']['hours'] = [ 
      '#type' => 'textfield',
      '#title' => t('Hours'),
      '#size' => 5,
      '#maxlength' => 5,
      '#number_type' => 'decimal',
      '#field_name' => 'add_tsentry',
      '#input' => TRUE,
      '#required' => TRUE,
    ];
  
    $form['add_tsentry']['actions']['#type'] = 'actions';
    $form['add_tsentry']['actions']['submit'] = [ 
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $nid = $form_state->getValue('nid');
    $uid = \Drupal::currentUser()->id();

    $title = substr($form_state->getValue('summary'), 0, 50);
    $node = \Drupal::entityTypeManager()->getStorage('node')->create(['type' => 'entry']);
    $node->set('title', $title);
    $node->set('field_ts_summary', $form_state->getValue('summary'));
    $node->set('field_ts_hours', $form_state->getValue('hours'));
    $node->set('field_ts_case', $nid);
    $node->status = 1;
    $node->uid = $uid;
    $node->enforceIsNew();
    $node->save();
  
    \Drupal::messenger()->addMessage($this->t("Time entry was successfully created."));
  }

}
