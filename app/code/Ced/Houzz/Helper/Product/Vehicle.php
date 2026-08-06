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

class Vehicle extends \Ced\Houzz\Helper\Product\Base
{
    /**
     * Insert Vehicle Category Data
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
                        ->ke
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
                case 'WheelsAndWheelComponents' : {
                    $data['WheelsAndWheelComponents'] = $this->setWheelsAndWheelComponents($product, $attributes);
                    break;
                }
                case 'LandVehicles' : {
                    $data['LandVehicles'] = $this->setLandVehicles($product, $attributes);
                    break;
                }
                case 'VehiclePartsAndAccessories' : {
                    $data['VehiclePartsAndAccessories'] = $this->setVehiclePartsAndAccessories($product, $attributes);
                    break;
                }
                case 'Tires' : {
                    $data['Tires'] = $this->setTires($product, $attributes);
                    break;
                }
                case 'Watercraft' : {
                    $data['Watercraft'] = $this->setWatercraft($product, $attributes);
                    break;
                }
                case 'VehicleOther' : {
                    $data['VehicleOther'] = $this->setVehicleOther($product, $attributes);
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Insert WheelsAndWheelComponents Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setWheelsAndWheelComponents($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','color','finish','material','vehicleRimSize','diameter/measure','diameter/unit','compatibleTireSize','numberOfSpokes','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert LandVehicles Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setLandVehicles($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','mainImageUrl','productSecondaryImageURL','color','vehicleType','vehicleYear','vehicleMake','vehicleModel','submodel','powertrain','drivetrain','transmissionDesignation','engineModel','engineDisplacement/measure','engineDisplacement/unit','boreStroke','inductionSystem','compressionRatio','maximumEnginePower','torque','acceleration','topSpeed','coolingSystem','fuelRequirement','fuelSystem','fuelCapacity/measure','fuelCapacity/unit','averageFuelConsumption/measure','averageFuelConsumption/unit','frontSuspension','rearSuspension','frontBrakes','rearBrakes','frontWheels','rearWheels','frontTires','rearTires','seatingCapacity','seatHeight/measure','seatHeight/unit','wheelbase/measure','wheelbase/unit','curbWeight/measure','curbWeight/unit','towingCapacity/measure','towingCapacity/unit','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','hasFuelContainer','compositeWoodCertificationCode','requiresTextileActLabeling','countryOfOriginTextiles','fabricContent','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert VehiclePartsAndAccessories Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setVehiclePartsAndAccessories($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','unitsPerConsumerUnit','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','color','form','shape','size','finish','fillMaterial','compatibleCars','compatibleBrands','compatibleDevices','isPowered','powerType','fluidOunces/measure','fluidOunces/unit','amps/measure','amps/unit','coldCrankAmp','beamSpread/measure','beamSpread/unit','beamAngle/measure','beamAngle/unit','inDashSystem','interfaceType','displayTechnology','displayResolution','fastenerHeadType','connections','cableLength/measure','cableLength/unit','chainLength/measure','chainLength/unit','candlePower','fuelType','flashPoint','filterLife','lightBulbType','isLockable','isReusable','breakingStrength/measure','breakingStrength/unit','maximumMotorSpeed','maximumTemperature/measure','maximumTemperature/unit','numberOfOutlets','receiverCompatibility/measure','receiverCompatibility/unit','reserveCapacity/measure','reserveCapacity/unit','loadCapacity/measure','loadCapacity/unit','horsepower/measure','horsepower/unit','saeDotCompliant','shackleClearance/measure','shackleClearance/unit','shackleDiameter/measure','shackleDiameter/unit','shackleLength/measure','shackleLength/unit','shankLength/measure','shankLength/unit','shearStrength/measure','shearStrength/unit','hitchClass','dropDistance/measure','dropDistance/unit','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','isAerosol','isChemical','compositeWoodCertificationCode','hasWarranty','warrantyURL','warrantyText','hasFuelContainer','vehicleType','motorOilViscosity','fabricContent','fabricCareInstructions','sportsLeague','sportsTeam','athlete','autographedBy','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert Tires  Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setTires($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','modelNumber','manufacturerPartNumber','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','color','tireSize','vehicleClassDesignator','tireWidth','tireAspectRatio','tireSpeedRating','wheelDiameter','tireLoadRange','overallDiameter/measure','overallDiameter/unit','tireSeason','mudAndSnowRated','isRunFlat','constructionType','treadDepth','treadWidth','tireLoadIndex','tireTreadwearRating','tireTractionRating','tireTemperatureRating','tireSidewallStyle','maximumInflationPressure/measure','maximumInflationPressure/unit','uniformTireQualityGrade','tireType','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','hasWarranty','warrantyURL','warrantyText','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert Watercraft Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setWatercraft($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','mainImageUrl','productSecondaryImageURL','color','vehicleType','vehicleYear','vehicleMake','vehicleModel','submodel','engineLocation','engineModel','engineDisplacement/measure','engineDisplacement/unit','boreStroke','inductionSystem','compressionRatio','maximumEnginePower','propulsionSystem','coolingSystem','thrust/measure','thrust/unit','impellerPropeller','topSpeed','fuelRequirement','fuelSystem','fuelCapacity/measure','fuelCapacity/unit','averageFuelConsumption/measure','averageFuelConsumption/unit','hullLength/measure','hullLength/unit','beam/measure','beam/unit','airDraft/measure','airDraft/unit','draft/measure','draft/unit','dryWeight/measure','dryWeight/unit','waterCapacity/measure','waterCapacity/unit','seatingCapacity','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','smallPartsWarnings/smallPartsWarning','hasBatteries','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','sportsLeague','athlete','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert VehicleOther Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setVehicleOther($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','color','size','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','hasFuelContainer','requiresTextileActLabeling','countryOfOriginTextiles','fabricContent','vehicleType','sportsLeague','sportsTeam','athlete','autographedBy','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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