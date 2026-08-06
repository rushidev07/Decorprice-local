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


class Electronics extends \Ced\Houzz\Helper\Product\Base
{
    /**
     * Insert Electronics Category Data
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
                case 'VideoProjectors' : {
                    $data['VideoProjectors'] = $this->setVideoProjectors($product, $attributes);
                    break;
                }
                case 'Computers' : {
                    $data['Computers'] = $this->setComputers($product, $attributes);
                    break;
                }
                case 'ElectronicsAccessories' : {
                    $data['ElectronicsAccessories'] = $this->setElectronicsAccessories($product, $attributes);
                    break;
                }
                case 'ComputerComponents' : {
                    $data['ComputerComponents'] = $this->setComputerComponents($product, $attributes);
                    break;
                }
                case 'Software' : {
                    $data['Software'] = $this->setSoftware($product, $attributes);
                    break;
                }
                case 'VideoGames' : {
                    $data['VideoGames'] = $this->setVideoGames($product, $attributes);
                    break;
                }
                case 'PrintersScannersAndImaging' : {
                    $data['PrintersScannersAndImaging'] =
                        $this->setPrintersScannersAndImaging($product, $attributes);
                    break;
                }
                case 'ElectronicsCables' : {
                    $data['ElectronicsCables'] =
                        $this->setElectronicsCables($product, $attributes);
                    break;
                }
                case 'TVsAndVideoDisplays' : {
                    $data['TVsAndVideoDisplays'] =
                        $this->setTVsAndVideoDisplays($product, $attributes);
                    break;
                }
                case 'CellPhones' : {
                    $data['CellPhones'] =
                        $this->setCellPhones($product, $attributes);
                    break;
                }
                case 'ElectronicsOther' : {
                    $data['ElectronicsOther'] =
                        $this->setElectronicsOther($product, $attributes);
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Insert VideoProjectors Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setVideoProjectors($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','resolution','displayTechnology','screenSize/measure','screenSize/unit','brightness/measure','brightness/unit','aspectRatio','throwRatio','has3dCapabilities','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductLength/measure','assembledProductLength/unit','assembledProductWeight/measure','assembledProductWeight/unit','assembledProductWidth/measure','assembledProductWidth/unit','hasBatteries','batteryTechnologyType','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','inputsAndOutputs','maximumContrastRatio','lampLife/measure','lampLife/unit','hasIntegratedSpeakers','wirelessTechnologies','nativeResolution','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert CellPhones Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setCellPhones($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','color','modelName','screenSize/measure','screenSize/unit','mobileOperatingSystem','cellularNetworkTechnology','cellPhoneServiceProvider','hardDriveCapacity/measure','hardDriveCapacity/unit','frontFacingCameraMegapixels/measure','frontFacingCameraMegapixels/unit','rearCameraMegapixels/measure','rearCameraMegapixels/unit','cellPhoneType','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','hasSignalBooster','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','connections','memoryCardType','hasFlash','batteryLife/measure','batteryLife/unit','talkTime/measure','talkTime/unit','standbyTime/measure','standbyTime/unit','wirelessTechnologies','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert TVsAndVideoDisplays Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setTVsAndVideoDisplays($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','resolution','screenSize/measure','screenSize/unit','displayTechnology','televisionType','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductWeight/measure','assembledProductWeight/unit','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','color','colorCategory/colorCategoryValue','connections','memoryCardType','hasTouchscreen','inputsAndOutputs','isEnergyStarCertified','aspectRatio','nativeResolution','maximumContrastRatio','refreshRate/measure','refreshRate/unit','responseTime/measure','responseTime/unit','backlightType','hasIntegratedSpeakers','wirelessTechnologies','audioFeatures','peakAudioPowerCapacity/measure','peakAudioPowerCapacity/unit','audioPowerOutput','features','keywords','streamingServices','mountingPattern','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert ElectronicsCables Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setElectronicsCables($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','color','cableLength/measure','cableLength/unit','compatibleDevices','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','numberOfTwistedPairsPerCable','connectorFinish','connections','dataTransferRate','features','keywords','numberOfChannels','globalBrandLicense','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert PrintersScannersAndImaging Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setPrintersScannersAndImaging($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','monochromeColor','printingTechnology','has3dCapabilities','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','color','connections','memoryCardType','hasAutomaticDocumentFeeder','hasAutomaticTwoSidedPrinting','colorPagesPerMinute','monochromePagesPerMinute','maximumDocumentSize','maximumPrintResolution/measure','maximumPrintResolution/unit','maximumScannerResolution/measure','maximumScannerResolution/unit','wirelessTechnologies','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert VideoGames Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setVideoGames($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','platform','videoGameGenre/videoGameGenreValue','esrbRating','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','ratingReason','sport','edition','releaseDate','ageGroup/ageGroupValue','videoGameCollection','targetAudience','isOnlineMultiplayerAvailable','isDownloadableContentAvailable','requiredPeripherals','physicalMediaFormat','sportsLeague','sportsTeam','athlete','autographedBy','features','keywords','numberOfChannels','globalBrandLicense','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert Software Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setSoftware($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','softwareCategory','isProp65WarningRequired','prop65WarningText','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','operatingSystem','systemRequirements','version','releaseDate','numberOfUsers','requiredPeripherals','educationalFocus','digitalFileFormat','physicalMediaFormat','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert ComputerComponents Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setComputerComponents($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','pieceCount','mainImageUrl','productSecondaryImageURL','hardDriveCapacity/measure','hardDriveCapacity/unit','ramMemory/measure','ramMemory/unit','maximumRamSupported/measure','maximumRamSupported/unit','internalExternal','processorSpeed/measure','processorSpeed/unit','processorType','isProp65WarningRequired','prop65WarningText','hasWarranty','warrantyURL','warrantyText','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','connections','isCordless','memoryCardType','RAMSpeed','cpuSocketType','motherboardFormFactor','wirelessTechnologies','dataIntegrityCheck','numberOfSpeakers','rackSize','RAIDlevel','features','keywords','numberOfChannels','count','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert ElectronicsAccessories Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setElectronicsAccessories($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','color','colorCategory/colorCategoryValue','size','screenSize/measure','screenSize/unit','compatibleBrands','compatibleDevices','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','isChemical','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','connections','memoryCardType','opticalDrive','tvAndMonitorMountType','maximumLoadWeight/measure','maximumLoadWeight/unit','maximumScreenSize/measure','maximumScreenSize/unit','minimumScreenSize/measure','minimumScreenSize/unit','recordableMediaFormats','headphoneFeatures','wirelessTechnologies','audioFeatures','peakAudioPowerCapacity/measure','peakAudioPowerCapacity/unit','audioPowerOutput','dataTransferRate','microphoneTechnology','numberOfSpeakers','mountingPattern','movementDetection','headphoneStyle','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert Computers Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setComputers($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','color','colorCategory/colorCategoryValue','screenSize/measure','screenSize/unit','resolution','displayTechnology','hardDriveCapacity/measure','hardDriveCapacity/unit','ramMemory/measure','ramMemory/unit','maximumRamSupported/measure','maximumRamSupported/unit','internalExternal','processorSpeed/measure','processorSpeed/unit','processorType','computerStyle','frontFacingCameraMegapixels/measure','frontFacingCameraMegapixels/unit','rearCameraMegapixels/measure','rearCameraMegapixels/unit','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','operatingSystem','RAMSpeed','hasTouchscreen','connections','memoryCardType','opticalDrive','graphicsInformation','formFactor','hasSignalBooster','wirelessTechnologies','batteryLife/measure','batteryLife/unit','dataIntegrityCheck','isPortable','features','keywords','numberOfChannels','globalBrandLicense','RAIDlevel','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert Computers Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setElectronicsOther($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','brand','manufacturer','manufacturerPartNumber','modelNumber','multipackQuantity','countPerPack','count','pieceCount','mainImageUrl','productSecondaryImageURL','platform','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','isProp65WarningRequired','prop65WarningText','batteryTechnologyType','hasWarranty','warrantyURL','warrantyText','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','color','colorCategory/colorCategoryValue','connections','isCordless','ageGroup/ageGroupValue','memoryCardType','wirelessTechnologies','audioFeatures','peakAudioPowerCapacity/measure','peakAudioPowerCapacity/unit','audioPowerOutput','resolution','dataTransferRate','streamingServices','speakerDriver','numberOfSpeakers','impedance/measure','impedance/unit','microphoneTechnology','digitalAudioFileFormat','isPortable','features','keywords','numberOfChannels','supportedMediaFormats','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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