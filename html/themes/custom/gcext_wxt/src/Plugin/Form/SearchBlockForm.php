<?php

namespace Drupal\gcext_wxt\Plugin\Form;

use Drupal\bootstrap\Bootstrap;
use Drupal\bootstrap\Plugin\Form\FormBase;
use Drupal\bootstrap\Utility\Element;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter().
 *
 * @BootstrapForm("custom_search_block_form")
 */
class SearchBlockForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function alterFormElement(Element $form, FormStateInterface $form_state, $form_id = NULL) {
    // @codingStandardsIgnoreStart
    /** @var \Drupal\wxt_library\LibraryService $wxt */
    $wxt = \Drupal::service('wxt_library.service_wxt');
    // @codingStandardsIgnoreEnd
    $wxt_active = $wxt->getLibraryName();
    $form->keys->setProperty('input_group_button', FALSE);
    if ($wxt_active == 'gcweb' || $wxt_active == 'gcweb_legacy') {
      $form->actions->submit->addClass('btn-primary');
      $form->actions->submit->setProperty('icon', Bootstrap::glyphicon('search'));
      $form->actions->submit->setProperty('icon_only', FALSE);
      $form->keys->setProperty('input_group_button', FALSE);
    }
    else {
      $form->submit_container->submit->addClass('btn-default');
      $form->submit_container->submit->setProperty('icon', Bootstrap::glyphicon(''));
      $form->submit_container->submit->unsetProperty('icon_only');
    }
  }

}

