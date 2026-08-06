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


class OtherCategory extends \Ced\Houzz\Helper\Product\Base
{
    /**
     * Insert Other Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @param string|[] $category
     * @param string|[] $type
     * @return string|[]
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
        $redundantAttributeCheck = [];

        if (isset($type['type'],$type['variantid'], $type['variantattr']) && !empty($type['variantid'])) {
            $attributes['variantGroupId'] = 'variantGroupId';
            $attributes['variantAttributeNames/variantAttributeName'] = 'variantAttributeNames/variantAttributeName';
            $attributes['isPrimaryVariant'] = 'isPrimaryVariant';

            $variantAttr = explode(',', $type['variantattr']);
            $product['variantGroupId'] = $type['variantid'];
            $this->configurableAttributes =  $type['variantattr'];
            $product['variantAttributeNames/variantAttributeName'] = $variantAttr;
            $product['isPrimaryVariant'] = $type['isprimary'];
            $additionalAttributes =  $type['additionalAttributes'];
            $redundantAttributeCheck = array_flip($type['variantattr']);
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
                case 'Storage' : {
                    $data['Storage'] = $this->setStorage($product, $attributes);
                    break;
                }
                case 'CleaningAndChemical' : {
                    $data['CleaningAndChemical'] = $this->setCleaningAndChemical($product, $attributes);
                    break;
                }
                case 'giftCards' : {
                    $data['giftCards'] = $this->setgiftCards($product, $attributes);
                    break;
                }
                case 'safetyAndEmergency' : {
                    $data['safetyAndEmergency'] = $this->setsafetyAndEmergency($product, $attributes);
                    break;
                }
                case 'fuelsAndLubricants' : {
                    $data['fuelsAndLubricants'] = $this->setfuelsAndLubricants($product, $attributes);
                    break;
                }
                case 'Other' : {
                    $data['Other'] = $this->setOther($product, $attributes);
                    break;
                }

            }
        }

        return $data;
    }


    /**
     * Insert Other/Storage Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setStorage($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','material','finish','color','colorCategory/colorCategoryValue','pattern','shape','size','recommendedRooms','recommendedLocations','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','smallPartsWarnings/smallPartsWarning','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','isAssemblyRequired','assemblyInstructions','fabricContent','fabricCareInstructions','collection','numberOfShelves','shelfStyle','shelfDepth/measure','shelfDepth/unit','numberOfDrawers','drawerPosition','drawerDimensions','capacity','maximumWeight/measure','maximumWeight/unit','recommendedUses','globalBrandLicense','features','keywords','isFoldable','isRetractable','isPortable','isIndustrial','systemOfMeasurement','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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

    /**
     * Insert Other/CleaningAndChemical Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setCleaningAndChemical($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','recommendedSurfaces','recommendedRooms','recommendedLocations','size','material','finish','color','colorCategory/colorCategoryValue','pattern','shape','isAssemblyRequired','assemblyInstructions','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','hasExpiration','hasPricePerUnit','pricePerUnitUom','pricePerUnitQuantity','hasIngredientList','ingredientListImage','ingredients','hasFuelContainer','fabricContent','isPowered','powerType','volts/measure','volts/unit','connections','activeIngredients','inactiveIngredients','instructions','form','scent','fluidOunces/measure','fluidOunces/unit','isRecyclable','isFlammable','isCombustible','isBiodegradable','isEnergyStarCertified','handleLength/measure','handleLength/unit','bladeWidth','bristleMaterial','cleaningPath/measure','cleaningPath/unit','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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

    /**
     * Insert Other/giftCards Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setgiftCards($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','giftCardFormat','giftCardCategory/giftCardCategoryValue','giftCardAmount','occasion','globalBrandLicense','features','keywords','gender','pattern','color','character','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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

    /**
     * Insert Other/Other Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setOther($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','material','finish','color','colorCategory/colorCategoryValue','pattern','shape','size','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','smallPartsWarnings/smallPartsWarning','hasIngredientList','ingredientListImage','ingredients','hasFuelContainer','requiresTextileActLabeling','countryOfOriginTextiles','features','keywords','isFoldable','isRetractable','isPortable','isIndustrial','systemOfMeasurement','gender','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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

    /**
     * Insert Other/fuelsAndLubricants Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setfuelsAndLubricants($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'hasFuelContainer','shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','size','fuelType','fluidOunces/measure','fluidOunces/unit','recommendedUses','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','hasExpiration','hasPricePerUnit','pricePerUnitUom','pricePerUnitQuantity','instructions','form','isEnergyStarCertified','isRefillable','features','keywords','systemOfMeasurement','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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

    /**
     * Insert Other/safetyAndEmergency Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setsafetyAndEmergency($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','recommendedSurfaces','recommendedLocations','size','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','smallPartsWarnings/smallPartsWarning','batteryTechnologyType','hasExpiration','hasWarranty','warrantyURL','warrantyText','hasFuelContainer','hasIngredientList','ingredientListImage','ingredients','isAssemblyRequired','assemblyInstructions','instructions','form','fluidOunces/measure','fluidOunces/unit','handleLength/measure','handleLength/unit','isRefillable','fireExtinguisherClasses/fireExtinguisherClassesValue','workingPressure/measure','workingPressure/unit','recommendedUses','isPowered','powerType','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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