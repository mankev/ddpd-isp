<?php

namespace Drupal\rules_webform\Plugin\DataType;

use Drupal\Core\TypedData\Plugin\DataType\Map;

/**
 * The "webform_fields_unchanged" data type.
 *
 * @ingroup typed_data
 *
 * @DataType(
 *   id = "webform_fields_unchanged",
 *   label = @Translation("Webform Fields Unchanged"),
 *   definition_class = "Drupal\rules_webform\WebformFieldsUnchangedDataDefinition"
 * )
 */
class WebformFieldsUnchanged extends Map {
}
