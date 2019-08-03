<?php

class SmsWeb_SMSNotifications_Model_Observer
{
// This method is called whenever an order is saved. It checks if the order's
// status has been updated and if yes, checks if the new order status should
// trigger sending a notification

private function _helper()
{
	return Mage::helper('smsnotifications');
}
	
public function salesOrderSaveAfter($observer)
{

// Get the settings
$settings = Mage::helper('smsnotifications/data')->getSettings();

// Get the new order object
$order = $observer->getEvent()->getOrder();

// Get the old order data
$oldOrder = $order->getOrigData();


//$oreder_notification_status = [];  // 'processing','complete',
$oreder_notification_status = array('pending','holded','closed','canceled');

if(!in_array($order->getStatus(), $oreder_notification_status)) {
return;
}

// If the order status hasn't changed, don't do anything
if($oldOrder['status'] === $order->getStatus()) {
return;
}


if(Mage::app()->getStore()->isAdmin() && $order->getStatus() == "pending" ) {

$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');
$table = $resource->getTableName('sales/order_status_history'); 

$query = 'SELECT count(status) as status FROM ' . $table . ' WHERE status = "pending" and parent_id = '
. (int)$order->getId() . ' LIMIT 1';
$pendingStatus = $readConnection->fetchOne($query);
} else {
$pendingStatus = 1;
}

// Generate the body for the notification
$store_name = Mage::app()->getStore()->getFrontendName();
$customer_name  = $order->getCustomerFirstname();
$customer_name .= ' ' . $order->getCustomerLastname();
$order_amount   = $order->getBaseCurrencyCode();
$order_amount  .= ' ' . $order->getBaseGrandTotal();
$order_id       = $order->getIncrementId();

$shippingAdress = $order->getShippingAddress();
$telephoneNumber = trim($shippingAdress->getTelephone());

if($order->getStatus() == "pending" && $pendingStatus <= 1) {
	if(!Mage::getStoreConfig('smsnotifications/order_notification/order_status_enable'))
		return;	
	$text = Mage::getStoreConfig('smsnotifications/order_notification/order_status');
}  
elseif($order->getStatus() == "pending") {
	if(!Mage::getStoreConfig('smsnotifications/order_notification/order_unhold_enable'))
		return;	
	$text = Mage::getStoreConfig('smsnotifications/order_notification/order_unhold_status');
} 
elseif($order->getStatus() == "holded") {
	if(!Mage::getStoreConfig('smsnotifications/order_notification/order_hold_enable'))
		return;	
	$text = Mage::getStoreConfig('smsnotifications/order_notification/order_hold_status');
} 
elseif($order->getStatus() == "canceled") {
	if(!Mage::getStoreConfig('smsnotifications/order_notification/order_canceled_enable'))
		return;	
	$text = Mage::getStoreConfig('smsnotifications/order_notification/order_canceled_status');
} 
elseif($order->getStatus() == "closed") {
	if(!Mage::getStoreConfig('smsnotifications/order_notification/order_closed_enable'))
		return;	
	$text = Mage::getStoreConfig('smsnotifications/order_notification/order_closed_status');
} 
elseif($order->getStatus() == "complete") {
	if(!Mage::getStoreConfig('smsnotifications/shipment_notification/order_shipment_enable'))
		return;	
	$text = Mage::getStoreConfig('smsnotifications/shipment_notification/message');
}

$text = str_replace('{{name}}',$customer_name, $text);
$text = str_replace('{{amount}}', $order_amount, $text);
$text = str_replace('{{order}}', $order_id, $text);

// If no recipients have been set, we can't do anything
if(!count($settings['order_noficication_recipients'])) {
return;
}

// Send the order notification by SMS

array_push($settings['order_noficication_recipients'],$telephoneNumber);

//file_put_contents('sms.txt',$text.PHP_EOL,FILE_APPEND);
$result = Mage::helper('smsnotifications/data')->sendSms($text,$settings['order_noficication_recipients']);

// Display a success or error message
if($result) {
$sendNumber1 = implode(',', $settings['order_noficication_recipients']);
Mage::getSingleton('adminhtml/session')->addSuccess(sprintf('The order notification has been sent via SMS to: %s', $sendNumber1));
} else {
Mage::getSingleton('adminhtml/session')->addError('There has been an error sending the order notification SMS.');
}

}


