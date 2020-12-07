<?php

namespace Drupal\gcext\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a add timesheet entry Block.
 *
 * @Block(
 *   id = "create_tsentry_block",
 *   admin_label = @Translation("Create timesheet entry block"),
 *   category = @Translation("GCExt"),
 *   context = {
 *     "node" = @ContextDefinition("entity:node")
 *   }
 * )
 */
class CreateTsEntryBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $currentUser = \Drupal::currentUser();
    if (!$currentUser->hasPermission('create time log')) {
      return [];
    }

    $node = $this->getContextValue('node');
    $form = \Drupal::formBuilder()->getForm('\Drupal\gcext\Form\CreateTsEntryForm', array('node' => $node));

    return $form;
  }

}
