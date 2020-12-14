<?php

namespace Drupal\workflow_progress_bar\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\workflow\Entity\WorkflowManager;
use Drupal\workflow\Entity\WorkflowState;

/**
 * Plugin implementation of the 'workflow_progress_bar' field formatter.
 *
 * @FieldFormatter(
 *   id = "workflow_progress_bar",
 *   label = @Translation("Workflow progress bar"),
 *   field_types = {
 *     "workflow"
 *   }
 * )
 */
class ProgressBarFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $defaults = [];
    //$wid = $this->getSetting('workflow');
    $states = WorkflowState::loadMultiple([], 'case_status');

    ///** @var \Drupal\workflow\Entity\WorkflowState $state */
    foreach ($states as $key => $state) {
     $defaults['progress_bar_color_' . $key] = '337ab7';
    }

    return $defaults + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $wid = $this->getSetting('workflow');
    $states = WorkflowState::loadMultiple([], $wid);

    /** @var \Drupal\workflow\Entity\WorkflowState $state */
    foreach ($states as $key => $state) {
      // Creating color field setting.
      $element['progress_bar_color_' . $key] = [
        '#title' => t('Color for: ' . $state->label()),
        '#type' => 'textfield',
        '#size' => 6,
        '#default_value' => $this->getSetting('progress_bar_color_' . $key),
        '#required' => TRUE,
      ];
    }

    return $element;
  }

  /**
   * Validate the color text field.
   */
  public function validate($element, FormStateInterface $form_state) {
    $value = $element['#value'];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = t('Color settings');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $allowed_values = [];

    $wid = $this->getSetting('workflow');
    $states = WorkflowState::loadMultiple([], $wid); 
    /** @var \Drupal\workflow\Entity\WorkflowState $state */
    foreach ($states as $key => $state) {
      // Creating color field setting.
      $allowed_values[$key] = $state->label();
    }
    $list_count = count($allowed_values);

    //$entity_type = $entity->getEntityTypeId();
    $field_name = $this->fieldDefinition->getName();
    $entity = $items->getEntity();
    $current_sid = WorkflowManager::getCurrentStateId($entity, $field_name);

    /* @var $current_state WorkflowState */
    $current_state = WorkflowState::load($current_sid);

    $elements = $this->getStateDetail($allowed_values, $list_count, $current_sid);

    return $elements;
  }

  /**
   * Helper function to get the state data.
   */
  protected function getStateData($allowed_values, $list_count, $current_sid) {
    // Array Loop Counter.
    $loop_count = 0;
    $state_data = array();
    $lowest_percent = (1 / $list_count) * 100;
    // Go through all allowed values.
    foreach ($allowed_values as $key => $value) {
      // If loop count is less than search position.
      $position = array_search($current_sid, array_keys($allowed_values));
      if ($loop_count < $position + 1) {
        // State.
        $state = (($loop_count + 1) / $list_count) * 100;
        // Add items.
        $state_data[] = array(
          'state' => $state,
          'name' => $value,
          'color' => '#' . $this->getSetting('progress_bar_color_' . $key),
          'lowest_percent' => $lowest_percent,
        );
      }
      ++$loop_count;
    }
    return $state_data;
  }

  /**
   * Helper function to get the element data for state.
   */
  protected function getStateDetail($allowed_values, $list_count, $current_sid) {
    $elements = [];
    // Get the state value for each row.
    $state = $this->getStateData($allowed_values, $list_count, $current_sid);
    $elements[0] = [
      '#theme' => 'workflow_progress_bar_format',
      '#state' => $state,
      '#attached' => array('library' => array('workflow_progress_bar/workflow-progress-bar')),
    ];

    return $elements;
  }

}
