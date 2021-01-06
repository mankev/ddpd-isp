<?php

namespace Drupal\gcext\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides the case number (NID) in a block.
 *
 * @Block(
 *   id = "op_case_number",
 *   admin_label = @Translation("Case number"),
 *   category = @Translation("Gcext"),
 *   context = {
 *     "node" = @ContextDefinition("entity:node")
 *   }
 * )
 */
class CaseNumber extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->getContextValue('node');
    $build = [];

    // Node context.
    if (is_object($node)) {

      $caseNumber = $node->id();
      if ($caseNumber) {
        $build['case_number']['#prefix'] = '<span class="case-number-wrapper"><div class="case-number-label">' . t('Case number: ') . '</span><span class="case-number">';
        $build['case_number']['#markup'] = $caseNumber;
        $build['case_number']['#suffix'] = '</span></span';
      }
    }

    return $build;
  }

}
