<?php

namespace Drupal\Tests\workflows_field\Kernel;

use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\workflows\Entity\Workflow;
use Drupal\workflows_field\Plugin\Field\FieldType\WorkflowsFieldItem;

/**
 * Test the workflows field.
 *
 * @group workflows_field
 */
class WorkflowsFieldTest extends WorkflowsTestBase {

  /**
   * @covers \Drupal\workflows_field\Plugin\Validation\Constraint\WorkflowsFieldContraint
   * @covers \Drupal\workflows_field\Plugin\Validation\Constraint\WorkflowsFieldContraintValidator
   */
  public function testWorkflowsConstraint() {
    $node = Node::create([
      'title' => 'Foo',
      'type' => 'project',
      'field_status' => 'in_discussion',
    ]);
    $node->save();

    // Same state does not cause a violation.
    $node->field_status->value = 'in_discussion';
    $violations = $node->validate();
    $this->assertCount(0, $violations);

    // A valid state does not cause a violation.
    $node->field_status->value = 'approved';
    $violations = $node->validate();
    $this->assertCount(0, $violations);

    // Violation exists during invalid transition.
    $node->field_status->value = 'planning';
    $violations = $node->validate();
    $this->assertCount(1, $violations);
    $this->assertEquals('No transition exists to move from <em class="placeholder">in_discussion</em> to <em class="placeholder">planning</em>.', $violations[0]->getMessage());
  }

  /**
   * Test the implementation of OptionsProviderInterface.
   */
  public function testOptionsProvider() {
    $node = Node::create([
      'title' => 'Foo',
      'type' => 'project',
      'field_status' => 'in_discussion',
    ]);
    $node->save();

    $this->assertEquals([
      'implementing' => 'Implementing',
    'approved' => 'Approved',
    'rejected' => 'Rejected',
    'planning' => 'Planning',
    'in_discussion' => 'In Discussion',
    ], $node->field_status[0]->getPossibleOptions());
    $this->assertEquals([
      'approved' => 'Approved',
      'rejected' => 'Rejected',
      'in_discussion' => 'In Discussion'
    ], $node->field_status[0]->getSettableOptions());

    $this->assertEquals([
      'implementing',
      'approved',
      'rejected',
      'planning',
      'in_discussion',
    ], $node->field_status[0]->getPossibleValues());
    $this->assertEquals([
      'approved',
      'rejected',
      'in_discussion',
    ], $node->field_status[0]->getSettableValues());
  }

  /**
   * @covers \Drupal\workflows_field\Plugin\Field\FieldType\WorkflowsFieldItem
   */
  public function testFieldType() {
    $node = Node::create([
      'title' => 'Foo',
      'type' => 'project',
      'field_status' => 'in_discussion',
    ]);
    $node->save();

    // Test the dependencies calculation.
    $this->assertEquals([
      'config' => [
        'workflows.workflow.bureaucracy_workflow',
      ],
    ], WorkflowsFieldItem::calculateStorageDependencies($node->field_status->getFieldDefinition()->getFieldStorageDefinition()));

    // Test the getWorkflow method.
    $this->assertEquals('bureaucracy_workflow', $node->field_status[0]->getWorkflow()->id());
  }

  /**
   * @covers \Drupal\workflows_field\Plugin\WorkflowType\WorkflowsField
   */
  public function testWorkflowType() {
    // Test the initial state based on the config, despite the state weights.
    $type = Workflow::load('bureaucracy_workflow')->getTypePlugin();
    $this->assertEquals('in_discussion', $type->getInitialState()->id());
  }

}
