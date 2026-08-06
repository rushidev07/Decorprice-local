<?php
namespace MageNJ\RapidflowNameTruncate\Observer;

use Magento\Framework\Event\ObserverInterface;

class DataManipulation implements ObserverInterface
{

    /**
     * @var \Unirgy\RapidFlow\Helper\Url
     */
    protected $helper;

    /**
     * CategoryUrlUpdateObserver constructor.
     * @param \Unirgy\RapidFlow\Helper\Url $helper
     */
    public function __construct(\Unirgy\RapidFlow\Helper\Url $helper)
    {
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Unirgy\RapidFlow\Model\Profile $profile */
        $vars = $observer->getEvent()->getVars();
        $profile = $vars['profile'];
        $profile_id = $profile->getData('profile_id');

        if (($profile_id=="17") || ($profile_id=="174") || ($profile_id=="173") || ($profile_id=="31") || ($profile_id=="94") || ($profile_id=="96") || ($profile_id=="97") || ($profile_id=="68") || ($profile_id=="58") || ($profile_id=="22") || ($profile_id=="83") || ($profile_id=="105") || ($profile_id=="107"))  {

            $fieldKey1 = $vars['fields']['title']['column_num']; $fieldName1 = 'title';
            $fieldKey2 = $vars['fields']['custom label 0']['column_num']; $fieldName2 = 'custom label 0';
            $fieldKey3 = $vars['fields']['link']['column_num']; $fieldName3 = 'link';
            $fieldKey4 = isset($vars['fields']['config_url']['column_num']) ? $vars['fields']['config_url']['column_num'] : 0; $fieldName4 = 'config_url';
            $fieldKey5 = $vars['fields']['adwords_grouping']['column_num']; $fieldName5 = 'adwords_grouping';
            $fieldKey6 = $vars['fields']['image link']['column_num']; $fieldName6 = 'image link';
            $fieldKey7 = $vars['fields']['id']['column_num']; $fieldName7 = 'id';
            //for best seller in Googlebase Afsupply
            if(($profile_id=="17") || ($profile_id=="174") || ($profile_id=="173") || ($profile_id=="31") || ($profile_id=="94") || ($profile_id=="96") || ($profile_id=="97") || ($profile_id=="83") || ($profile_id=="105") || ($profile_id=="107")){
                $fieldKey8 = isset($vars['fields']['custom label 1']['column_num']) ? $vars['fields']['custom label 1']['column_num'] : 0; $fieldName8 = 'custom label 1';
            }
            if(($profile_id=="17") || ($profile_id=="174") || ($profile_id=="173") || ($profile_id=="31") || ($profile_id=="94") || ($profile_id=="96") || ($profile_id=="97") || ($profile_id=="105") || ($profile_id=="107")){
                $fieldKey11 = $vars['fields']['google_product_category']['column_num']; $fieldName11 = 'google_product_category';
            }
            $fieldKey9 = $vars['fields']['price']['column_num']; $fieldName9 = 'price';
            $fieldKey10 = $vars['fields']['custom label 4']['column_num']; $fieldName10 = 'custom label 4';
            $fieldKey12 = $vars['fields']['min_handling_time']['column_num']; $fieldName12 = 'min_handling_time';
            $fieldKey13 = $vars['fields']['max_handling_time']['column_num']; $fieldName13 = 'max_handling_time';
            $website = "https://www.decorprice.com/";
            if($profile_id=="96")
            {
                $website = "https://www.justicedesignlightingselection.com/";
            }
            if($profile_id=="105")
            {
                $website = "https://www.jamesmoderlightingselection.com/";
            }
            if($profile_id=="107")
            {
                $website = "https://www.livexlightingselection.com/";
            }
            if($profile_id=="174")
            {
                $website = "https://www.lightingselection.com/";
            }
            //We need to subtract 1 from column_num to offset the difference between column_num and the array key for $vars['rows']
            $fieldValue1 = $fieldValue2 = $fieldValue3 = $fieldValue4 = $fieldValue5 = $fieldValue6 = $fieldValue7 = $fieldValue8 = $fieldValue9 = $fieldValue10 = $fieldValue11 = $fieldValue12 = $fieldValue13 = false; // init field index to false
            foreach ($vars['rows'] as &$row) {
                if( !$fieldValue1 ) { // check to use numeric or text indexes, but just once per call
                    $fieldValue1 = array_key_exists($fieldName1, $row)? $fieldName1: $fieldKey1-1;
                }

                if (!$fieldValue2) {
                    $fieldValue2 = array_key_exists($fieldName2, $row)? $fieldName2: $fieldKey2-1;
                }

                if (!$fieldValue3) {
                    $fieldValue3 = array_key_exists($fieldName3, $row)? $fieldName3: $fieldKey3-1;
                }

                if (!$fieldValue4) {
                    $fieldValue4 = array_key_exists($fieldName4, $row)? $fieldName4: $fieldKey4-1;
                }
                if (!$fieldValue5) {
                    $fieldValue5 = array_key_exists($fieldName5, $row)? $fieldName5: $fieldKey5-1;
                }

                if (!$fieldValue6) {
                    $fieldValue6 = array_key_exists($fieldName6, $row)? $fieldName6: $fieldKey6-1;
                }

                if (!$fieldValue7) {
                    $fieldValue7 = array_key_exists($fieldName7, $row)? $fieldName7: $fieldKey7-1;
                }

                //for best seller in Googlebase Afsupply
                if(($profile_id=="17") || ($profile_id=="174") || ($profile_id=="173") || ($profile_id=="31") || ($profile_id=="94") || ($profile_id=="96") || ($profile_id=="97") || ($profile_id=="83") || ($profile_id=="105") || ($profile_id=="107")){
                    if (!$fieldValue8) {
                        $fieldValue8 = array_key_exists($fieldName8, $row)? $fieldName8: $fieldKey8-1;
                    }
                }

                if (!$fieldValue9) {
                    $fieldValue9 = array_key_exists($fieldName9, $row)? $fieldName9: $fieldKey9-1;
                }

                if (!$fieldValue10) {
                    $fieldValue10 = array_key_exists($fieldName10, $row)? $fieldName10: $fieldKey10-1;
                }

                if (!$fieldValue11) {
                    $fieldValue11 = array_key_exists($fieldName11, $row)? $fieldName11: $fieldKey11-1;
                }

                if (!$fieldValue12) {
                    $fieldValue12 = array_key_exists($fieldName12, $row)? $fieldName12: $fieldKey12-1;
                }

                if (!$fieldValue13) {
                    $fieldValue13 = array_key_exists($fieldName13, $row)? $fieldName13: $fieldKey13-1;
                }

                if(isset($row[$fieldValue1])){
                    $row[$fieldValue1] = substr($row[$fieldValue1], 0, 150);

                }

                if(isset($row[$fieldValue2])){
                    $qty=$row[$fieldValue2];
                    if($qty > 0) {
                        $row[$fieldValue2]="instock";
                        $row[$fieldValue5]="instock";
                        if(($profile_id=="17") || ($profile_id=="174") || ($profile_id=="173") || ($profile_id=="31") || ($profile_id=="94") || ($profile_id=="96") || ($profile_id=="97") || ($profile_id=="105") || ($profile_id=="107"))
                        {
                            $price = intval($row[$fieldValue9]);
                            if($price>=1500)
                            {
                                $promo = $row[$fieldValue10];
                                if(trim($promo)=="")
                                {
                                    $row[$fieldValue10] = "HIGH";
                                }
                                else
                                {
                                    $row[$fieldValue10] .= "; HIGH";
                                }
                            }
                        }

                    }
                    else {
                        $row[$fieldValue2]="outofstock";
                        $row[$fieldValue5]="";
                    }

                }

                $row[$fieldValue3] = $website.($row[$fieldValue3]).".html";

                if(isset($row[$fieldValue4])){

                    $row[$fieldValue3] = $row[$fieldValue4];

                }

                if(isset($row[$fieldValue7])){
                    $image=$row[$fieldValue6];
                    $sku=$row[$fieldValue7];
                    if($image=="") {
                        $row[$fieldValue6] = "https://www.decorprice.com/media/catalog/standard/$sku.jpg";
                        if($profile_id=="96")
                        {
                            $row[$fieldValue6] = "https://www.justicedesignlightingselection.com/media/catalog/standard/$sku.jpg";
                        }
                        if($profile_id=="105")
                        {
                            $row[$fieldValue6] = "https://www.jamesmoderlightingselection.com/media/catalog/standard/$sku.jpg";
                        }
                        if($profile_id=="107")
                        {
                            $row[$fieldValue6] = "https://www.livexlightingselection.com/media/catalog/standard/$sku.jpg";
                        }
                    }
                }

                //for best seller in Googlebase Afsupply
                if(($profile_id=="17") || ($profile_id=="174") || ($profile_id=="173") || ($profile_id=="31") || ($profile_id=="94") || ($profile_id=="96") || ($profile_id=="97") || ($profile_id=="83") || ($profile_id=="105") || ($profile_id=="107")){
                    if (isset($row[$fieldValue8])) {
                        $best_seller = $row[$fieldValue8];
                        if ($best_seller != "Yes") {
                            $row[$fieldValue8] = "No";
                        }
                    }
                }

                if(($profile_id=="17") || ($profile_id=="174") || ($profile_id=="173") || ($profile_id=="31") || ($profile_id=="94") || ($profile_id=="96") || ($profile_id=="97") || ($profile_id=="105") || ($profile_id=="107"))
                {
                    $category_ids=$row[$fieldValue11];

                }

                if(($profile_id=="17") || ($profile_id=="174") || ($profile_id=="173") || ($profile_id=="31") || ($profile_id=="50") || ($profile_id=="183") || ($profile_id=="97") || ($profile_id=="105") || ($profile_id=="107"))
                {
                    if($row['custom label 0'] == "instock")
                    {
                        $time_toship = $row[$fieldValue12];
                        if($time_toship=="1-2 days")
                        {
                            $row['min_handling_time'] = "1";
                            $row['max_handling_time'] = "2";
                        }
                        elseif ($time_toship=="1-3 days")
                        {
                            $row['min_handling_time'] = "1";
                            $row['max_handling_time'] = "3";
                        }
                        elseif ($time_toship=="2-3 days")
                        {
                            $row['min_handling_time'] = "2";
                            $row['max_handling_time'] = "3";
                        }
                        elseif ($time_toship=="2-4 days")
                        {
                            $row['min_handling_time'] = "2";
                            $row['max_handling_time'] = "4";
                        }
                        elseif ($time_toship=="3-4 days")
                        {
                            $row['min_handling_time'] = "3";
                            $row['max_handling_time'] = "4";
                        }
                        elseif ($time_toship=="3-5 days")
                        {
                            $row['min_handling_time'] = "3";
                            $row['max_handling_time'] = "5";
                        }
                        elseif ($time_toship=="4-5 days")
                        {
                            $row['min_handling_time'] = "4";
                            $row['max_handling_time'] = "5";
                        }
                        elseif ($time_toship=="4-7 days")
                        {
                            $row['min_handling_time'] = "4";
                            $row['max_handling_time'] = "7";
                        }
                        elseif ($time_toship=="5-6 days")
                        {
                            $row['min_handling_time'] = "5";
                            $row['max_handling_time'] = "6";
                        }
                        elseif ($time_toship=="5-6 biz days")
                        {
                            $row['min_handling_time'] = "5";
                            $row['max_handling_time'] = "6";
                        }
                        elseif ($time_toship=="1-2 weeks")
                        {
                            $row['min_handling_time'] = "7";
                            $row['max_handling_time'] = "14";
                        }
                        elseif ($time_toship=="2-3 weeks")
                        {
                            $row['min_handling_time'] = "14";
                            $row['max_handling_time'] = "21";
                        }
                        elseif ($time_toship=="3-4 weeks")
                        {
                            $row['min_handling_time'] = "21";
                            $row['max_handling_time'] = "28";
                        }
                        elseif ($time_toship=="4-6 weeks")
                        {
                            $row['min_handling_time'] = "28";
                            $row['max_handling_time'] = "42";
                        }
                        elseif ($time_toship=="6-8 weeks")
                        {
                            $row['min_handling_time'] = "42";
                            $row['max_handling_time'] = "56";
                        }
                        elseif ($time_toship=="same day")
                        {
                            $row['min_handling_time'] = "0";
                            $row['max_handling_time'] = "1";
                        }
                        else
                        {
                            $row['min_handling_time'] = "";
                            $row['max_handling_time'] = "";
                        }
                    }
                    else
                    {
                        $row['min_handling_time'] = "";
                        $row['max_handling_time'] = "";
                    }

                }

                unset($row['config_url']);
            }

            $observer->setData('vars', $vars);

        }

        if ($profile_id=="63")  {
            $fieldKey1 = $vars['fields']['availability']['column_num']; $fieldName1 = 'availability';
            $fieldValue1 = false;

            foreach ($vars['rows'] as &$row) {
                if( !$fieldValue1 ) { // check to use numeric or text indexes, but just once per call
                    $fieldValue1 = array_key_exists($fieldName1, $row)? $fieldName1: $fieldKey1-1;
                }

                if(isset($row[$fieldValue1])){
                    $qty=$row[$fieldValue1];
                    if($qty > 0) {
                        $row[$fieldValue1]="in stock";
                    }
                    else {
                        $row[$fieldValue1]="out of stock";
                    }
                }
            }
            $observer->setData('vars', $vars);
        }

        if ($profile_id=="85")  {
            $fieldKey1 = $vars['fields']['quantity']['column_num']; $fieldName1 = 'quantity';
            $fieldValue1 = false;

            foreach ($vars['rows'] as &$row) {
                if( !$fieldValue1 ) { // check to use numeric or text indexes, but just once per call
                    $fieldValue1 = array_key_exists($fieldName1, $row)? $fieldName1: $fieldKey1-1;
                }

                if(isset($row[$fieldValue1])){
                    $qty=$row[$fieldValue1];
                    if($qty < 3) {
                        $row[$fieldValue1] = 0;
                    }
                }
            }
            $observer->setData('vars', $vars);
        }

        if(($profile_id=="50") || ($profile_id=="183") || ($profile_id=="84")) {

            $fieldKey1 = $vars['fields']['title']['column_num']; $fieldName1 = 'title';
            $fieldKey2 = $vars['fields']['custom label 0']['column_num']; $fieldName2 = 'custom label 0';
            $fieldKey3 = $vars['fields']['link']['column_num']; $fieldName3 = 'link';
            //$fieldKey4 = $vars['fields']['config_url']['column_num']; $fieldName4 = 'config_url';
            $fieldKey5 = $vars['fields']['adwords_grouping']['column_num']; $fieldName5 = 'adwords_grouping';
            $fieldKey6 = $vars['fields']['image link']['column_num']; $fieldName6 = 'image link';
            $fieldKey7 = $vars['fields']['id']['column_num']; $fieldName7 = 'id';
            $fieldKey8 = $vars['fields']['google_product_category']['column_num']; $fieldName8 = 'google_product_category';
            $fieldKey12 = $vars['fields']['min_handling_time']['column_num']; $fieldName12 = 'min_handling_time';
            $fieldKey13 = $vars['fields']['max_handling_time']['column_num']; $fieldName13 = 'max_handling_time';
            $website = "https://www.lightingselection.com/";
            //We need to subtract 1 from column_num to offset the difference between column_num and the array key for $vars['rows']
            $fieldValue1 = $fieldValue2 = $fieldValue3 = $fieldValue5 = $fieldValue6 = $fieldValue7 = $fieldValue8 = $fieldValue12 = $fieldValue13 = false; // init field index to false
            foreach ($vars['rows'] as &$row) {
                if( !$fieldValue1 ) { // check to use numeric or text indexes, but just once per call
                    $fieldValue1 = array_key_exists($fieldName1, $row)? $fieldName1: $fieldKey1-1;
                }
                if (!$fieldValue2) {$fieldValue2 = array_key_exists($fieldName2, $row)? $fieldName2: $fieldKey2-1;}
                if (!$fieldValue3) {$fieldValue3 = array_key_exists($fieldName3, $row)? $fieldName3: $fieldKey3-1;}
                if (!$fieldValue5) {$fieldValue5 = array_key_exists($fieldName5, $row)? $fieldName5: $fieldKey5-1;}
                if (!$fieldValue6) {$fieldValue6 = array_key_exists($fieldName6, $row)? $fieldName6: $fieldKey6-1;}
                if (!$fieldValue7) {$fieldValue7 = array_key_exists($fieldName7, $row)? $fieldName7: $fieldKey7-1;}
                if (!$fieldValue8) {$fieldValue8 = array_key_exists($fieldName8, $row)? $fieldName8: $fieldKey8-1;}
                if (!$fieldValue12) {$fieldValue12 = array_key_exists($fieldName12, $row)? $fieldName12: $fieldKey12-1;}
                if (!$fieldValue13) {$fieldValue13 = array_key_exists($fieldName13, $row)? $fieldName13: $fieldKey13-1;}

                if(isset($row[$fieldValue1])){$row[$fieldValue1] = substr($row[$fieldValue1], 0, 150);}
                if(isset($row[$fieldValue2])){
                    $qty=$row[$fieldValue2];
                    if($qty > 0) {
                        $row[$fieldValue2]="instock";
                        $row[$fieldValue5]="instock";
                    }
                    else {
                        $row[$fieldValue2]="outofstock";
                        $row[$fieldValue5]="";
                    }
                }
                if(isset($row[$fieldValue3])){$row[$fieldValue3] = $website.($row[$fieldValue3]).".html";}

                if(isset($row[$fieldValue7])){
                    $image=$row[$fieldValue6];
                    $sku=$row[$fieldValue7];
                    if($image=="") {
                        $row[$fieldValue6] = "https://www.lightingselection.com/media/catalog/standard/$sku.jpg";
                    }
                }

                if($profile_id=="50" || ($profile_id=="183"))
                {
                    if($row['custom label 0'] == "instock")
                    {
                        $time_toship = $row[$fieldValue12];
                        if($time_toship=="1-2 days")
                        {
                            $row['min_handling_time'] = "1";
                            $row['max_handling_time'] = "2";
                        }
                        elseif ($time_toship=="1-3 days")
                        {
                            $row['min_handling_time'] = "1";
                            $row['max_handling_time'] = "3";
                        }
                        elseif ($time_toship=="2-3 days")
                        {
                            $row['min_handling_time'] = "2";
                            $row['max_handling_time'] = "3";
                        }
                        elseif ($time_toship=="2-4 days")
                        {
                            $row['min_handling_time'] = "2";
                            $row['max_handling_time'] = "4";
                        }
                        elseif ($time_toship=="3-4 days")
                        {
                            $row['min_handling_time'] = "3";
                            $row['max_handling_time'] = "4";
                        }
                        elseif ($time_toship=="3-5 days")
                        {
                            $row['min_handling_time'] = "3";
                            $row['max_handling_time'] = "5";
                        }
                        elseif ($time_toship=="4-5 days")
                        {
                            $row['min_handling_time'] = "4";
                            $row['max_handling_time'] = "5";
                        }
                        elseif ($time_toship=="4-7 days")
                        {
                            $row['min_handling_time'] = "4";
                            $row['max_handling_time'] = "7";
                        }
                        elseif ($time_toship=="5-6 days")
                        {
                            $row['min_handling_time'] = "5";
                            $row['max_handling_time'] = "6";
                        }
                        elseif ($time_toship=="5-6 biz days")
                        {
                            $row['min_handling_time'] = "5";
                            $row['max_handling_time'] = "6";
                        }
                        elseif ($time_toship=="1-2 weeks")
                        {
                            $row['min_handling_time'] = "7";
                            $row['max_handling_time'] = "14";
                        }
                        elseif ($time_toship=="2-3 weeks")
                        {
                            $row['min_handling_time'] = "14";
                            $row['max_handling_time'] = "21";
                        }
                        elseif ($time_toship=="3-4 weeks")
                        {
                            $row['min_handling_time'] = "21";
                            $row['max_handling_time'] = "28";
                        }
                        elseif ($time_toship=="4-6 weeks")
                        {
                            $row['min_handling_time'] = "28";
                            $row['max_handling_time'] = "42";
                        }
                        elseif ($time_toship=="6-8 weeks")
                        {
                            $row['min_handling_time'] = "42";
                            $row['max_handling_time'] = "56";
                        }
                        elseif ($time_toship=="same day")
                        {
                            $row['min_handling_time'] = "0";
                            $row['max_handling_time'] = "1";
                        }
                        else
                        {
                            $row['min_handling_time'] = "";
                            $row['max_handling_time'] = "";
                        }
                    }
                    else
                    {
                        $row['min_handling_time'] = "";
                        $row['max_handling_time'] = "";
                    }

                    $category_ids=$row[$fieldValue8];

                }

            }

            $observer->setData('vars', $vars);

        }

        if ($profile_id=="22") {

            $fieldKey1 = $vars['fields']['title']['column_num']; $fieldName1 = 'title';
            $fieldKey2 = $vars['fields']['availability']['column_num']; $fieldName2 = 'availability';
            $fieldKey3 = $vars['fields']['link']['column_num']; $fieldName3 = 'link';
            $fieldKey4 = $vars['fields']['config_url']['column_num']; $fieldName4 = 'config_url';
            $fieldKey5 = $vars['fields']['adwords_grouping']['column_num']; $fieldName5 = 'adwords_grouping';
            $website = "https://www.decorprice.com/";
            //We need to subtract 1 from column_num to offset the difference between column_num and the array key for $vars['rows']
            $fieldValue1 = $fieldValue2 = $fieldValue3 = $fieldValue4 = $fieldValue5 = false; // init field index to false
            foreach ($vars['rows'] as &$row) {
                if( !$fieldValue1 ) { // check to use numeric or text indexes, but just once per call
                    $fieldValue1 = array_key_exists($fieldName1, $row)? $fieldName1: $fieldKey1-1;
                }

                if (!$fieldValue2) {
                    $fieldValue2 = array_key_exists($fieldName2, $row)? $fieldName2: $fieldKey2-1;
                }

                if (!$fieldValue3) {
                    $fieldValue3 = array_key_exists($fieldName3, $row)? $fieldName3: $fieldKey3-1;
                }

                if (!$fieldValue4) {
                    $fieldValue4 = array_key_exists($fieldName4, $row)? $fieldName4: $fieldKey4-1;
                }
                if (!$fieldValue5) {
                    $fieldValue5 = array_key_exists($fieldName5, $row)? $fieldName5: $fieldKey5-1;
                }



                if(isset($row[$fieldValue1])){
                    $row[$fieldValue1] = substr($row[$fieldValue1], 0, 68);

                }

                if(isset($row[$fieldValue2])){
                    $qty=$row[$fieldValue2];
                    if($qty > 0) {
                        $row[$fieldValue2]="in stock";
                        $row[$fieldValue5]="instock";
                    }
                    else {
                        $row[$fieldValue2]="available for order";
                    }

                }
                if(isset($row[$fieldValue3])){
                    $row[$fieldValue3] = $website.($row[$fieldValue3]).".html";

                }
                if(isset($row[$fieldValue4])){

                    $row[$fieldValue3] = $row[$fieldValue4];

                }

                unset($row['config_url']);
            }

            $observer->setData('vars', $vars);

        }

        if ($profile_id=="28") {

            $fieldKey3 = $vars['fields']['ProductUrl']['column_num']; $fieldName3 = 'ProductUrl';
            //$fieldKey4 = $vars['fields']['config_url']['column_num']; $fieldName4 = 'config_url';
            $fieldKey5 = $vars['fields']['DocumentURL1']['column_num']; $fieldName5 = 'DocumentURL1';
            $fieldKey6 = $vars['fields']['DocumentURL2']['column_num']; $fieldName6 = 'DocumentURL2';
            $fieldKey7 = $vars['fields']['DocumentURL3']['column_num']; $fieldName7 = 'DocumentURL3';
            $fieldKey8 = $vars['fields']['DocumentURL4']['column_num']; $fieldName8 = 'DocumentURL4';
            $website = "https://www.decorprice.com/";
            //We need to subtract 1 from column_num to offset the difference between column_num and the array key for $vars['rows']
            /*$fieldValue1 = */
            $fieldValue3 = $fieldValue4 = $fieldValue5 = $fieldValue6 = $fieldValue7 = $fieldValue8 = false; // init field index to false
            foreach ($vars['rows'] as &$row) {
                // check to use numeric or text indexes, but just once per call


                if (!$fieldValue3) {
                    $fieldValue3 = array_key_exists($fieldName3, $row)? $fieldName3: $fieldKey3-1;
                }

                /*if (!$fieldValue4) {
                    $fieldValue4 = array_key_exists($fieldName4, $row)? $fieldName4: $fieldKey4-1;
                }*/

                if (!$fieldValue5) {
                    $fieldValue5 = array_key_exists($fieldName5, $row)? $fieldName5: $fieldKey5-1;
                }

                if (!$fieldValue6) {
                    $fieldValue6 = array_key_exists($fieldName6, $row)? $fieldName6: $fieldKey6-1;
                }

                if (!$fieldValue7) {
                    $fieldValue7 = array_key_exists($fieldName7, $row)? $fieldName7: $fieldKey7-1;
                }

                if (!$fieldValue8) {
                    $fieldValue8 = array_key_exists($fieldName8, $row)? $fieldName8: $fieldKey8-1;
                }

                if(isset($row[$fieldValue3])){
                    $row[$fieldValue3] = $website.($row[$fieldValue3]).".html";

                }
                /*if(isset($row[$fieldValue4])){
                    $row[$fieldValue3] = $row[$fieldValue4];
                }*/
                if(isset($row[$fieldValue5])){
                    $row[$fieldValue5] = substr($website, 0, -1).$row[$fieldValue5];
                }
                if(isset($row[$fieldValue6])){
                    $row[$fieldValue6] = substr($website, 0, -1).$row[$fieldValue6];
                }
                if(isset($row[$fieldValue7])){
                    $row[$fieldValue7] = substr($website, 0, -1).$row[$fieldValue7];
                }
                if(isset($row[$fieldValue8])){
                    $row[$fieldValue8] = substr($website, 0, -1).$row[$fieldValue8];
                }
                unset($row['config_url']);
            }
            $observer->setData('vars', $vars);

        }

        // AMAZON ADS
        if (($profile_id=="25") || ($profile_id=="104")|| ($profile_id=="66")) {

            $fieldKey3 = isset($vars['fields']['Link']['column_num']) ? $vars['fields']['Link']['column_num'] : 0; $fieldName3 = 'Link';
            $fieldKey4 = isset($vars['fields']['config_url']['column_num']) ? $vars['fields']['config_url']['column_num'] : 0; $fieldName4 = 'config_url';
            $website = "https://www.decorprice.com/";
            //We need to subtract 1 from column_num to offset the difference between column_num and the array key for $vars['rows']
            /*$fieldValue1 = */
            $fieldValue3 = $fieldValue4 = false; // init field index to false
            foreach ($vars['rows'] as &$row) {
                // check to use numeric or text indexes, but just once per call


                if (!$fieldValue3) {
                    $fieldValue3 = array_key_exists($fieldName3, $row)? $fieldName3: $fieldKey3-1;
                }

                if (!$fieldValue4) {
                    $fieldValue4 = array_key_exists($fieldName4, $row)? $fieldName4: $fieldKey4-1;
                }

                if(isset($row[$fieldValue3])){
                    $row[$fieldValue3] = $website.($row[$fieldValue3]).".html";

                }
                if(isset($row[$fieldValue4])){

                    $row[$fieldValue3] = $row[$fieldValue4];

                }

                unset($row['config_url']);
            }
            $observer->setData('vars', $vars);

        }

        // BING
        if (($profile_id=="30") || ($profile_id=="118") || ($profile_id=="111") || ($profile_id=="128")) {

            $fieldKey2 = isset($vars['fields']['custom label 0']['column_num']) ? $vars['fields']['custom label 0']['column_num'] : 0; $fieldName2 = 'custom label 0';
            $fieldKey3 = isset($vars['fields']['ProductURL']['column_num']) ? $vars['fields']['ProductURL']['column_num'] : 0; $fieldName3 = 'ProductURL';
            $fieldKey4 = isset($vars['fields']['config_url']['column_num']) ? $vars['fields']['config_url']['column_num'] : 0; $fieldName4 = 'config_url';
            $fieldKey8 = isset($vars['fields']['custom label 1']['column_num']) ? $vars['fields']['custom label 1']['column_num'] : 0; $fieldName8 = 'custom label 1';
            $website = "https://www.decorprice.com/";
            if(($profile_id=="118")) {
                $website = "https://www.lightingselection.com/";
            }
            else if(($profile_id=="111")) {
                $website = "https://www.livexlightingselection.com/";
            }
            //We need to subtract 1 from column_num to offset the difference between column_num and the array key for $vars['rows']
            /*$fieldValue1 = */
            $fieldValue2 = $fieldValue3 = $fieldValue4 = $fieldValue8 = false; // init field index to false
            foreach ($vars['rows'] as &$row) {
                // check to use numeric or text indexes, but just once per call

                if (!$fieldValue2) {
                    $fieldValue2 = array_key_exists($fieldName2, $row)? $fieldName2: $fieldKey2-1;
                }

                if (!$fieldValue3) {
                    $fieldValue3 = array_key_exists($fieldName3, $row)? $fieldName3: $fieldKey3-1;
                }

                if (!$fieldValue4) {
                    $fieldValue4 = array_key_exists($fieldName4, $row)? $fieldName4: $fieldKey4-1;
                }

                if (!$fieldValue8) {
                    $fieldValue8 = array_key_exists($fieldName8, $row)? $fieldName8: $fieldKey8-1;
                }

                if(isset($row[$fieldValue3])){
                    $row[$fieldValue3] = $website.($row[$fieldValue3]).".html";

                }
                if(isset($row[$fieldValue4])){

                    $row[$fieldValue3] = $row[$fieldValue4];

                }

                if(isset($row[$fieldValue2])){
                    $qty=$row[$fieldValue2];
                    if($qty > 0) {
                        $row[$fieldValue2]="instock";
                    }
                    else {
                        $row[$fieldValue2]="outofstock";
                    }

                }

                //if(isset($row[$fieldValue8])){
                $best_seller=isset($row[$fieldValue8]) ? $row[$fieldValue8] : "";
                if($best_seller != "Yes") {
                    $row[$fieldValue8]="No";
                }
                //}

                $row['Bingads_redirect'] = isset($row[$fieldKey3]) ? ($row[$fieldKey3]) . "?utm_source=adcenterpla&utm_medium=paidtraffic" : "";

                unset($row['config_url']);
            }
            $observer->setData('vars', $vars);

        }

        // NEXTAG
        if ($profile_id=="32") {

            $fieldKey3 = $vars['fields']['Click-Out URL']['column_num']; $fieldName3 = 'Click-Out URL';
            $fieldKey4 = $vars['fields']['config_url']['column_num']; $fieldName4 = 'config_url';
            $website = "https://www.decorprice.com/";
            //We need to subtract 1 from column_num to offset the difference between column_num and the array key for $vars['rows']
            /*$fieldValue1 = */
            $fieldValue3 = $fieldValue4 = false; // init field index to false
            foreach ($vars['rows'] as &$row) {
                // check to use numeric or text indexes, but just once per call


                if (!$fieldValue3) {
                    $fieldValue3 = array_key_exists($fieldName3, $row)? $fieldName3: $fieldKey3-1;
                }

                if (!$fieldValue4) {
                    $fieldValue4 = array_key_exists($fieldName4, $row)? $fieldName4: $fieldKey4-1;
                }

                if(isset($row[$fieldValue3])){
                    $row[$fieldValue3] = $website.($row[$fieldValue3]).".html";

                }
                if(isset($row[$fieldValue4])){

                    $row[$fieldValue3] = $row[$fieldValue4];

                }

                unset($row['config_url']);
            }
            $observer->setData('vars', $vars);

        }

        // PRICEGRABBER
        if ($profile_id=="33") {

            $fieldKey3 = $vars['fields']['Referring Product URL']['column_num']; $fieldName3 = 'Referring Product URL';
            $fieldKey4 = $vars['fields']['config_url']['column_num']; $fieldName4 = 'config_url';
            $website = "https://www.decorprice.com/";
            //We need to subtract 1 from column_num to offset the difference between column_num and the array key for $vars['rows']
            /*$fieldValue1 = */
            $fieldValue3 = $fieldValue4 = false; // init field index to false
            foreach ($vars['rows'] as &$row) {
                // check to use numeric or text indexes, but just once per call


                if (!$fieldValue3) {
                    $fieldValue3 = array_key_exists($fieldName3, $row)? $fieldName3: $fieldKey3-1;
                }

                if (!$fieldValue4) {
                    $fieldValue4 = array_key_exists($fieldName4, $row)? $fieldName4: $fieldKey4-1;
                }

                if(isset($row[$fieldValue3])){
                    $row[$fieldValue3] = $website.($row[$fieldValue3]).".html";

                }
                if(isset($row[$fieldValue4])){

                    $row[$fieldValue3] = $row[$fieldValue4];

                }

                unset($row['config_url']);
            }
            $observer->setData('vars', $vars);

        }

        // SHOPZILLA
        if ($profile_id=="34") {

            $fieldKey3 = $vars['fields']['Product URL']['column_num']; $fieldName3 = 'Product URL';
            $fieldKey4 = $vars['fields']['config_url']['column_num']; $fieldName4 = 'config_url';
            $website = "https://www.decorprice.com/";
            //We need to subtract 1 from column_num to offset the difference between column_num and the array key for $vars['rows']
            /*$fieldValue1 = */
            $fieldValue3 = $fieldValue4 = false; // init field index to false
            foreach ($vars['rows'] as &$row) {
                // check to use numeric or text indexes, but just once per call


                if (!$fieldValue3) {
                    $fieldValue3 = array_key_exists($fieldName3, $row)? $fieldName3: $fieldKey3-1;
                }

                if (!$fieldValue4) {
                    $fieldValue4 = array_key_exists($fieldName4, $row)? $fieldName4: $fieldKey4-1;
                }

                if(isset($row[$fieldValue3])){
                    $row[$fieldValue3] = $website.($row[$fieldValue3]).".html";

                }
                if(isset($row[$fieldValue4])){

                    $row[$fieldValue3] = $row[$fieldValue4];

                }

                unset($row['config_url']);
            }
            $observer->setData('vars', $vars);

        }

        // SHOPPING.com
        if ($profile_id=="36") {

            $fieldKey3 = $vars['fields']['Product URL']['column_num']; $fieldName3 = 'Product URL';
            $fieldKey4 = $vars['fields']['config_url']['column_num']; $fieldName4 = 'config_url';
            $website = "https://www.decorprice.com/";
            //We need to subtract 1 from column_num to offset the difference between column_num and the array key for $vars['rows']
            /*$fieldValue1 = */
            $fieldValue3 = $fieldValue4 = false; // init field index to false
            foreach ($vars['rows'] as &$row) {
                // check to use numeric or text indexes, but just once per call


                if (!$fieldValue3) {
                    $fieldValue3 = array_key_exists($fieldName3, $row)? $fieldName3: $fieldKey3-1;
                }

                if (!$fieldValue4) {
                    $fieldValue4 = array_key_exists($fieldName4, $row)? $fieldName4: $fieldKey4-1;
                }

                if(isset($row[$fieldValue3])){
                    $row[$fieldValue3] = $website.($row[$fieldValue3]).".html";

                }
                if(isset($row[$fieldValue4])){

                    $row[$fieldValue3] = $row[$fieldValue4];

                }

                unset($row['config_url']);
            }
            $observer->setData('vars', $vars);

        }

        // BECOME
        if ($profile_id=="37") {

            $fieldKey3 = $vars['fields']['Product URL']['column_num']; $fieldName3 = 'Product URL';
            $fieldKey4 = $vars['fields']['config_url']['column_num']; $fieldName4 = 'config_url';
            $website = "https://www.decorprice.com/";
            //We need to subtract 1 from column_num to offset the difference between column_num and the array key for $vars['rows']
            /*$fieldValue1 = */
            $fieldValue3 = $fieldValue4 = false; // init field index to false
            foreach ($vars['rows'] as &$row) {
                // check to use numeric or text indexes, but just once per call


                if (!$fieldValue3) {
                    $fieldValue3 = array_key_exists($fieldName3, $row)? $fieldName3: $fieldKey3-1;
                }

                if (!$fieldValue4) {
                    $fieldValue4 = array_key_exists($fieldName4, $row)? $fieldName4: $fieldKey4-1;
                }

                if(isset($row[$fieldValue3])){
                    $row[$fieldValue3] = $website.($row[$fieldValue3]).".html";

                }
                if(isset($row[$fieldValue4])){

                    $row[$fieldValue3] = $row[$fieldValue4];

                }

                unset($row['config_url']);
            }
            $observer->setData('vars', $vars);

        }

        // FIND
        if ($profile_id=="38") {
            $fieldKey1 = $vars['fields']['title']['column_num']; $fieldName1 = 'title';
            //$fieldKey2 = $vars['fields']['custom label 0']['column_num']; $fieldName2 = 'custom label 0';
            $fieldKey3 = $vars['fields']['link']['column_num']; $fieldName3 = 'link';
            $fieldKey4 = $vars['fields']['config_url']['column_num']; $fieldName4 = 'config_url';
            $website = "https://www.decorprice.com/";
            //We need to subtract 1 from column_num to offset the difference between column_num and the array key for $vars['rows']
            /*$fieldValue1 = */
            $fieldValue1 = $fieldValue2 = $fieldValue3 = $fieldValue4 = false; // init field index to false
            foreach ($vars['rows'] as &$row) {
                // check to use numeric or text indexes, but just once per call

                if( !$fieldValue1 ) {
                    $fieldValue1 = array_key_exists($fieldName1, $row)? $fieldName1: $fieldKey1-1;
                }

                if (!$fieldValue3) {
                    $fieldValue3 = array_key_exists($fieldName3, $row)? $fieldName3: $fieldKey3-1;
                }

                if (!$fieldValue4) {
                    $fieldValue4 = array_key_exists($fieldName4, $row)? $fieldName4: $fieldKey4-1;
                }


                if(isset($row[$fieldValue1])){
                    $row[$fieldValue1] = substr($row[$fieldValue1], 0, 150);

                }

                if(isset($row[$fieldValue3])){
                    $row[$fieldValue3] = $website.($row[$fieldValue3]).".html";

                }
                if(isset($row[$fieldValue4])){

                    $row[$fieldValue3] = $row[$fieldValue4];

                }

                unset($row['config_url']);
            }
            $observer->setData('vars', $vars);

        }

        // PARAG PROFILES
        if (($profile_id=="16") || ($profile_id=="20") || ($profile_id=="19"))  {

            $fieldKey3 = $vars['fields']['url_key']['column_num']; $fieldName3 = 'url_key';
            $fieldKey4 = isset($vars['fields']['config_url']['column_num']) ? $vars['fields']['config_url']['column_num'] : 0; $fieldName4 = 'config_url';
            $website = "https://www.decorprice.com/";
            if($profile_id=="19")
            {
                $website = "https://www.lightingselection.com/";
            }
            //We need to subtract 1 from column_num to offset the difference between column_num and the array key for $vars['rows']
            /*$fieldValue1 = */
            $fieldValue3 = $fieldValue4 = false; // init field index to false
            foreach ($vars['rows'] as &$row) {
                // check to use numeric or text indexes, but just once per call


                if (!$fieldValue3) {
                    $fieldValue3 = array_key_exists($fieldName3, $row)? $fieldName3: $fieldKey3-1;
                }

                if (!$fieldValue4) {
                    $fieldValue4 = array_key_exists($fieldName4, $row)? $fieldName4: $fieldKey4-1;
                }

                if(isset($row[$fieldValue3])){
                    $row[$fieldValue3] = $website.($row[$fieldValue3]).".html";

                }
                if(isset($row[$fieldValue4])){

                    $row[$fieldValue3] = $row[$fieldValue4];

                }

                unset($row['config_url']);
            }
            $observer->setData('vars', $vars);

        }

        // LIGHTING SELECTION ADWORDS PARESH PROFILE
        if ($profile_id=="57") {
            $fieldKey3 = $vars['fields']['url_key']['column_num'];
            $fieldName3 = 'url_key';
            $website = "https://www.lightingselection.com/";
            $fieldValue3 = false;
            foreach ($vars['rows'] as &$row) {
                if (!$fieldValue3) {
                    $fieldValue3 = array_key_exists($fieldName3, $row)? $fieldName3: $fieldKey3-1;
                }
                if(isset($row[$fieldValue3])){
                    $row[$fieldValue3] = $website.($row[$fieldValue3]).".html";

                }
            }
            $observer->setData('vars', $vars);
        }

    }//execute

}
?>
