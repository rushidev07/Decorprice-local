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


class HealthAndBeauty extends \Ced\Houzz\Helper\Product\Base
{
    /**
     * Insert FoodAndBeverage Category Data
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

            $product['variantGroupId'] = $type['variantid'];
            $this->configurableAttributes =  $type['variantattr'];
            $product['variantAttributeNames/variantAttributeName'] = $type['variantattr'];
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
                case 'HealthAndBeautyElectronics' : {
                    $data['HealthAndBeautyElectronics'] =
                        $this->setHealthAndBeautyElectronics($product, $attributes);
                    break;
                }
                case 'Optical' : {
                    $data['Optical'] =
                        $this->setOptical($product, $attributes);
                    break;
                }
                case 'MedicalAids' : {
                    $data['MedicalAids'] =
                        $this->setMedicalAids($product, $attributes);
                    break;
                }
                case 'PersonalCare' : {
                    $data['PersonalCare'] =
                        $this->setPersonalCare($product, $attributes);
                    break;
                }
                case 'MedicineAndSupplements' : {
                    $data['MedicineAndSupplements'] =
                        $this->setMedicineAndSupplements($product, $attributes);
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Insert HealthAndBeautyElectronics Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setHealthAndBeautyElectronics($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','countPerPack','multipackQuantity','count','pieceCount','mainImageUrl','productSecondaryImageURL','color','colorCategory/colorCategoryValue','material','gender','ageGroup/ageGroupValue','size','compatibleBrands','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','smallPartsWarnings/smallPartsWarning','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','isPowered','powerType','flexibleSpendingAccountEligible','recommendedUses','cleaningCareAndMaintenance','fabricContent','fabricCareInstructions','trackingMode','features','keywords','bodyParts','collection','isSet','isTravelSize','isPortable','isReusable','isDisposable','isCordless','hasAutomaticShutoff','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert Optical Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setOptical($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','countPerPack','multipackQuantity','count','mainImageUrl','productSecondaryImageURL','color','colorCategory/colorCategoryValue','shape','material','gender','ageGroup/ageGroupValue','isAdultProduct','collection','character','compatibleBrands','isSet','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','eyewearRimStyle','hasAdaptiveLenses','isPolarized','isScratchResistant','lensMaterial','lensTint','lensType','sunglassesStyle','uvRating','sportsLeague','sportsTeam','athlete','flexibleSpendingAccountEligible','globalBrandLicense','features','keywords','cleaningCareAndMaintenance','eyewearLensFeature','eyewearFrameStyle','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert MedicalAids Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setMedicalAids($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','countPerPack','multipackQuantity','count','pieceCount','mainImageUrl','productSecondaryImageURL','color','colorCategory/colorCategoryValue','material','gender','ageGroup/ageGroupValue','healthConcerns','diameter/measure','diameter/unit','maximumWeight/measure','maximumWeight/unit','size','isLatexFree','isWaterproof','isFoldable','isInflatable','isWheeled','isIndustrial','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','smallPartsWarnings/smallPartsWarning','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','fabricContent','fabricCareInstructions','isPowered','powerType','flexibleSpendingAccountEligible','recommendedUses','cleaningCareAndMaintenance','driveSystem','features','keywords','bodyParts','collection','shape','compatibleBrands','isSet','isTravelSize','isPortable','isReusable','isDisposable','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert PersonalCare Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setPersonalCare($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','color','colorCategory/colorCategoryValue','gender','size','ageGroup/ageGroupValue','bodyParts','collection','scent','flavor','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','smallPartsWarnings/smallPartsWarning','batteryTechnologyType','hasPricePerUnit','pricePerUnitQuantity','pricePerUnitUom','isDrugFactsLabelRequired','drugFactsLabel','drugDosageInstructionsImage','drugActiveInactiveIngredientsImage','hasIngredientList','ingredientListImage','ingredients','hasGMOs','skinCareConcern','skinType','skinTone','spfValue','hairType','isAdultProduct','recommendedUses','globalBrandLicense','activeIngredients','inactiveIngredients','form','instructions','stopUseIndications','features','compatibleBrands','isSet','isTravelSize','isPortable','isReusable','isDisposable','isPowered','powerType','flexibleSpendingAccountEligible','cleaningCareAndMaintenance','batteriesRequired','resultTime/measure','resultTime/unit','isNoncomodegenic','isTinted','isSelfTanning','isWaterproof','isUnscented','absorbency','keywords','material','sportsLeague','sportsTeam','athlete','wigCapStyle','hairColorCategory','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert MedicineAndSupplements Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setMedicineAndSupplements($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','countPerPack','multipackQuantity','count','mainImageUrl','productSecondaryImageURL','flavor','size','ageGroup/ageGroupValue','gender','bodyParts','healthConcerns','color','colorCategory/colorCategoryValue','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','hasPricePerUnit','pricePerUnitQuantity','pricePerUnitUom','isDrugFactsLabelRequired','drugFactsLabel','drugDosageInstructionsImage','drugActiveInactiveIngredientsImage','isSupplementFactsLabelRequired','supplementFactsLabel','supplementDosageInstructionsImage','supplementActiveInactiveIngredientsImage','isNutritionFactsLabelRequired','nutritionFactsLabel','hasIngredientList','ingredientListImage','ingredients','hasGMOs','hasExpiration','recommendedUses','globalBrandLicense','activeIngredients','inactiveIngredients','form','instructions','dosage','stopUseIndications','medicineStrength','nationalDrugCode','nutrients','servingSize','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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