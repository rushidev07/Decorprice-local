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


class SportAndRecreation extends \Ced\Houzz\Helper\Product\Base
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
                case 'Cycling' : {
                    $data['Cycling'] = $this->setCycling($product, $attributes);
                    break;
                }
                case 'Optics' : {
                    $data['Optics'] = $this->setCycling($product, $attributes);
                    break;
                }
                case 'Weapons' : {
                    $data['Weapons'] = $this->setCycling($product, $attributes);
                    break;
                }
                case 'SportAndRecreationOther' : {
                    $data['SportAndRecreationOther'] = $this->setSportAndRecreationOther($product, $attributes);
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Insert Cycling Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setCycling($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'hasFuelContainer','shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','color','colorCategory/colorCategoryValue','gender','size','ageGroup/ageGroupValue','ageRange','sport','bicycleFrameSize/measure','bicycleFrameSize/unit','bicycleWheelDiameter/measure','bicycleWheelDiameter/unit','bicycleTireSize','numberOfSpeeds','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','smallPartsWarnings/smallPartsWarning','batteryTechnologyType','hasExpiration','shelfLife/measure','shelfLife/unit','requiresTextileActLabeling','countryOfOriginTextiles','hasWarranty','warrantyURL','warrantyText','fabricContent','fabricCareInstructions','isAssemblyRequired','assemblyInstructions','material','dexterity','globalBrandLicense','sportsLeague','sportsTeam','athlete','features','keywords','pattern','finish','shape','season','character','capacity','seatingCapacity','maximumWeight/measure','maximumWeight/unit','wirelessTechnologies','isPortable','isFoldable','isWeatherResistant','isWaterproof','isPowered','powerType','horsepower/measure','horsepower/unit','tireDiameter/measure','tireDiameter/unit','cleaningCareAndMaintenance','recommendedUses','lockType','lockingMechanism','recommendedLocations','lightBulbType','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert Optics Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setOptics($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','gender','size','ageGroup/ageGroupValue','ageRange','digitalZoom','opticalZoom','lensDiameter/measure','lensDiameter/unit','lensCoating','sensorResolution','magnification','focusType','fieldOfView','isParfocal','focalRatio','displayTechnology','displayResolution','hasNightVision','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','isChemical','hasWarranty','warrantyURL','warrantyText','isAssemblyRequired','assemblyInstructions','material','dexterity','globalBrandLicense','screenSize/measure','screenSize/unit','hasLcdScreen','powerType','isMulticoated','isLockable','lockType','hasMemoryCardSlot','isFogResistant','operatingTemperature/measure','operatingTemperature/unit','hasDovetailBarSystem','attachmentStyle','features','keywords','color','colorCategory/colorCategoryValue','sport','pattern','wirelessTechnologies','isPortable','isFoldable','isWeatherResistant','isWaterproof','isPowered','cleaningCareAndMaintenance','recommendedUses','recommendedLocations','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert Weapons Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setWeapons($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','ammunitionType','sport','velocity/measure','velocity/unit','caliber','firearmAction','shotgunGauge','barrelLength/measure','barrelLength/unit','gender','ageGroup/ageGroupValue','size','clothingSize','color','colorCategory/colorCategoryValue','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','hasFuelContainer','material','dexterity','globalBrandLicense','isAssemblyRequired','assemblyInstructions','firearmChamberLength','sportsLeague','sportsTeam','athlete','features','keywords','pattern','finish','shape','season','character','bladeType','animalType','wirelessTechnologies','isMemorabilia','isCollectible','isPortable','isWeatherResistant','isWaterproof','isPowered','compatibleDevices','powerType','cleaningCareAndMaintenance','recommendedUses','recommendedLocations','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert SportAndRecreationOther Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setSportAndRecreationOther($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','color','colorCategory/colorCategoryValue','sport','gender','size','ageGroup/ageGroupValue','ageRange','clothingSize','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','smallPartsWarnings/smallPartsWarning','batteryTechnologyType','hasExpiration','shelfLife/measure','shelfLife/unit','requiresTextileActLabeling','countryOfOriginTextiles','hasWarranty','warrantyURL','warrantyText','compositeWoodCertificationCode','hasFuelContainer','fabricContent','fabricCareInstructions','shoeSize','sportsLeague','sportsTeam','isAssemblyRequired','assemblyInstructions','driveSystem','strideLength','material','dexterity','globalBrandLicense','athlete','autographedBy','features','keywords','pattern','finish','shape','season','character','capacity','seatingCapacity','maximumWeight/measure','maximumWeight/unit','maximumIncline','batDrop','fitnessGoal','footballSize','basketballSize','soccerBallSize','ballCoreMaterial','bladeType','animalType','tentType','fishingLocation','fishingLinePoundTest','wirelessTechnologies','hasAutomaticShutoff','minimumTemperature/measure','minimumTemperature/unit','isMemorabilia','isCollectible','isPortable','isFoldable','isSpaceSaving','isWheeled','isTearResistant','isWeatherResistant','isWaterproof','isPowered','powerType','horsepower/measure','horsepower/unit','velocity/measure','velocity/unit','tireDiameter/measure','tireDiameter/unit','cleaningCareAndMaintenance','recommendedUses','recommendedLocations','compatibleDevices','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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