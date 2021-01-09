<?php

namespace Drupal\customer_entity\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Customer entity.
 *
 * @ingroup customer_entity
 *
 * @ContentEntityType(
 *   id = "customer_entities",
 *   label = @Translation("Customer"),
 *   handlers = {
 *     "storage" = "Drupal\customer_entity\CustomerEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\customer_entity\CustomerEntityListBuilder",
 *     "views_data" = "Drupal\customer_entity\Entity\CustomerEntityViewsData",
 *     "translation" = "Drupal\customer_entity\CustomerEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\customer_entity\Form\CustomerEntityForm",
 *       "add" = "Drupal\customer_entity\Form\CustomerEntityForm",
 *       "edit" = "Drupal\customer_entity\Form\CustomerEntityForm",
 *       "delete" = "Drupal\customer_entity\Form\CustomerEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\customer_entity\CustomerEntityHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\customer_entity\CustomerEntityAccessControlHandler",
 *   },
 *   base_table = "customer_entities",
 *   data_table = "customer_entities_field_data",
 *   revision_table = "customer_entities_revision",
 *   revision_data_table = "customer_entities_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer customer entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "customer_name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/customer_entities/{customer_entities}",
 *     "add-form" = "/admin/structure/customer_entities/add",
 *     "edit-form" = "/admin/structure/customer_entities/{customer_entities}/edit",
 *     "delete-form" = "/admin/structure/customer_entities/{customer_entities}/delete",
 *     "version-history" = "/admin/structure/customer_entities/{customer_entities}/revisions",
 *     "revision" = "/admin/structure/customer_entities/{customer_entities}/revisions/{customer_entities_revision}/view",
 *     "revision_revert" = "/admin/structure/customer_entities/{customer_entities}/revisions/{customer_entities_revision}/revert",
 *     "revision_delete" = "/admin/structure/customer_entities/{customer_entities}/revisions/{customer_entities_revision}/delete",
 *     "translation_revert" = "/admin/structure/customer_entities/{customer_entities}/revisions/{customer_entities_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/customer_entities",
 *   },
 *   field_ui_base_route = "customer_entities.settings"
 * )
 */
class CustomerEntity extends EditorialContentEntityBase implements CustomerEntityInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly,
    // make the customer_entities owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Customer entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
   
    $fields['customer_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Customer id'))
      ->setDescription(t('The ID of the customer'))
      ->setRevisionable(TRUE)
       -> setDisplayOptions('form', [
        'type' => 'number',
         'weight' => -4,
      ])
      -> setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'default_formatter',
        'weight' => -4
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['customer_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Customer name'))
      ->setDescription(t('The name of the customer'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['customer_balance'] = BaseFieldDefinition::create('float')
      ->setLabel(t('Balance'))
      ->setDescription(t('Customer balance'))
      ->setRevisionable(TRUE)
          -> setDisplayOptions('form', [
        'type' => 'number',
         'weight' => -4,
      ])
      -> setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'default_formatter',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the Customer is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
