<?php

namespace Drupal\gcext\Controller;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

class CheckCaseAccess extends ControllerBase {

  /**
   * Checks access for displaying time tab.
   */

  public function checkAccess(NodeInterface $node) {
    $currentUser = \Drupal::currentUser();
    return AccessResult::allowedif(($node->bundle() === 'case') && $currentUser->hasPermission('view time logs'));
  }
}
