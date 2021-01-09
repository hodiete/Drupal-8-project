<?php

namespace Drupal\customers\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Customers type entity.
 *
 * @ConfigEntityType(
 *   id = "customer_entity_type",
 *   label = @Translation("Customers type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\customers\CustomerEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\customers\Form\CustomerEntityTypeForm",
 *       "edit" = "Drupal\customers\Form\CustomerEntityTypeForm",
 *       "delete" = "Drupal\customers\Form\CustomerEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\customers\CustomerEntityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "customer_entity_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "customer_entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/customer_entity_type/{customer_entity_type}",
 *     "add-form" = "/admin/structure/customer_entity_type/add",
 *     "edit-form" = "/admin/structure/customer_entity_type/{customer_entity_type}/edit",
 *     "delete-form" = "/admin/structure/customer_entity_type/{customer_entity_type}/delete",
 *     "collection" = "/admin/structure/customer_entity_type"
 *   }
 * )
 */
class CustomerEntityType extends ConfigEntityBundleBase implements CustomerEntityTypeInterface {

  /**
   * The Customers type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Customers type label.
   *
   * @var string
   */
  protected $label;

}
