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
        
        $profile = $observer->getData('profile');
        
        $vars = $observer->getEvent()->getVars();

	$profile = $vars['profile'];
        $profile_id = $profile->getData('profile_id');

        if ($profile_id=="57") {
            $fieldKey3 = $vars['fields']['url_key']['column_num'];
            $fieldName3 = 'url_key';
            $website = "https://www.mydomain.com/";
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
    }

}
?>
