<?php

namespace Drupal\workflows_field\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the workflows field.
 */
class WorkflowsFieldContraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates an instance of WorkflowsFieldContraintValidator.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function validate($field, Constraint $constraint) {
    $entity = $field->getEntity();
    $workflow_type = $field->getWorkflow()->getTypePlugin();

    // An entity can start its life in any state.
    if (!isset($field->value) || $entity->isNew()) {
      return;
    }

    $original_entity = $this->entityTypeManager->getStorage($entity->getEntityTypeId())->loadUnchanged($entity->id());
    if (!$entity->isDefaultTranslation() && $original_entity->hasTranslation($entity->language()->getId())) {
      $original_entity = $original_entity->getTranslation($entity->language()->getId());
    }
    $previous_state = $original_entity->{$field->getFieldDefinition()->getName()}->value;

    // The state does not have to change.
    if ($previous_state === $field->value) {
      return;
    }

    if (!$workflow_type->hasTransitionFromStateToState($previous_state, $field->value)) {
      $this->context->addViolation($constraint->message, [
        '%state' => $field->value,
        '%previous_state' => $previous_state,
      ]);
    }
  }

}
