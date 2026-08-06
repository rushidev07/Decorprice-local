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

namespace Ced\Houzz\Helper\Product;


class ArtAndCraftCategory extends \Ced\Houzz\Helper\Product\Base
{
    /**
     * @param $product
     * @param array $attributes
     * @param array $category
     * @param array $type
     * @return array
     */
    public function setData(
        $product,
        $attributes = [],
        $category = [],
        $type = [
        'type' => 'simple',
        'variantid' => null,
        'variantattr' => null,
        'isprimary' => '0'
        ]
    ) {
        $this->productObject = $product;
        $product = $product->toArray();
        $product['blank'] = '';
        $attributes['variantGroupId'] = 'blank';
        $attributes['variantAttributeNames/variantAttributeName'] = 'blank';
        $attributes['isPrimaryVariant'] = 'blank';
        $this->attributes = $attributes;
        $product = $this->extractSelectValues($product);
        if (isset($type['type'],$type['variantid'], $type['variantattr']) && !empty($type['variantid'])) {
            $attributes['variantGroupId'] = 'variantGroupId';
            $attributes['variantAttributeNames/variantAttributeName'] = 'variantAttributeNames/variantAttributeName';
            $attributes['isPrimaryVariant'] = 'isPrimaryVariant';
            $product['variantGroupId'] = $type['variantid'];
            $this->configurableAttributes =  $type['variantattr'];
            $product['variantAttributeNames/variantAttributeName'] = $type['variantattr'];
            $product['isPrimaryVariant'] = $type['isprimary'];
            $redundantAttributeCheck = array_flip($type['variantattr']);
            $additionalAttributes =  $type['additionalAttributes'];
            if(count($additionalAttributes['_value']) > 0) {
                foreach ($additionalAttributes['_value'] as $key => $value) {
                    # code...
                    if(isset($value['additionalProductAttribute']['productAttributeValue'])) {
                        $productValues[$value['additionalProductAttribute']['productAttributeName']] = $value['additionalProductAttribute']['productAttributeValue'];
                    }
                }
            }
            if($this->swatchEnabled) {
                if(isset($redundantAttributeCheck['color'])) {
                    $product['swatchImages/swatchImage/swatchImageUrl'] = $this->objectManager->get('\Magento\Catalog\Helper\Image')->init($this->productObject, 'swatch_image')->constrainOnly(TRUE)
                        ->keepAspectRatio(TRUE)
                        ->keepTransparency(TRUE)
                        ->keepFrame(FALSE)->resize(100,100)->getUrl();
                    $product['swatchImages/swatchImage/swatchVariantAttribute'] = 'color';
                    $attributes['swatchImages/swatchImage/swatchImageUrl'] = 'swatchImages/swatchImage/swatchImageUrl';
                    $attributes['swatchImages/swatchImage/swatchVariantAttribute'] = 'swatchImages/swatchImage/swatchVariantAttribute';
                } elseif(isset($redundantAttributeCheck['pattern'])) {
                    $product['swatchImages/swatchImage/swatchImageUrl'] = $this->objectManager->get('\Magento\Catalog\Helper\Image')->init($this->productObject, 'swatch_image')->constrainOnly(TRUE)
                        ->keepAspectRatio(TRUE)
                        ->keepTransparency(TRUE)
                        ->keepFrame(FALSE)->resize(100,100)->getUrl();
                    $product['swatchImages/swatchImage/swatchVariantAttribute'] = 'pattern';
                    $attributes['swatchImages/swatchImage/swatchImageUrl'] = 'swatchImages/swatchImage/swatchImageUrl';
                    $attributes['swatchImages/swatchImage/swatchVariantAttribute'] = 'swatchImages/swatchImage/swatchVariantAttribute';
                } else {
                    foreach ($redundantAttributeCheck as $key => $attribute) {
                        if(!isset($confAttributeFlag[$key])) {
                            $product['swatchImages/swatchImage/swatchImageUrl'] = $this->objectManager->get('\Magento\Catalog\Helper\Image')->init($this->productObject, 'swatch_image')->constrainOnly(TRUE)
                                ->keepAspectRatio(TRUE)
                                ->keepTransparency(TRUE)
                                ->keepFrame(FALSE)->resize(100,100)->getUrl();
                            $product['swatchImages/swatchImage/swatchVariantAttribute'] = $key;
                            $attributes['swatchImages/swatchImage/swatchImageUrl'] = 'swatchImages/swatchImage/swatchImageUrl';
                            $attributes['swatchImages/swatchImage/swatchVariantAttribute'] = 'swatchImages/swatchImage/swatchVariantAttribute';
                            break;
                        }
                    }
                }
            }
        }
        $data = [];
        if (!empty($product) && !empty($attributes) && !empty($category)) {

            switch ($category['cat_id']) {
                case 'ArtAndCraft' : {
                    $data['ArtAndCraft'] =
                        $this->setArtAndCraft($product, $attributes);
                    break;
                }
            }
            return $data;
        }
    }

