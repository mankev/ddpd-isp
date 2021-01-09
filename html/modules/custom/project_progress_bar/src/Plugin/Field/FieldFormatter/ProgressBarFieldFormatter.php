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
 * Plugin implementation of the 'project_progress_bar' field formatter.
 *
 * @FieldFormatter(
 *   id = "project_progress_bar",
 *   label = @Translation("Project progress bar"),
 *   field_types = {
 *     "datetime"
 *   }
 * )
 */
class ProgressBarFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $defaults = [
      'start_state' => NULL,
      'end_state' => NULL,
    ];

    return $defaults + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $start_state = $this->getSetting('start_state');
    $end_state = $this->getSetting('end_state');
    $options = [];

    // @TODO make this more generic
    $wid = 'case_status';
    $states = WorkflowState::loadMultiple([], $wid);

    /** @var \Drupal\workflow\Entity\WorkflowState $state */
    foreach ($states as $key => $state) {
      $options[$key] = $state->label(); 
    }

    $element['start_state'] = [
      '#title' => t('Starting state'),
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $start_state,
    ];

    $element['end_state'] = [
      '#title' => t('Completed state'),
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $end_state,
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
    $wid = 'case_status';
    $states = WorkflowState::loadMultiple([], $wid);
    $start = $states[$this->getSetting('start_state')];
    $summary[] = t('Start state: @start', ['@start' => $start->label()]);

    $end = $states[$this->getSetting('end_state')];
    $summary[] = t('Complete state: @end', ['@end' => $end->label()]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $start_state = $this->getSetting('start_state');
    $end_state = $this->getSetting('end_state');

    $entity = $items->getEntity();
    $entity_type = $entity->getEntityTypeId();
    $field_name = 'field_case_status';   // @TODO use wf code to determine wf field
    $current_sid = WorkflowManager::getCurrentStateId($entity, $field_name);

    // get transition timestamps for start and complete
    $start_trans_date = $this->getTransitionTime($entity_type, $entity->id(), $field_name, $start_state);
    $end_trans_date = $this->getTransitionTime($entity_type, $entity->id(), $field_name, $end_state);

    // load the current state to use as a lable on the progressbar
    /* @var $current_state WorkflowState */
    $current_state = WorkflowState::load($current_sid);

    $start_date = time();
    $field_project = $entity->get('field_project')->getValue();
    if (isset($field_project[0]['target_id'])) {
      // get the estimated project completion date from the project
      $project = \Drupal::entityTypeManager()->getStorage('node')->load($field_project[0]['target_id']); 
      $end = $project->get('field_project_complete')->getValue();

      // if project date is set we will generate the progressbar, otherwise leave it empty
      if (isset($end[0]['value'])) {
        $end_date = strtotime($end[0]['value']);
        // if a due date is set
        if (isset($items[0]) && !empty($items[0])) {
          $due = $items[0]->getValue();
          $due_date = strtotime($due['value']);
        }
        else {
          // otherwise we will assume the due date is the same as project completion date
          $due_date = $end_date;
        }
        $elements = $this->formatDetail($start_date, $end_date, $due_date, $current_state, $start_trans_date, $end_trans_date);
      }
    }

    return $elements;
  }

  /**
   * Helper function to get the data.
   * 
   * Some thoughts on what to add:
   *  - change the start date from now() to a field on the project? Maybe project node creation date?
   *  - change the colour / name on the bar prior to start state being reached
   *  - change the colour / name on the bar after complete state reached
   */
  protected function getData($start_date, $end_date, $due_date, $list_count, $start_trans_date, $end_trans_date) {
    // Array Loop Counter.
    $loop_count = 0;
    $state_data = array();
    $lowest_percent = (1 / $list_count) * 100;
    $colors = $this->getSetting('progress_bar_color');
    $due_period = $this->diffDates($start_date, $due_date);

    while ($loop_count <= $due_period) {
      $state = (($loop_count + 1) / $list_count) * 100;
      $due = ($due_period == $loop_count) ? \Drupal::service('date.formatter')->format($due_date, 'html_date') :  NULL;

      // Add items.
      $state_data[] = array(
        'state' => $state,
        //'name' => $current_state->label(),
        'color' => '#26374a',  // @TODO change colour on milestones 
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
  protected function formatDetail($start_date, $end_date, $due_date, $current_state, $start_trans_date, $end_trans_date) {
    $elements = [];
    $end_state = $this->getSetting('end_state');

    // get number of months between now and end date for progress bar
    $list_count = $this->diffDates($start_date, $end_date);

    $state = $this->getData($start_date, $end_date, $due_date, $list_count, $start_trans_date, $end_trans_date);
    $elements[0] = [
      '#theme' => 'project_progress_bar_format',
      '#state' => $state,
      '#label' => $current_state->label(),
      '#is_complete' => ($current_state->id() == $end_state) ? TRUE : FALSE,
      '#attached' => array('library' => array('project_progress_bar/project-progress-bar')),
    ];
//dpm($elements);
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
