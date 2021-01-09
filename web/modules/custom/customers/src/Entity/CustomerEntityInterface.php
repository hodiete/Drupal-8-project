<?php

namespace Drupal\customers\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Customers entities.
 *
 * @ingroup customers
 */
interface CustomerEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Customers name.
   *
   * @return string
   *   Name of the Customers.
   */
  public function getName();

  /**
   * Sets the Customers name.
   *
   * @param string $name
   *   The Customers name.
   *
   * @return \Drupal\customers\Entity\CustomerEntityInterface
   *   The called Customers entity.
   */
  public function setName($name);

  /**
   * Gets the Customers creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Customers.
   */
  public function getCreatedTime();

  /**
   * Sets the Customers creation timestamp.
   *
   * @param int $timestamp
   *   The Customers creation timestamp.
   *
   * @return \Drupal\customers\Entity\CustomerEntityInterface
   *   The called Customers entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Customers revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Customers revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\customers\Entity\CustomerEntityInterface
   *   The called Customers entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Customers revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Customers revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\customers\Entity\CustomerEntityInterface
   *   The called Customers entity.
   */
  public function setRevisionUserId($uid);

}
