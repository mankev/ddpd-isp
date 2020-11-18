<?php

namespace Drupal\gcext\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'text' formatter.
 *
 * @FieldFormatter(
 *   id = "viya_chart_formatter",
 *   label = @Translation("SAS Viya Chart Formatter"),
 *   field_types = {
 *     "text", "string"
 *   }
 * )
 */
class ViyaChartFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays a SAS Viya Chart.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $output = [
      '#type' => 'inline_template',
      '#template' => '<sas-report-object objectName="ve16" authenticationType="guest" url="https://viya.openplus.ca" reportUri="{{ chartpath }}"></sas-report-object>',
      '#context' => [
        'chartpath' => $items[0]->value,
      ],
    ];

    return $output;
  }

  /**
   * {@inheritdoc}
  */
  public static function defaultSettings() {
    return [
      'viya_url' => 'https://viya.openplus.ca',
    ] + parent::defaultSettings();
  }

}
