<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Houzz
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Houzz\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

if (!defined('DS')) { define('DS', DIRECTORY_SEPARATOR); }

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     * @var EavSetupFactory
     */
    private $eavSetupFactory;


    /**
     * directoryList
     * @var directoryList
     */
    public $directoryList;

    /**
     * InstallData constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\File\Csv $fileCsv
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->directoryList = $directoryList;
        $this->_fileCsv = $fileCsv;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $appPath = $this->directoryList->getRoot();
        $houzzPath =  $appPath.DS."app".DS."code".DS."Ced".DS."Houzz".DS."Setup".DS;
        // This is your CSV file.
        $file = $houzzPath . 'HouzzCategoryValues.csv';

        if (file_exists($file)) {
            $houzzCatData = [];
            $catKey = 0;
            $data = $this->_fileCsv->getData($file);
            // This skips the first line of your csv file, since it will probably be a heading. Set $i = 0 to not skip the first line.
            for($i=1; $i<count($data); $i++) {
                if($data[$i][0] != '')
                {
                    $houzzCatData[$catKey] = ['cat_id' => $data[$i][0], 'category_name' => $data[$i][1]];
                    $catKey++;
                }
                  }
            try {
                $setup->getConnection()->insertArray($setup->getTable('houzz_categories'),
                    [
                        'cat_id',
                        'category_name'
                    ],
                    $houzzCatData
                );
            } catch (\Exception $e) {

            }
        }
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /**
         * Add attributes to the eav/attribute
         */
        $groupName = 'Houzz';
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 1000);
        $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);

        
        $eavSetup->addAttribute('catalog_product', 'houzz_product_status', [
                'group'            => 'Houzz',
                'note'             => 'Houzz Product Status',
                'input'            => 'select',
                'type'             => 'varchar',
                'label'            => 'Houzz Product Status',
                'backend'          => '',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 5,
                'user_defined'     => 1,
                'source'           => 'Ced\Houzz\Model\Source\ProductStatus',
                'comparable'       => 0,
                'visible_on_front' => 0,
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );

        $eavSetup->addAttribute('catalog_product', 'houzz_product_validation', [
                'group'            => 'Houzz',
                'note'             => 'Houzz Product Validation',
                'input'            => 'text',
                'type'             => 'text',
                'label'            => 'Houzz Product Validation',
                'default'          => 'Not-Validated',
                'backend'          => '',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 5,
                'user_defined'     => 1,
                'comparable'       => 0,
                'visible_on_front' => 0,
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );
        $eavSetup->addAttribute('catalog_product', 'houzz_validation_errors', [
                'group'            => 'Houzz',
                'note'             => 'Houzz Product Validation Error',
                'input'            => 'textarea',
                'type'             => 'text',
                'label'            => 'Houzz Product Validation Error',
                'backend'          => '',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 5,
                'user_defined'     => 1,
                'comparable'       => 0,
                'visible_on_front' => 0,
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );
    }
}


