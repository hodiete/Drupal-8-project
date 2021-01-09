<?php

namespace Drupal\customers;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\customers\Entity\CustomerEntityInterface;

/**
 * Defines the storage handler class for Customers entities.
 *
 * This extends the base storage class, adding required special handling for
 * Customers entities.
 *
 * @ingroup customers
 */
interface CustomerEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Customers revision IDs for a specific Customers.
   *
   * @param \Drupal\customers\Entity\CustomerEntityInterface $entity
   *   The Customers entity.
   *
   * @return int[]
   *   Customers revision IDs (in ascending order).
   */
  public function revisionIds(CustomerEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Customers author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Customers revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\customers\Entity\CustomerEntityInterface $entity
   *   The Customers entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(CustomerEntityInterface $entity);

  /**
   * Unsets the language for all Customers with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
