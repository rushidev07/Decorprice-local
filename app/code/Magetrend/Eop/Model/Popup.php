<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */

namespace Magetrend\Eop\Model;

/**
 * Popup model
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Popup extends \Magento\Framework\Model\AbstractModel
{
    const TYPE_NEWSLETTER_SUBSCRIPTION = 'newsletter_subscription_form';

    const TYPE_STATIC_BLOCK = 'static_block';

    const TYPE_YES_NO_BUTTONS= 'yes_no_buttons';

    const TYPE_CONTACT_FORM= 'contact_form';

    const DISCOUNT_CODE_FIELD = 'eo_discount_code';

    /**
     * @var ResourceModel\Field\CollectionFactory
     */
    public $fieldCollectionFactory;

    /**
     * @var ResourceModel\FieldOption\CollectionFactory
     */
    public $fieldOptionCollectionFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    public $rule;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var ResourceModel\Field\Collection|null
     */
    private $additionalFields = null;

    /**
     * @var \Magento\SalesRule\Model\Coupon\Massgenerator
     */
    public $massGenerator;

    /**
     * @var ResourceModel\Campaign\CollectionFactory
     */
    public $campaignCollection;

    /**
     * @var FieldOptionFactory
     */
    public $fieldOptionFactory;

    /**
     * @var FieldFactory
     */
    public $fieldFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;

    /**
     * Popup constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param FieldOptionFactory $fieldOptionFactory
     * @param FieldFactory $fieldFactory
     * @param ResourceModel\Field\CollectionFactory $fieldCollection
     * @param ResourceModel\Campaign\CollectionFactory $campaignCollectionFactory
     * @param ResourceModel\FieldOption\CollectionFactory $fieldOptionCollection
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\SalesRule\Model\Coupon\Massgenerator $massGenerator
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magetrend\Eop\Model\FieldOptionFactory $fieldOptionFactory,
        \Magetrend\Eop\Model\FieldFactory $fieldFactory,
        \Magetrend\Eop\Model\ResourceModel\Field\CollectionFactory $fieldCollection,
        \Magetrend\Eop\Model\ResourceModel\Campaign\CollectionFactory $campaignCollectionFactory,
        \Magetrend\Eop\Model\ResourceModel\FieldOption\CollectionFactory $fieldOptionCollection,
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\SalesRule\Model\Coupon\Massgenerator $massGenerator,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->rule = $rule;
        $this->fieldCollectionFactory = $fieldCollection;
        $this->fieldOptionCollectionFactory = $fieldOptionCollection;
        $this->objectManager = $objectManager;
        $this->massGenerator = $massGenerator;
        $this->campaignCollection = $campaignCollectionFactory;
        $this->fieldOptionFactory = $fieldOptionFactory;
        $this->fieldFactory = $fieldFactory;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize object popup
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        $this->_init('Magetrend\Eop\Model\ResourceModel\Popup');
    }

    /**
     * Save popup and additional fields
     */
    public function save()
    {
        $fieldData = $this->getData('field');
        parent::save();
        $this->saveAdditionalFieldCollection($fieldData);
    }

    /**
     * Save additionall fields
     * @param $fieldData
     */
    public function saveAdditionalFieldCollection($fieldData)
    {
        if (isset($fieldData['options'])) {
            $data = $fieldData['options'];
            if (!empty($data)) {
                foreach ($data as $fieldData) {
                    if (isset($fieldData['is_delete']) && $fieldData['is_delete'] == 1) {
                        $this->deleteField($fieldData['id']);
                    } else {
                        $this->saveField($fieldData);
                    }
                }
            }
        }
    }

    /**
     * Save additional field
     * @param $fieldData
     */
    public function saveField($fieldData)
    {
        $newFieldId = $this->createField($fieldData, $this->getId());
        if (isset($fieldData['previous_group']) && $fieldData['previous_group'] == 'select') {
            if (isset($fieldData['values'])) {
                foreach ($fieldData['values'] as $fieldOption) {
                    if ($fieldOption['is_delete'] == 1) {
                        $this->deleteOption($fieldOption['option_type_id']);
                    } else {
                        $this->createOption($newFieldId, $fieldOption);
                    }
                }
            }
        }
    }

    /**
     * Save additional field options
     * @param $fieldId
     * @param $postData
     */
    public function createOption($fieldId, $postData)
    {
        $option = $this->fieldOptionFactory->create();
        if (isset($postData['option_type_id']) && $postData['option_type_id'] > 0) {
            $option->load($postData['option_type_id']);
        }
        $option->setData('field_id', $fieldId);
        $option->setData('value', $postData['value']);
        $option->setData('label', $postData['label']);
        $option->setData('position', $postData['sort_order']);
        $option->save();
    }

    /**
     * Create new additional field
     * @param $postData
     * @param $popupId
     * @return mixed
     */
    public function createField($postData, $popupId)
    {
        $field = $this->fieldFactory->create();
        if (isset($postData['id']) && $postData['option_id'] > 0) {
            $field->load($postData['id']);
        }

        if (!isset($postData['error_message'])) {
            $postData['error_message'] = [];
        }
        $postData['error_message'] = $this->jsonHelper->jsonEncode($postData['error_message']);

        $field->setData('popup_id', $popupId);
        $field->setData('name', $postData['name']);
        $field->setData('type', $postData['type']);
        $field->setData('position', $postData['sort_order']);
        $field->setData('is_required', $postData['is_require']);
        $field->setData('after_email_field', $postData['after_email_field']);
        $field->setData('default_value', isset($postData['default_value'])?$postData['default_value']:'');
        $field->setData('frontend_label', isset($postData['frontend_label'])?$postData['frontend_label']:'');
        $field->setData('error_message', $postData['error_message']);
        $field->setData('label', $postData['label']);
        $field->save();
        return $field->getId();
    }

    /**
     * Delete additional field by ID
     * @param $fieldId
     * @return bool
     */
    public function deleteField($fieldId)
    {
        $field = $this->fieldFactory->create();
        $field->load($fieldId);

        $optionCollection = $this->fieldOptionCollectionFactory->create()
            ->setFieldFilter($field->getId());
        $optionCollection->walk('delete');

        $field->delete();
        return true;
    }

    /**
     * Delete field options by option ID
     * @param $optionId
     * @return bool
     */
    public function deleteOption($optionId)
    {
        $option = $this->fieldOptionFactory->create();
        $option->load($optionId);
        $option->delete();

        return true;
    }

    /**
     * Delete campaign-popup relations
     */
    public function deleteRelations()
    {
        $campaignCollection = $this->campaignCollection->create()
            ->addFieldToFilter('popup_id', $this->getId());

        if ($campaignCollection->getSize() > 0) {
            foreach ($campaignCollection as $campaign) {
                $campaign->setPopupId(null);
            }
            $campaignCollection->walk('save');
        }
    }

    /**
     * Delete popup and additional field
     */
    public function delete()
    {
        $this->deleteFieldCollection();
        $this->deleteRelations();
        parent::delete();
    }

    /**
     * It will delete popup fields collection
     * @return bool
     */
    public function deleteFieldCollection()
    {
        $collection = $this->fieldCollectionFactory->create()
            ->setPopupFilter($this->getId());
        if ($collection->getSize() > 0) {
            foreach ($collection as $item) {
                $this->deleteField($item->getId());
            }
        }
        return true;
    }

    /**
     * Is popup content static block
     * @return bool
     */
    public function isStaticBlockPopup()
    {
        if ($this->getContentType() != self::TYPE_STATIC_BLOCK) {
            return false;
        }
        return true;
    }

    /**
     * Returns color code with #
     * @param $key
     * @return string
     */
    public function getColor($key)
    {
        $color = $this->getData('color_'.$key);
        return '#'.str_replace('#', '', $color);
    }

    /**
     * Returns additional fields collection
     * @return null|array
     */
    public function getAdditionalFields()
    {
        if ($this->additionalFields == null) {
            $collection = $this->fieldCollectionFactory->create()
                ->setPopupFilter($this->getId())
                ->joinOptionCollection()
                ->sortByPositionCollection();

            $this->additionalFields = $collection->getGroupedData();
        }
        return $this->additionalFields;
    }

    /**
     * Generate discount code
     * @return string
     */
    public function getUniqueDiscountCode()
    {
        if (!$this->getId() || !is_numeric($this->getCouponRuleId())) {
            return '';
        }

        $rule = $this->rule->load($this->getCouponRuleId());
        if (!$rule->getId()) {
            return '';
        }

        if ($rule->getUseAutoGeneration() == 0) {
            return $rule->getCouponCode();
        }

        $codeGenerator = $this->massGenerator;
        $codeGenerator->setData('qty', 1);
        $codeGenerator->setData('rule_id', $rule->getId());
        $codeGenerator->setData('length', $this->getCouponLength());
        $codeGenerator->setData('format', $this->getCouponFormat());
        $codeGenerator->setData('prefix', $this->getCouponPrefix());
        $codeGenerator->setData('suffix', $this->getCouponSuffix());
        $codeGenerator->setData('dash', $this->getCouponDash());
        $codeGenerator->setData('uses_per_coupon', 1);
        $codeGenerator->setData('uses_per_customer', 1);

        $codeGenerator->generatePool();
        $latestCoupon = max($rule->getCoupons());

        $expireInDays = $this->getData('coupon_expire_in_days');
        if (is_numeric($expireInDays) && $expireInDays > 0) {
            $latestCoupon->setData(
                'expiration_date',
                $this->date->gmtDate('Y-m-d H:i:s', time() + 3600 * 24 * $expireInDays)
            )->save();
        } elseif ($rule->getToDate()) {
            $latestCoupon->setData(
                'expiration_date',
                $rule->getToDate()
            )->save();
        }

        return $latestCoupon->getCode();
    }
}
