<?php

namespace Drupal\workflows_field\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint for the workflows field.
 *
 * @Constraint(
 *   id = "WorkflowsFieldConstraint",
 *   label = @Translation("WorkflowsFieldConstraint provider constraint", context = "Validation"),
 * )
 */
class WorkflowsFieldContraint extends Constraint {

  /**
   * Message displayed during an invalid transition.
   *
   * @var string
   */
  public $message = 'No transition exists to move from %previous_state to %state.';

}
