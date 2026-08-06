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

namespace Magetrend\Eop\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.4') < 0) {
            $this->update204($setup);
        }
        $setup->endSetup();
    }

    public function update204($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            \Magetrend\Eop\Model\Campaign::PRODUCT_ATTRIBUTE_CODE,
            [
                'type' => 'int',
                'length' => 10,
                'label' => 'Exit Intent Popup Campaign',
                'input' => 'select',
                'required' => false,
                'sort_order' => 999,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'user_defined' => true,
                'is_user_defined' => true,
                'visible' => true,
                'note' => 'Leave blank if you do not want to show popup on this product page.',
                'source_model' => 'Magetrend\Eop\Model\Config\Source\Eav\Campaign',
                'source' => 'Magetrend\Eop\Model\Config\Source\Eav\Campaign'
            ]
        );

        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
        $attributeId = $eavSetup->getAttributeId(
            $entityTypeId,
            \Magetrend\Eop\Model\Campaign::PRODUCT_ATTRIBUTE_CODE
        );
        foreach ($attributeSetIds as $attributeSetId) {
            $attributeGroupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'general');
            $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, 99999);
        }

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            \Magetrend\Eop\Model\Campaign::CATEGORY_ATTRIBUTE_CODE,
            [
                'type' => 'int',
                'length' => 10,
                'label' => 'Exit Intent Popup Campaign',
                'input' => 'select',
                'required' => false,
                'sort_order' => 999,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'user_defined' => true,
                'is_user_defined' => true,
                'visible' => true,
                'note' => 'Leave blank if you do not want to show popup on this product page.',
                'source_model' => 'Magetrend\Eop\Model\Config\Source\Eav\Campaign',
                'source' => 'Magetrend\Eop\Model\Config\Source\Eav\Campaign',
                'group' => 'Additional Settings',
            ]
        );
    }
}
