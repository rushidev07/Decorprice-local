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


class Media extends \Ced\Houzz\Helper\Product\Base
{
    /**
     * Insert Media Category Data
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
                case 'TVShows' : {
                    $data['TVShows'] = $this->setTVShows($product, $attributes);
                    break;
                }
                case 'Music' : {
                    $data['Music'] = $this->setMusic($product, $attributes);
                    break;
                }
                case 'BooksAndMagazines' : {
                    $data['BooksAndMagazines'] = $this->setBooksAndMagazines($product, $attributes);
                    break;
                }
                case 'Movies' : {
                    $data['Movies'] = $this->setMovies($product, $attributes);
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Insert TVShows Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setTVShows($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','synopsis','countPerPack','multipackQuantity','count','mainImageUrl','productSecondaryImageURL','title','physicalMediaFormat','tvRating','ratingReason','tvShowGenre','tvShowSubgenre','tvNetwork','seriesTitle','numberInSeries','tvShowSeason','character','numberOfEpisodes','episode','director','actors','screenwriter','studioProductionCompany','targetAudience','awardsWon','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','isAdultProduct','numberOfDiscs','originalLanguages','edition','releaseDate','duration/measure','duration/unit','hasSubtitles','subtitledLanguages','isDubbed','dubbedLanguages','audioTrackCodec','aspectRatio','sportsLeague','sportsTeam','athlete','features','keywords','dvdReleaseDate','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert Music Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setMusic($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','synopsis','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','title','physicalMediaFormat','performer','songwriter','musicGenre','musicSubGenre','targetAudience','awardsWon','character','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','digitalAudioFileFormat','recordLabel','releaseDate','musicReleaseType','trackListings','numberOfTracks','musicProducer','seriesTitle','numberInSeries','isEdited','isEnhanced','edition','hasParentalAdvisoryLabel','ratingReason','parentalAdvisoryLabelURL','numberOfDiscs','isAdultProduct','originalLanguages','autographedBy','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert BooksAndMagazines Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setBooksAndMagazines($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','synopsis','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','title','bookFormat','author','publisher','publicationDate','originalPublicationDate','targetAudience','awardsWon','character','fictionNonfiction','genre','subgenre','subject','seriesTitle','numberInSeries','issue','assembledProductLength/measure','assembledProductLength/unit','assembledProductWidth/measure','assembledProductWidth/unit','assembledProductHeight/measure','assembledProductHeight/unit','assembledProductWeight/measure','assembledProductWeight/unit','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','smallPartsWarnings/smallPartsWarning','isAdultProduct','edition','numberOfDiscs','originalLanguages','numberOfPages','isUnabridged','isLargePrint','readingLevel','editor','translator','translatedFrom','illustrator','bisacSubjectCodes','sportsLeague','sportsTeam','athlete','autographedBy','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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
     * Insert Movies Category Data
     * @param string|array() $product
     * @param string|array() $attributes
     * @return string|array()
     */
    public function setMovies($product = array(), $attributes = array())
    {
        $houzzAttr = array(
            'shortDescription','keyFeatures','synopsis','multipackQuantity','countPerPack','count','mainImageUrl','productSecondaryImageURL','title','physicalMediaFormat','mpaaRating','ratingReason','movieGenre','movieSubgenre','seriesTitle','numberInSeries','director','actors','screenwriter','studioProductionCompany','targetAudience','awardsWon','character','variantGroupId','variantAttributeNames/variantAttributeName','isPrimaryVariant','duration/measure','duration/unit','theatricalReleaseDate','isDubbed','dubbedLanguages','hasSubtitles','subtitledLanguages','audioTrackCodec','aspectRatio','isAdultProduct','originalLanguages','edition','numberOfDiscs','sportsLeague','sportsTeam','athlete','autographedBy','features','keywords','swatchImages/swatchImage/swatchVariantAttribute','swatchImages/swatchImage/swatchImageUrl'
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