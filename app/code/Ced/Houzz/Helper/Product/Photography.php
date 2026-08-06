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


class Photography extends \Ced\Houzz\Helper\Product\Base
{
    /**
     * Insert Photography Category Data
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
                case 'PhotoAccessories' : {
                    $data['PhotoAccessories'] = $this->setPhotoAccessories($product, $attributes);
                    break;
                }
                case 'CamerasAndLenses' : {
                    $data['CamerasAndLenses'] = $this->setCamerasAndLenses($product, $attributes);
                    break;
                }
            }
        }

        return $data;
    }


    /**
     * Insert Photography/PhotoAccessories Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setPhotoAccessories($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','compatibleBrands','compatibleDevices','color','colorCategory/colorCategoryValue','material','memoryCardType','recordableMediaFormats','connections','size','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','globalBrandLicense','isAssemblyRequired','assemblyInstructions','wirelessTechnologies','displayTechnology','accessoriesIncluded','isWaterproof','recommendedUses','cleaningCareAndMaintenance','shape','pattern','capacity','inputsAndOutputs','lightOutput/measure','lightOutput/unit','lightBulbType','volts/measure','volts/unit','watts/measure','watts/unit','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert Photography/CamerasAndLenses Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setCamerasAndLenses($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','numberOfMegapixels/measure','numberOfMegapixels/unit','digitalZoom','opticalZoom','magnification','sensorResolution','lensDiameter/measure','lensDiameter/unit','lensCoating','lensFilterType','cameraLensType','hasFlash','flashType','minimumShutterSpeed/measure','minimumShutterSpeed/unit','maximumShutterSpeed/measure','maximumShutterSpeed/unit','focusType','focalLength/measure','focalLength/unit','focalRatio','minimumAperture','maximumAperture','exposureModes','displayTechnology','displayResolution','screenSize/measure','screenSize/unit','diameter/measure','diameter/unit','color','colorCategory/colorCategoryValue','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','isAssemblyRequired','assemblyInstructions','hasWarranty','warrantyURL','warrantyText','accessoriesIncluded','hasTouchscreen','hasMemoryCardSlot','memoryCardType','recordableMediaFormats','wirelessTechnologies','connections','isPortable','isWaterproof','hasSelfTimer','selfTimerDelay','standbyTime/measure','standbyTime/unit','fieldOfView','isParfocal','shootingMode','microphoneIncluded','hasHandle','isMulticoated','hasRedEyeReduction','hasNightVision','isFogResistant','attachmentStyle','hasShoulderStrap','compatibleBrands','compatibleDevices','material','cleaningCareAndMaintenance','globalBrandLicense','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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