<?php

namespace Drupal\project_progress_bar\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\workflow\Entity\WorkflowManager;
use Drupal\workflow\Entity\WorkflowState;
use Drupal\workflow\Entity\WorkflowTransition;

/**
 * Plugin implementation of the 'workflow_progress_bar' field formatter.
 *
 * @FieldFormatter(
 *   id = "project_progress_bar",
 *   label = @Translation("Project progress bar"),
 *   field_types = {
 *     "date"
 *   }
 * )
 */
class ProgressBarFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $defaults = [
      'progress_bar_color' => [],
      'exclude_dates' => [],
    ];

    return $defaults + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $colors = $this->getSetting('progress_bar_color');
    $options = [];

    $entityFieldManager = \Drupal::service('entity_field.manager');
    $fields = $entityFieldManager->getFieldDefinitions('node', 'project');
    $datefields = [];
    foreach ($fields as $field) {
      if ($field instanceof Drupal\field\Entity\FieldConfig) {
        if ($field->getType() == 'datetime') {
          $datefields[$field->getName()] = $field->getLabel(); 
        }
      }
    }

    /** @var \Drupal\workflow\Entity\WorkflowState $state */
    foreach ($datefields as $key => $label) {
      // Creating color field setting.
      $element['progress_bar_color'][$key] = [
        '#title' => t('Color for ' . $label),
        '#type' => 'textfield',
        '#size' => 6,
        '#default_value' => $colors[$key],
      ];
  
      $options[$key] = $label;
    }

    $element['exclude_dates'] = [
      '#title' => t('Exclude the following date fields'),
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $this->getSetting('exclude_dates'),
    ];

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
    $colors = implode(', ', array_filter($this->getSetting('progress_bar_color')));
    $summary[] = t('Color settings: @colors', ['@colors' => $colors]);

    $dates = implode(', ', array_filter($this->getSetting('exclude_dates')));
    $summary[] = t('Exclude dates: @dates', ['@dates' => $dates]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $exclude_dates = array_filter($this->getSetting('exclude_dates'));

    $entity = $items->getEntity();
    $entity_type = $entity->getEntityTypeId();
    $field_name = $this->fieldDefinition->getName();
    $current_sid = WorkflowManager::getCurrentStateId($entity, $field_name);
    
    // load the current state to use as a lable on the progressbar
    /* @var $current_state WorkflowState */
    $current_state = WorkflowState::load($current_sid);

    $start_date = time();
    $field_project = $entity->get('field_project')->getValue();
    if (isset($field_project[0]['target_id'])) {
      // get the estimated project completion date from the project
      $project = \Drupal::entityTypeManager()->getStorage('node')->load($field_project[0]['target_id']); 
      $end = $project->get('field_date_complete')->getValue();
      $end_date = strtotime($end[0]['value']);
      // @TODO have a default if project completion date not set or show nothing at all
   
      // get the due date from the case 
      $due_date = strtotime($items[0]['value']);
    
      $elements = $this->formatDetail($start_date, $end_date, $due_date, $current_state);
    }

    return $elements;
  }

  /**
   * Helper function to get the data.
   */
  protected function getData($start_date, $end_date, $due_date, $list_count);
    // Array Loop Counter.
    $loop_count = 0;
    $state_data = array();
    $lowest_percent = (1 / $list_count) * 100;
    $colors = $this->getSetting('progress_bar_color');

    while ($loop_count <= $list_count) {
      $state = (($loop_count + 1) / $list_count) * 100;

      // current month =  strtotime (start date + loop_count months)
      // if months between $due_date and current month is zero add time otherwise time is null
      $due_period = $this->diffDates($start_date, $due_period);
      $due = $loop_count ? \Drupal::service('date.formatter')->format($due_date, $key), 'short') :  NULL;
    
      // Add items.
      $state_data[] = array(
        'state' => $state,
        //'name' => $current_state->label(),
        'color' => '#f2f2f2',  // @TODO change colour on milestones 
        'lowest_percent' => $lowest_percent,
        'time' => $due, 
        'is_first' => $loop_count == 0 ? 1 : 0,
      );
      ++$loop_count;
    }

    return $state_data;
  }

  /**
   * Helper function to get the element data for state.
   */
  protected function formatDetail($start_date, $end_date, $due_date, $current_state);
    $elements = [];

    // get number of months between now and end date for progress bar
    $list_count = $this->diffDates($start_date, $end_date);

    $state = $this->getData($start_date, $end_date, $due_date, $list_count);
    $elements[0] = [
      '#theme' => 'project_progress_bar_format',
      '#state' => $state,
      '#label' => $current_state->label(),
      '#attached' => array('library' => array('project_progress_bar/project-progress-bar')),
    ];

    return $elements;
  }

  /**
   * Helper function to return the number of months between two dates 
   */
  protected function diffDates($start_date, $end_date) {
    $year1 = date('Y', $start_date);
    $year2 = date('Y', $end_date);
    $month1 = date('m', $start_date);
    $month2 = date('m', $end_date);

    return (($year2 - $year1) * 12) + ($month2 - $month1);
  }

  /**
   * Helper function to get transition time for a state
   */
  protected function getTransitionTime($entity_type, $entity_id, $field_name = '', $state = '', $transition_type = 'workflow_transition') {

    /** @var $query \Drupal\Core\Entity\Query\QueryInterface */
    $query = \Drupal::entityQuery($transition_type)
      ->condition('entity_type', $entity_type)
      ->sort('timestamp', 'DESC')
      ->range(0,1)
      ->addTag($transition_type);
    if (!empty($entity_ids)) {
      $query->condition('entity_id', $entity_ids, 'IN');
    }
    if ($field_name != '') {
      $query->condition('field_name', $field_name, '=');
    }
    if ($state != '') {
      $query->condition('to_sid', $state, '=');
    }

    $ids = $query->execute();
    if (!empty($ids)) {
      $transition_id = array_pop($ids);
      $transition = WorkflowTransition::load($transition_id);
      if ($transition instanceof WorkflowTransition) {
        $timestamp = $transition->get('timestamp')->getValue();
        if (!empty($timestamp) && isset($timestamp[0]['value'])) {
          return $timestamp[0]['value'];
        }
      }
    }
  }

}
