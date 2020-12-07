<?php

namespace Drupal\gcext\Controller;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;

/**
 * Checks access for displaying time tab.
 */
class CheckCaseAccess extends ControllerBase {

  public function checkAccess(NodeInterface $node) {
    return AccessResult::allowedif($node->bundle() === 'case');
  }
}