    /**
     * Handler of sending of order status SMS
     *
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderStatusHistorySaveBefore(Varien_Event_Observer $observer)
    {
        if (!$this->_helper()->isEnabled()) {
            return $this;
        }

        /** @var  $req Mage_Core_Controller_Request_Http */
        $req = Mage::app()->getRequest();
        if ($req->getControllerName() == 'sales_order' && $req->getActionName() == 'addComment') {
            $history = $observer->getStatusHistory();
            if ($history instanceof Mage_Sales_Model_Order_Status_History && $history->getId() == null) {
                $data = $req->getPost('history');
                $notify = isset($data['is_customer_sms_notified']) ? $data['is_customer_sms_notified'] : false;
                $comment = trim(strip_tags($history->getComment()));
                if ($notify) {
                    $bill = $history->getOrder()->getShippingAddress();
                    $data = Mage::getModel('smsnotifications/sms_data_order_status', array(
                            'order' => $history->getOrder(),
                            'comment' => $comment,
                        )
                    );
					
					$telephoneNumber = trim($bill->getTelephone());
					$result = Mage::helper('smsnotifications/data')->sendSms($comment, $telephoneNumber);
					// Display a success or error message
					if($result) {
						$history->setIsCustomerSmsNotified($notify);
					}
                }
            }
        }
    }
// This method is called whenever a new shipment is created for an order
public function salesOrderInvoiceSaveAfter($observer)
{
if(!Mage::getStoreConfig('smsnotifications/invoice_notification/order_invoice_enable'))
		return;	

// Get the settings
$settings = Mage::helper('smsnotifications/data')->getSettings();

$text = Mage::getStoreConfig('smsnotifications/invoice_notification/message');
// If no invoice notification has been specified, no notification can be sent
if(!$text) {
return;
}


$order = $observer->getEvent()->getInvoice()->getOrder();
$order_id  = $order->getIncrementId();

$invoice = $order->getInvoiceCollection()->getFirstItem();
$invoiceId = $invoice->getIncrementId();


$store_name = Mage::app()->getStore()->getFrontendName();
$customer_name = $order->getCustomerFirstname();
$customer_name .= ' ' . $order->getCustomerLastname();

$order_amount   = $order->getBaseCurrencyCode();
$order_amount  .= ' ' . $order->getBaseGrandTotal();

$shippingAdress = $order->getShippingAddress();
$telephoneNumber = trim($shippingAdress->getTelephone());


//file_put_contents('invoice.txt',$order_id);

// Check if a telephone number has been specified
if($telephoneNumber) {
$text = Mage::getStoreConfig('smsnotifications/invoice_notification/message');

// Send the shipment notification to the specified telephone number
$text = $settings['invoice_notification_message'];

$text = str_replace('{{name}}',$customer_name, $text);
$text = str_replace('{{order}}', $order_id, $text);
$text = str_replace('{{amount}}', $order_amount, $text);
$text = str_replace('{{invoiceno}}', $invoiceId, $text);

array_push($settings['order_noficication_recipients'],$telephoneNumber);
//file_put_contents('invoice.txt',$text.PHP_EOL,FILE_APPEND);
$result = Mage::helper('smsnotifications/data')->sendSms($text, $settings['order_noficication_recipients']);

// Display a success or error message
if($result) {
$recipients_string = implode(',', $settings['order_noficication_recipients']);
Mage::getSingleton('adminhtml/session')->addSuccess(sprintf('The invoice notification has been sent via SMS to: %s', $recipients_string));
} else {
Mage::getSingleton('adminhtml/session')->addError('There has been an error sending the invoice notification SMS.');
}
}
}

