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


class Baby extends \Ced\Houzz\Helper\Product\Base
{
    /**
     * Insert Baby Category Data
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
                case 'ChildCarSeats' : {
                    $data['ChildCarSeats'] = $this->setChildCarSeats($product, $attributes);
                    break;
                }
                case 'BabyClothing' : {
                    $data['BabyClothing'] = $this->setBabyClothing($product, $attributes);
                    break;
                }
                case 'BabyFurniture' : {
                    $data['BabyFurniture'] = $this->setBabyFurniture($product, $attributes);
                    break;
                }
                case 'BabyToys' : {
                    $data['BabyToys'] = $this->setBabyToys($product, $attributes);
                    break;
                }
                case 'BabyFood' : {
                    $data['BabyFood'] = $this->setBabyFood($product, $attributes);
                    break;
                }
                case 'BabyOther' : {
                    $data['BabyOther'] = $this->setBabyOther($product, $attributes);
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Insert ChildCarSeats Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setChildCarSeats($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','modelNumber','manufacturerPartNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','color','colorCategory/colorCategoryValue','pattern','material','gender','size','ageGroup/ageGroupValue','ageRange','minimumWeight/measure','minimumWeight/unit','maximumWeight/measure','maximumWeight/unit','character','globalBrandLicense','isFoldable','isWheeled','strollerType','seatingCapacity','babyCarrierStyle','babyCarrierPosition','safetyHarnessStyle','childWalkingHarnessStyle','travelSystemCompatibility','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','smallPartsWarnings/smallPartsWarning','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','isAssemblyRequired','assemblyInstructions','requiresTextileActLabeling','countryOfOriginTextiles','fabricContent','fabricCareInstructions','childCarSeatType','facingDirection','forwardFacingMinimumWeight/measure','forwardFacingMinimumWeight/unit','forwardFacingMaximumWeight/measure','forwardFacingMaximumWeight/unit','rearFacingMinimumWeight/measure','rearFacingMinimumWeight/unit','rearFacingMaximumWeight/measure','rearFacingMaximumWeight/unit','hasLatchSystem','carSeatBaseDepth/measure','carSeatBaseDepth/unit','carSeatBaseWidth/measure','carSeatBaseWidth/unit','carSeatMaxChildHeight/measure','carSeatMaxChildHeight/unit','sportsLeague','sportsTeam','athlete','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert BabyClothing Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setBabyClothing($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','modelNumber','manufacturerPartNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','color','colorCategory/colorCategoryValue','gender','babyClothingSize','ageGroup/ageGroupValue','ageRange','minimumWeight/measure','minimumWeight/unit','maximumWeight/measure','maximumWeight/unit','season','scent','character','globalBrandLicense','pattern','shoeCategory','shoeStyle','shoeSize','shoeWidth','shoeClosure','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','smallPartsWarnings/smallPartsWarning','requiresTextileActLabeling','countryOfOriginTextiles','fabricContent','fabricCareInstructions','sportsLeague','sportsTeam','theme','athlete','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert BabyFurniture Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setBabyFurniture($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','modelNumber','manufacturerPartNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','color','colorCategory/colorCategoryValue','gender','size','ageGroup/ageGroupValue','ageRange','minimumWeight/measure','minimumWeight/unit','maximumWeight/measure','maximumWeight/unit','material','pattern','character','globalBrandLicense','bedSize','mattressFirmness','fillMaterial','finish','shape','isFoldable','isWheeled','homeDecorStyle','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','smallPartsWarnings/smallPartsWarning','requiresTextileActLabeling','countryOfOriginTextiles','hasWarranty','warrantyURL','warrantyText','isAssemblyRequired','assemblyInstructions','fabricContent','fabricCareInstructions','sportsLeague','sportsTeam','athlete','features','keywords','collection','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
        );
        $data = array();

        if (!empty($product) && !empty($attributes)) {
            foreach ($houzzAttr as $attr) {
                if (!empty($product[$attributes[$attr]])) {
                    $data = array_merge_recursive($data, $this->generateArray($attr, $product[$attributes[$attr]]));
                }
            }
        }

        return $data;
    }

    /**
     * Insert BabyToys Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setBabyToys($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','modelNumber','manufacturerPartNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','color','colorCategory/colorCategoryValue','material','gender','size','ageGroup/ageGroupValue','ageRange','minimumWeight/measure','minimumWeight/unit','maximumWeight/measure','maximumWeight/unit','season','scent','character','globalBrandLicense','pattern','educationalFocus','theme','makesNoise','awardsWon','animalType','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','smallPartsWarnings/smallPartsWarning','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','fabricContent','fabricCareInstructions','isAssemblyRequired','assemblyInstructions','isPowered','powerType','screenSize/measure','screenSize/unit','sportsLeague','sportsTeam','athlete','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
        );
        $data = array();

        if (!empty($product) && !empty($attributes)) {
            foreach ($houzzAttr as $attr) {
                if (!empty($product[$attributes[$attr]])) {
                    $data = array_merge_recursive($data, $this->generateArray($attr, $product[$attributes[$attr]]));
                }
            }
        }

        return $data;
    }

    /**
     * Insert BabyFood Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setBabyFood($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','flavor','meal','isReadyToEat','size','ageGroup/ageGroupValue','ageRange','character','globalBrandLicense','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','hasPricePerUnit','pricePerUnitQuantity','pricePerUnitUom','hasExpiration','shelfLife/measure','shelfLife/unit','isNutritionFactsLabelRequired','nutritionFactsLabel','nutritionIngredientsImage','hasIngredientList','ingredientListImage','ingredients','hasGMOs','servingSize','servingsPerContainer','calories/measure','calories/unit','caloriesFromFat/measure','caloriesFromFat/unit','totalFat/measure','totalFat/unit','totalFatPercentageDailyValue','fatCaloriesPerGram/measure','fatCaloriesPerGram/unit','totalCarbohydrate/measure','totalCarbohydrate/unit','totalCarbohydratePercentageDailyValue','carbohydrateCaloriesPerGram/measure','carbohydrateCaloriesPerGram/unit','nutrients','proteinCaloriesPerGram/measure','proteinCaloriesPerGram/unit','totalProteinPercentageDailyValue','totalProtein/measure','totalProtein/unit','foodForm','containerType','isImitation','usdaInspected','hasHighFructoseCornSyrup','fluidOuncesSupplying100Calories/measure','fluidOuncesSupplying100Calories/unit','foodAllergenStatements','babyFoodPackaging','babyFormulaStage','babyFoodStage','instructions','features','keywords','safeHandlingInstructions','cuisine','foodPreparationTips','foodStorageTips','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
        );
        $data = array();

        if (!empty($product) && !empty($attributes)) {
            foreach ($houzzAttr as $attr) {
                if (!empty($product[$attributes[$attr]])) {
                    $data = array_merge_recursive($data, $this->generateArray($attr, $product[$attributes[$attr]]));
                }
            }
        }

        return $data;
    }

    /**
     * Insert BabyOther Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setBabyOther($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','modelNumber','manufacturerPartNumber','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','color','colorCategory/colorCategoryValue','size','bedSize','diaperSize','diaposableBabyDiaperType','material','gender','ageGroup/ageGroupValue','ageRange','minimumWeight/measure','minimumWeight/unit','maximumWeight/measure','maximumWeight/unit','scent','character','globalBrandLicense','pattern','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','smallPartsWarnings/smallPartsWarning','batteryTechnologyType','hasPricePerUnit','pricePerUnitQuantity','pricePerUnitUom','hasExpiration','hasWarranty','warrantyURL','warrantyText','hasIngredientList','ingredientListImage','ingredients','requiresTextileActLabeling','countryOfOriginTextiles','fabricContent','fabricCareInstructions','batteriesRequired','sportsLeague','sportsTeam','athlete','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
        );
        $data = array();

        if (!empty($product) && !empty($attributes)) {
            foreach ($houzzAttr as $attr) {
                if (!empty($product[$attributes[$attr]])) {
                    $data = array_merge_recursive($data, $this->generateArray($attr, $product[$attributes[$attr]]));
                }
            }
        }

        return $data;
    }
}