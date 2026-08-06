<?php
namespace Aheadworks\RewardPoints\Api\Data;

/**
 * Interface StorefrontLabelsEntityInterface
 *
 * @package Aheadworks\RewardPoints\Api\Data
 * @api
 */
interface StorefrontLabelsEntityInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const LABELS = 'labels';
    const CURRENT_LABELS = 'current_labels';
    /**#@-*/

    /**
     * Retrieve ID of entity with storefront labels
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Get array of labels on storefront per store view
     *
     * @return \Aheadworks\RewardPoints\Api\Data\StorefrontLabelsInterface[]
     */
    public function getLabels();

    /**
     * Set array of labels on storefront per store view
     *
     * @param \Aheadworks\RewardPoints\Api\Data\StorefrontLabelsInterface[] $labelsRecordsArray
     * @return $this
     */
    public function setLabels($labelsRecordsArray);

    /**
     * Get labels on storefront for current store view
     *
     * @return \Aheadworks\RewardPoints\Api\Data\StorefrontLabelsInterface
     */
    public function getCurrentLabels();

    /**
     * Set labels on storefront for current store view
     *
     * @param \Aheadworks\RewardPoints\Api\Data\StorefrontLabelsInterface $labelsRecord
     * @return $this
     */
    public function setCurrentLabels($labelsRecord);

    /**
     * Retrieve type of entity with storefront labels
     *
     * @return string
     */
    public function getStorefrontLabelsEntityType();
}