// This method is called whenever a new shipment is created for an order
public function salesOrderShipmentSaveAfter($observer)
{

if(!Mage::getStoreConfig('smsnotifications/shipment_notification/order_shipment_enable'))
		return;	

try
{



// Get the settings
$settings = Mage::helper('smsnotifications/data')->getSettings();

// If no shipment notification has been specified, no notification can be sent
if(!$settings['shipment_notification_message']) {
return;
}

// Get the new order object
$order = $observer->getEvent()->getShipment()->getOrder();

// Get the telephone # associated with the shipping (or billing) address


$customer_name = $order->getCustomerFirstname();
$customer_name .= ' ' . $order->getCustomerLastname();
$order_id       = $order->getIncrementId();
$order_amount   = $order->getBaseCurrencyCode();
$order_track_number   = $order->getTrackingNumbers();

$order_amount  .= ' ' . $order->getBaseGrandTotal();


$shippingAdress = $order->getShippingAddress();
$telephoneNumber = trim($shippingAdress->getTelephone());


$shipment = $order->getShipmentsCollection()->getFirstItem();
$shipId = $shipment->getIncrementId();


$trackings=Mage::getResourceModel('sales/order_shipment_track_collection')->addAttributeToSelect('*')->addAttributeToFilter('parent_id',$shipment->getId());
$trackings->getSelect()->order('entity_id desc')->limit(1);

$trackData = $trackings->getData();
$trackID = $trackData[0]['entity_id'];
$track_number = $trackData[0]['track_number'];
$title = $trackData[0]['title'];
$carrier_code = $trackData[0]['carrier_code'];
//Mage::log($trackID);










// Check if a telephone number has been specified
if($telephoneNumber) {
// Send the shipment notification to the specified telephone number
$text = $settings['shipment_notification_message'];
$text = str_replace('{{name}}',$customer_name, $text);
$text = str_replace('{{order}}', $order_id, $text);
$text = str_replace('{{amount}}', $order_amount, $text);
$text = str_replace('{{shipmentno}}', $shipId, $text);
$text = str_replace('{{trackID}}', $trackID, $text);
$text = str_replace('{{track_number}}', $track_number, $text);
$text = str_replace('{{title}}', $title, $text);
$text = str_replace('{{carrier_code}}', $carrier_code, $text);

array_push($settings['order_noficication_recipients'],$telephoneNumber);

//file_put_contents('ship.txt',$text);
$result = Mage::helper('smsnotifications/data')->sendSms($text, $settings['order_noficication_recipients']);

// Display a success or error message
if($result) {
$recipients_string = implode(',', $settings['order_noficication_recipients']);
Mage::getSingleton('adminhtml/session')->addSuccess(sprintf('The shipment notification has been sent via SMS to: %s', $recipients_string));
} else {
Mage::getSingleton('adminhtml/session')->addError('There has been an error sending the shipment notification SMS.');
}
}


}
catch(Exception $e)
{

}




}

