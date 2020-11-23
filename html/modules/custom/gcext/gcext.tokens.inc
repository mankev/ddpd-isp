<?php

/**
 * @file
 * Builds custom placeholder replacement tokens for this site.
 */

use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\tac_lite\Form\SchemeForm;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function gcext_token_info() {
  $types = [];
  $tokens = [];

  // tac_lite
  $tokens['current-user']['tac_term_ids'] = [
    'name' => t('TAC term ids from user'),
    'description' =>  t('A token to return tac lite tids of a user + delimited for views.'),
  ];


  return ['types' => $types, 'tokens' => $tokens];
}

/**
 * Implements hook_tokens().
 */
function gcext_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  // current-user tokens
  if ($type == 'current-user') {
    foreach ($tokens as $name => $original) {
      // Find the desired token by name.
      switch ($name) {
        case 'tac_term_ids':
          // this is in Data
          //$user = $data['user'];
          $config = SchemeForm::tacLiteConfig(1);
          //$user = \Drupal::currentUser();
          $user = $data['current-user'];
          $tids = array_filter(_tac_lite_user_tids($user, 1, $config));
          $replacements[$original] = implode('+', $tids);
          break;
      }
    }
  }

  return $replacements;
}

