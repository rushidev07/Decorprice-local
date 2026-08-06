<?php
namespace Onestepcheckout\Userinterface\Block;

class Display extends \Magento\Framework\View\Element\Template
{
	protected $_storeManager;
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		array $data = []
	)
	{
		$this->_storeManager = $storeManager;
        parent::__construct($context,$data);
	}

	public function showLogo()
	{
		$storeID = $this->_storeManager->getStore()->getId();
		$mediaPath = 'Onestepcheckout_Userinterface::images/secure-checkout-'.$storeID.'.png';
		$imageUrl = $this->getViewFileUrl($mediaPath);
		$outputHtml = '';
		$outputHtml  .= '<div class="header">';
		$outputHtml  	.= '<img class="image-styles"src="'.$imageUrl.'" title="Decorprice" alt="Decorprice">';
		$outputHtml  .= '</div>';
		$outputHtml .= '<hr>';
		
		return $outputHtml;
	}
}