// This method is called whenever the application's setting in the
// adminhtml are changed
public function configSaveAfter($observer)
{
// Get the settings
$settings = Mage::helper('smsnotifications/data')->getSettings();

// If no recipients have been set, we can't do anything
if(!count($settings['order_noficication_recipients'])) {
return;
}

// Verify the settings by sending a test message
$result = Mage::helper('smsnotifications/data')->verifyApi();

// Display a success or error message
if($result) {
// If everything has worked, let the user know that a test message
// has been sent to the recipients
$recipients_string = implode(',', $settings['order_noficication_recipients']);
Mage::getSingleton('adminhtml/session')->addNotice(sprintf('SMS Alert credentials Verified Successfully'));
} else {
Mage::getSingleton('adminhtml/session')->addError('Unable to verify your SMS Alert credentials. Please verify that all your settings are correct and try again.');
}
}


    /**
     * Handler of sending of confirm of SMS login
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function controllerActionPredispatchCustomerAccountLoginPost(Varien_Event_Observer $observer)
    {
        $settings = Mage::helper('smsnotifications/data')->getSettings();
       
		if (!$settings['is_login_otp']) {
            return $this;
        }

        /** @var  $ctrl Mage_Customer_AccountController */
        $ctrl = $observer->getControllerAction();

        if (!$ctrl->getRequest()->isPost()) {
            return $this;
        }

        $login = $ctrl->getRequest()->getPost('login');
        if (empty($login['username']) || empty($login['password'])) {
            return $this;
        }

        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
		
		try {
            if ($customer->authenticate($login['username'], $login['password'])) {
                Mage::getSingleton('smsnotifications/session')->setAuthUser($login['username']);
                Mage::getSingleton('smsnotifications/session')->setAuthPassword($login['password']);
                $bill = $customer->getDefaultBillingAddress();
                if ($bill && $bill->getTelephone()) {
					
					$result = Mage::helper('smsnotifications/data')->sendOTP($settings['login_verifiction_otp'],$bill->getTelephone());
					
					if (!$result) {
                        Mage::getSingleton('customer/session')->addError('There was an error sending OTP.');
                        $ctrl->getResponse()->setRedirect(Mage::getUrl('*/*/login'))->sendResponse();
                        //exit();
						$this->getResponse()->setBody();
                    }
                    $ctrl->getResponse()->setRedirect(Mage::getUrl('smsalert/customer_account/loginConfirm'))->sendResponse();
                    //exit();
					$this->getResponse()->setBody();
                }
            }
        } catch (Exception $e) {}

        return $this;
    }
	
	 /**
     * Handler of sending of SMS reset password
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function controllerActionPredispatchCustomerAccountResetPassword(Varien_Event_Observer $observer)
    { 
	
		$settings = Mage::helper('smsnotifications/data')->getSettings();
       
		if (!$settings['is_reset_pwd_otp']) {
            return $this;
        }

        /** @var  $ctrl Mage_Customer_AccountController */
        $ctrl = $observer->getControllerAction();

        if ($ctrl->getRequest()->isPost()) {
            return $this;
        }

        $customerId = intval($ctrl->getRequest()->get('id'));
        $token      = strval($ctrl->getRequest()->get('token'));

        if (empty($customerId) || $customerId < 0 || empty($token)) {
//            throw Mage::exception('Mage_Core', $this->_getHelper('customer')->__('Invalid password reset token.'));
            return $this;
        }

        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')->load($customerId);
        if (!$customer->getId()) {
//            throw Mage::exception('Mage_Core', $this->_getHelper('customer')->__('Wrong customer account specified.'));
            return $this;
        }

        $customerToken = $customer->getRpToken();
        if (strcmp($customerToken, $token) != 0 || $customer->isResetPasswordLinkTokenExpired()) {
//            throw Mage::exception('Mage_Core', $this->_getHelper('customer')->__('Your password reset link has expired.'));
            return $this;
        }

        if (Mage::getSingleton('smsnotifications/session')->isValidRpToken($customerToken)) {
            return $this;
        }

        try {
            $bill = $customer->getDefaultBillingAddress();
            if ($bill && $bill->getTelephone()) {

               
				$result = Mage::helper('smsnotifications/data')->sendOTP($settings['password_reset_otp'],$bill->getTelephone());
				if (!$result) {
                    Mage::getSingleton('customer/session')->addError('There was an error sending OTP.');
                    $ctrl->getResponse()->setRedirect(Mage::getUrl('*/*/login'))->sendResponse();
                    //exit();
					$this->getResponse()->setBody();
                }

                $ctrl->getResponse()->setRedirect(Mage::getUrl('smsnotifications/customer_account/resetPasswordConfirm',array(
                    '_current' => true
                )))->sendResponse();
                //exit();
				$this->getResponse()->setBody();
            }
        } catch (Exception $e) {}

        return $this;
    }

}