    /**
     * Insert ArtAndCraft Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setArtAndCraft($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription', 'keyFeatures', 'brand', 'manufacturer', 'manufacturerPartNumber', 'modelNumber',
            'multipackQuantity', 'countPerPack', 'count', 'pieceCount', 'mainImageUrl', 'productSecondaryImageURL',
            'color', 'colorCategory', 'colorCategory/colorCategoryValue', 'material', 'pattern', 'size', 'metal',
            'plating', 'finish', 'form', 'shape', 'scent', 'gender', 'ageGroup', 'ageGroup/ageGroupValue', 'ageRange',
            'artPaintType', 'assembledProductLength', 'assembledProductLength/measure', 'assembledProductLength/unit',
            'assembledProductWidth', 'assembledProductWidth/measure', 'assembledProductWidth/unit',
            'assembledProductHeight', 'assembledProductHeight/measure', 'assembledProductHeight/unit',
            'assembledProductWeight', 'assembledProductWeight/measure', 'assembledProductWeight/unit',
            'variantGroupId', 'variantAttributeNames', 'variantAttributeNames/variantAttributeName',
            'isPrimaryVariant', 'isProp65WarningRequired', 'prop65WarningText', 'smallPartsWarnings',
            'smallPartsWarnings/smallPartsWarning', 'batteryTechnologyType', 'hasIngredientList',
            'ingredientListImage', 'ingredients', 'isAerosol', 'isChemical', 'compositeWoodCertificationCode',
            'hasExpiration', 'hasWarranty', 'warrantyURL', 'warrantyText', 'hasFuelContainer', 'fabricContent',
            'fabricCareInstructions', 'capacity', 'diameter', 'diameter/measure', 'diameter/unit', 'culturalStyle',
            'subject', 'theme', 'occasion', 'isAntique', 'isCollectible', 'character', 'isPowered', 'powerType', 'isBulk',
            'isHandmade', 'isSelfAdhesive', 'isAntitarnish', 'isReusable', 'isRefillable', 'isDisposable', 'isRecyclable',
            'recommendedUses', 'recommendedSurfaces', 'chainLength', 'chainLength/measure', 'chainLength/unit',
            'skillLevel', 'globalBrandLicense', 'features', 'keywords', 'swatchImages', 'swatchImages/swatchImage',
            'swatchImages/swatchImage/swatchVariantAttribute', 'swatchImages/swatchImage/swatchImageUrl'
        );
        $data = array();
        if (!empty($product) && !empty($attributes)) {
            foreach ($houzzAttr as $attr) {
                if (isset($attributes[$attr]) && !empty($product[$attributes[$attr]])) {
                    $data = array_merge_recursive($data, $this->generateArray($attr, $product[$attributes[$attr]]));
                }
            }
        }
        return $data;
    }
}