<?php

class Ei_Creditpoint_CreditpointController extends Mage_Core_Controller_Front_Action {

    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    public function preDispatch() {

        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        $loginUrl = Mage::helper('customer')->getLoginUrl();
        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    public function indexAction() {

        $this->loadLayout();
        $this->renderLayout();
    }

    public function estimateCreditPointAction() {

        
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        //$userSelection = $this->getRequest()->getParam('isChecked');
        $code = (string) $this->getRequest()->getParam('estimate_credit');
        $pointsRedeem = Mage::app()->getRequest()->getParam('pointsredeem');
        $pointsAmount = Mage::helper('creditpoint')->getPrice($pointsRedeem);
        $subTotal = $quote->getData('subtotal');
        
        //if estimate_credit code set and redeemed points greater than 0
        if ( (!empty($pointsRedeem)) && (!empty($code)) && ($pointsRedeem < $subTotal) ) {
            
            
            Mage::getSingleton('core/session')->setEstimateCredit($code);
            Mage::getSingleton('core/session')->setPointsRedeem($pointsRedeem);
            Mage::getSingleton('core/session')->setPointsPrice($pointsAmount);
           
            
        }else if( (!empty($pointsRedeem)) && ($pointsRedeem >= $subTotal) ){
            
            $pointsRedeem = $subTotal;
            Mage::getSingleton('core/session')->setEstimateCredit($code);
            Mage::getSingleton('core/session')->setPointsRedeem($pointsRedeem);
            Mage::getSingleton('core/session')->setPointsPrice($pointsAmount);
            
        }else{
            
            Mage::getSingleton('core/session')->unsEstimateCredit();
            Mage::getSingleton('core/session')->unsPointsRedeem();
            Mage::getSingleton('core/session')->unsPointsPrice();
            
        }
        
        
        //call collectTotals on quote with the totals collected flag set to false.
        //$quote->collectTotals() one the first line checks for the totals_collected_flag,
        //if it is set to true it returns without doing anything but we want to update it after applying discount so its set to false.
        //Look at the definition : Mage_Sales_Model_Quote::collectTotals() 
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        
        
        $response = array();
        
        //here 'creditpointblock' will be key and cart block html as value for that key
        $response['creditpointblock'] = $this->creditpointAjax();
        $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
        return $this->getResponse()->setBody(json_encode($response));
    }
    
    
    
    protected function creditpointAjax()
    {

        $layout = $this->getLayout();
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true); 
        $totalsBlock = $layout->createBlock('checkout/cart_totals')->setTemplate('checkout/cart/totals.phtml');
        return $totalsBlock->toHtml();
    }

}