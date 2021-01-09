<?php

/**
 * @file
 * Contains customer_entities.page.inc.
 *
 * Page callback for Customer entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Customer templates.
 *
 * Default template: customer_entities.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_customer_entities(array &$variables) {
  // Fetch CustomerEntity Entity Object.
  $customer_entities = $variables['elements']['#customer_entities'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
