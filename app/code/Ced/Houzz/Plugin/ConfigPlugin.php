<?php
namespace Ced\Houzz\Plugin;

class ConfigPlugin
{
    public function afterSave(
        \Magento\Config\Model\Config $subject
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfigManager = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $configPost = $subject->getData();
        if(isset($configPost['section']) && $configPost['section'] == 'houzzconfiguration'
            && isset($configPost['groups']['houzzsetting']['fields']['api_appname']['value'])
            && isset($configPost['groups']['houzzsetting']['fields']['api_username']['value'])
            && isset($configPost['groups']['houzzsetting']['fields']['api_token']['value']) ) {
            $appName = $configPost['groups']['houzzsetting']['fields']['api_appname']['value'];
            $userName = $configPost['groups']['houzzsetting']['fields']['api_username']['value'];
            $appToken = $configPost['groups']['houzzsetting']['fields']['api_token']['value'];
            $this->jsonHelper = $objectManager->create('\Magento\Framework\Json\Helper\Data');
            $configResourceModel = $objectManager->create('\Magento\Config\Model\ResourceModel\Config');
            $responseStatus = false;
            $params= [
                'api_token'=> $appToken,
                'api_appname' =>  $appName,
                'api_username' => $userName,
            ];
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $datahelper = $objectManager->get( '\Ced\Houzz\Helper\Data' );
            $response = $datahelper->validateAPI($params);
            if($response)
                $response = $this->jsonHelper->jsonDecode($response);
            if(isset($response['Ack']) && $response['Ack'] == "Success") {
                $responseStatus = true;
            }
            $messageManager = $objectManager->create('\Magento\Framework\Message\ManagerInterface');
            $enabled = $scopeConfigManager->getValue('houzzconfiguration/houzzsetting/enable');
            if ( $enabled && $responseStatus ) {
                $messageManager->addSuccess('Houzz Credential Valid');
                $configResourceModel->saveConfig('houzzconfiguration/houzzsetting/validate_details','1','default',0);
            } else {
                $messageManager->addError('Houzz Credential Invalid');
                $configResourceModel->saveConfig('houzzconfiguration/houzzsetting/validate_details','0','default',0);
            }
        }
    }
}