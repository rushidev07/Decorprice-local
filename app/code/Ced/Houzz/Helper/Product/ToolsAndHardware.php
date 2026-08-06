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


class ToolsAndHardware extends \Ced\Houzz\Helper\Product\Base
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
                case 'PlumbingAndHVAC' : {
                    $data['PlumbingAndHVAC'] = $this->setPlumbingAndHVAC($product, $attributes);
                    break;
                }
                case 'Hardware' : {
                    $data['Hardware'] = $this->setHardware($product, $attributes);
                    break;
                }
                case 'BuildingSupply' : {
                    $data['BuildingSupply'] = $this->setBuildingSupply($product, $attributes);
                    break;
                }
                case 'Tools' : {
                    $data['Tools'] = $this->setTools($product, $attributes);
                    break;
                }
                case 'Electrical' : {
                    $data['Electrical'] = $this->setElectrical($product, $attributes);
                    break;
                }
                case 'ToolsAndHardwareOther' : {
                    $data['ToolsAndHardwareOther'] = $this->setToolsAndHardwareOther($product, $attributes);
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Insert PlumbingAndHVAC Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setPlumbingAndHVAC($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','material','shape','globalBrandLicense','size','homeDecorStyle','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','hasFuelContainer','accessoriesIncluded','color','colorCategory/colorCategoryValue','isWaterproof','isIndustrial','isFireResistant','cleaningCareAndMaintenance','recommendedUses','gallonsPerMinute/measure','gallonsPerMinute/unit','mervRating','fuelType','volts/measure','volts/unit','watts/measure','watts/unit','volumeCapacity/measure','volumeCapacity/unit','finish','numberOfBlades','sprayPatterns','roughInDistance','contaminantsRemoved','faucetHandleDesign','hardwareFinish','features','keywords','autographedBy','coverageArea/measure','coverageArea/unit','faucetDrillings','gallonsPerFlush/measure','gallonsPerFlush/unit','hasCeeCertification','ceeTier','horsepower/measure','horsepower/unit','humidificationOutputPerDay','pintsOfMoistureRemovedPerDay','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert Hardware Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setHardware($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','material','shape','globalBrandLicense','homeDecorStyle','finish','mountType','threadStandard','size','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','hasFuelContainer','accessoriesIncluded','color','colorCategory/colorCategoryValue','isWaterproof','isFireResistant','cleaningCareAndMaintenance','recommendedUses','isLockable','lockType','lockingMechanism','backsetSize/measure','backsetSize/unit','maximumWeight/measure','maximumWeight/unit','workingLoadLimit','alphanumericCharacter','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert BuildingSupply Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setBuildingSupply($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','material','shape','globalBrandLicense','coverageArea/measure','coverageArea/unit','form','pattern','paintFinish','recommendedSurfaces','size','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','rollLength/measure','rollLength/unit','thickness/measure','thickness/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','compositeWoodCertificationCode','hasFuelContainer','hasIngredientList','ingredientListImage','ingredients','accessoriesIncluded','color','colorCategory/colorCategoryValue','doorOpeningStyle','isWaterproof','isFireResistant','cleaningCareAndMaintenance','recommendedUses','isMadeFromSustainableMaterials','isMadeFromReclaimedMaterials','isMadeFromRecycledMaterial','recycledMaterialContent','hasLowEmissivity','powerType','isEnergyStarCertified','pileHeight/measure','pileHeight/unit','grade','fineness','isOdorless','vocLevel','features','keywords','dryTime/measure','dryTime/unit','isPrefinished','isReadyToUse','isFastSetting','isMoldResistant','isCombustible','isFlammable','isBiodegradable','isWaterSoluble','peiRating','carpetStyle','acRating','snowLoadRating/measure','snowLoadRating/unit','doorStyle','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert Tools Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setTools($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','material','shape','size','globalBrandLicense','powerType','fuelType','bladeDiameter/measure','bladeDiameter/unit','bladeLength/measure','bladeLength/unit','bladeShank','shankSize/measure','shankSize/unit','chuckType','chuckSize/measure','chuckSize/unit','arborDiameter/measure','arborDiameter/unit','colletSize/measure','colletSize/unit','spindleThread','discSize/measure','discSize/unit','sandingBeltSize','airInlet/measure','airInlet/unit','averageAirConsumptionAt90PSI/measure','averageAirConsumptionAt90PSI/unit','cfmAt40Psi/measure','cfmAt40Psi/unit','cfmAt90Psi/measure','cfmAt90Psi/unit','volts/measure','volts/unit','amps/measure','amps/unit','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','hasFuelContainer','accessoriesIncluded','color','colorCategory/colorCategoryValue','isWaterproof','isIndustrial','isFireResistant','cleaningCareAndMaintenance','recommendedUses','numberOfBlades','bladeWidth','lightBulbType','gritSize','squareDriveSize','socketDepth','numberOfSteps','numberOfPoints','features','keywords','handing','finish','cordLength/measure','cordLength/unit','batteryCapacity/measure','batteryCapacity/unit','engineDisplacement/measure','engineDisplacement/unit','horsepower/measure','horsepower/unit','decibelRating/measure','decibelRating/unit','maximumAirPressure/measure','maximumAirPressure/unit','maximumWattsOut/measure','maximumWattsOut/unit','torque','sandingSpeed/measure','sandingSpeed/unit','noLoadSpeed/measure','noLoadSpeed/unit','strokeLength/measure','strokeLength/unit','strokesPerMinute','blowsPerMinute','impactEnergy/measure','impactEnergy/unit','loadCapacity/measure','loadCapacity/unit','volumeCapacity/measure','volumeCapacity/unit','teethPerInch','maximumJawOpening/measure','maximumJawOpening/unit','tankConfiguration','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert Electrical Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setElectrical($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'lightBulbShape','shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','material','shape','globalBrandLicense','homeDecorStyle','brightness/measure','brightness/unit','powerType','size','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','hasFuelContainer','finish','pattern','isEnergyStarCertified','maximumEnergySurgeRating','estimatedEnergyCostPerYear/measure','estimatedEnergyCostPerYear/unit','compatibleConduitSizes/compatibleConduitSize/measure','compatibleConduitSizes/compatibleConduitSize/unit','volts/measure','volts/unit','amps/measure','amps/unit','watts/measure','watts/unit','lightBulbColor','numberOfLights','shadeMaterial','shadeStyle','accessoriesIncluded','color','colorCategory/colorCategoryValue','baseColor','baseFinish','isWaterproof','isFireResistant','cleaningCareAndMaintenance','recommendedUses','impedance/measure','impedance/unit','conductorMaterial','features','keywords','lightBulbBaseType','electricalBallastFactor','beamAngle/measure','beamAngle/unit','beamSpread/measure','beamSpread/unit','horsepower/measure','horsepower/unit','isDarkSkyCompliant','colorTemperature/measure','colorTemperature/unit','decibelRating/measure','decibelRating/unit','maximumRange','numberOfGangs','numberOfPoles','responseTime/measure','responseTime/unit','americanWireGauge','mountType','isRatedForOutdoorUse','lifespan','character','sportsLeague','sportsTeam','athlete','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert Electrical Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setToolsAndHardwareOther($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','material','shape','size','globalBrandLicense','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','hasFuelContainer','accessoriesIncluded','color','colorCategory/colorCategoryValue','isWaterproof','isFireResistant','cleaningCareAndMaintenance','recommendedUses','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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