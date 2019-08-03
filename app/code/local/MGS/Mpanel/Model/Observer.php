<?php 
class MGS_Mpanel_Model_Observer extends Mage_Core_Model_Abstract
{
    public function addHandles($observer) {
	
		if(Mage::getStoreConfig('mpanel/general/enabled')){
			$action = $observer->getEvent()->getAction();
			$actionName = $action->getFullActionName();
			
			$actionToAddHandle = Mage::helper('mpanel')->getMyAccountActionName();
			$configLayout = Mage::getStoreConfig('mpanel/my_account/layout');
			
			if($configLayout!='' && in_array($actionName, $actionToAddHandle)){
				$layout = $observer->getEvent()->getLayout();
				
				$hander = '';
				switch ($configLayout) {
					case 'empty':
						$hander = 'mpanel_empty_column';
						break;
					case 'one_column':
						$hander = 'mpanel_one_column';
						break;
					case 'two_columns_left':
						$hander = 'mpanel_twocolumn_left';
						break;
					case 'two_columns_right':
						$hander = 'mpanel_twocolumn_right';
						break;
					case 'three_columns':
						$hander = 'mpanel_three_column';
						break;
				}
				
				$layout->getUpdate()->addHandle($hander);
			}
		}
    }
}