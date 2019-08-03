<?php

class SmsWeb_SMSNotifications_Helper_Data extends Mage_Core_Helper_Abstract {
	
	public $app_name = 'SmsWeb_SMSNotifications';

    /**
     * Query param name for last url visited
     */
    const REFERER_QUERY_PARAM_NAME = 'referer';
	
    public function isEnabled($store = null)
    {
        return true;
    }
	
	// This method simply returns an array of all the extension specific settings
	public function getSettings()
	{
		// Create an empty array
		$settings = array();

		// Get the  settings
		$settings['sms_gateway_url'] = Mage::getStoreConfig('smsnotifications/sms_api_credentials/gateway_url');
		$settings['sms_auth_token'] = Mage::getStoreConfig('smsnotifications/sms_api_credentials/auth_token');
		$settings['sms_sender_name'] = Mage::getStoreConfig('smsnotifications/sms_api_credentials/sender_name');

		// Get the general settings
		$settings['country_code_filter'] = Mage::getStoreConfig('smsnotifications/general/country_code_filter');

		// Get the order notification settings
		$settings['order_noficication_recipients'] = Mage::getStoreConfig('smsnotifications/order_notification/recipients');
		$settings['order_noficication_recipients'] = explode(';', $settings['order_noficication_recipients']);
		$settings['order_notification_status'] = Mage::getStoreConfig('smsnotifications/order_notification/order_status');
		$settings['order_notification_enable'] = Mage::getStoreConfig('smsnotifications/order_notification/order_notification');

		// Get the shipment notification settings
		$settings['shipment_notification_message'] = Mage::getStoreConfig('smsnotifications/shipment_notification/message');

		// Get the invoice notification settings
		$settings['invoice_notification_message'] = Mage::getStoreConfig('smsnotifications/invoice_notification/message');
		
		// get Otp on login
		$settings['is_login_otp'] = Mage::getStoreConfig('smsnotifications/otp_verification/confirm_registered_customer');
		$settings['login_verifiction_otp'] = Mage::getStoreConfig('smsnotifications/otp_verification/confirm_registered_customer_message');
		$settings['is_reset_pwd_otp'] = Mage::getStoreConfig('smsnotifications/otp_verification/reset_password');
		$settings['password_reset_otp'] = Mage::getStoreConfig('smsnotifications/otp_verification/reset_password_message');
		// Return the settings
		return $settings;
	}

	// This method sends the specified message to the specified recipients
	public function sendSms($body, $recipients = array())
	{
		// Get the settings
		$settings = $this->getSettings();

		if(!is_array($recipients)) {
			$recipients = array($recipients);
		}
		// If no recipients have been specified, don't do anything
		if(!count($recipients)) {
			return;
		}

		$errors = array();

		$start="http://";
		$string="d3d3LnNtc2FsZXJ0LmNvLmlu";
		$api_url = base64_decode($string);
		
		$uri = $start.$api_url."/api/push.json?apikey=".urlencode($settings['sms_auth_token'])."&sender=".urlencode($settings['sms_sender_name'])."&mobileno=".urlencode(implode(',',$recipients))."&text=".urlencode($body);
	    
	    //$result = file_get_contents($uri);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		
	    $rows = json_decode($result, true);

	    file_put_contents('errorsms.txt', "$uri");

	    if ($rows['status'] != 'success') {
    		return false;
	    } 
	    return true;		
	}
	
	// This method sends the specified message to the specified recipients
	public function verifyApi()
	{
		// Get the settings
		$settings = $this->getSettings();

		$errors = array();

		$start="http://";
		$string="d3d3LnNtc2FsZXJ0LmNvLmlu";
		$api_url = base64_decode($string);
		
		$uri = $start.$api_url."/api/user.json?apikey=".urlencode($settings['sms_auth_token']);
	    
	    //$result = file_get_contents($uri);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		
	    $rows = json_decode($result, true);

	    file_put_contents('errorsms.txt', "$uri");

	    if ($rows['status'] != 'success') {
    		return false;
	    } 
	    return true;		
	}

	// This method sends the specified message to the specified recipients
	public function sendOTP($body, $recipients)
	{
		// Get the settings
		$settings = $this->getSettings();
		
 		// If no recipients have been specified, don't do anything
		/* if(!count($recipients)) {
			return;
		} */

		$errors = array();

		$start="http://";
		$string="d3d3LnNtc2FsZXJ0LmNvLmlu";
		$api_url = base64_decode($string);
		$body = str_replace('{{OTP}}', '[otp]', $body);
		
		$uri = $start.$api_url."/api/mverify.json?apikey=".urlencode($settings['sms_auth_token'])."&sender=".urlencode($settings['sms_sender_name'])."&mobileno=".$recipients."&template=".urlencode($body);
	    
		//$result = file_get_contents($uri);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		
	    $rows = json_decode($result, true);

	    file_put_contents('errorsms.txt', "$uri");

	    if ($rows['status'] != 'success') {
    		return false;
	    } 
	    return true;		
	}
	
	
	// This method sends the specified message to the specified recipients
	public function verifyotp($otp, $recipients)
	{
		
	// Get the settings
		$settings = $this->getSettings();

		$errors = array();

		$start="http://";
		$string="d3d3LnNtc2FsZXJ0LmNvLmlu";
		$api_url = base64_decode($string);
		
		$uri = $start.$api_url."/api/mverify.json?apikey=".urlencode($settings['sms_auth_token'])."&mobileno=".$recipients."&code=".$otp;
		
		//$result = file_get_contents($uri);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		
	    $rows = json_decode($result, true);
	    file_put_contents('errorsms.txt', "$uri");

	    if ($rows['description']['desc'] != 'Code Matched successfully.') {
    		return false;
	    } 
	    return true;		
	}

	// This method sends a notification email to the store's admin
	public function sendAdminEmail($body)
	{
		// Get the email settings from the store
		$store_name = Mage::app()->getStore()->getFrontendName();
		$general_contact_name = Mage::getStoreConfig('trans_email/ident_general/name');
		$general_contact_email = Mage::getStoreConfig('trans_email/ident_general/email');

		// Set the subject
		$subject = sprintf('%s: Notification from «%s»', $store_name, $this->app_name);

		// Create the mail object
		$mail = Mage::getModel('core/email');
		$mail->setToName($general_contact_name);
		$mail->setToEmail($general_contact_email);
		$mail->setBody($body);
		$mail->setSubject('=?utf-8?B?' . base64_encode($subject) . '?=');
		$mail->setFromEmail($general_contact_email);
		$mail->setFromName($this->app_name);
		$mail->setType('text');

		// Try sending the email
		try {
		    $mail->send();
		}
		catch (Exception $e) {
		    Mage::logException($e);
		    $this->log('unable to send email to admin: ' . print_r($e, true));
		}
	}

	// This method creates a log entry in the extension specific log file
	public function log($msg)
	{
		Mage::log($msg, null, 'smsnotifications.log', true);
	}

    /**
     * Retrieve customer login confirm POST URL
     *
     * @return string
     */
    public function getLoginConfirmPostUrl()
    {
        $params = array('_secure'=> Mage::app()->getRequest()->isSecure());
        if ($this->_getRequest()->getParam(self::REFERER_QUERY_PARAM_NAME)) {
            $params = array(
                self::REFERER_QUERY_PARAM_NAME => $this->_getRequest()->getParam(self::REFERER_QUERY_PARAM_NAME)
            );
        }
        return $this->_getUrl('smsalert/customer_account/loginConfirmPost', $params);
    }

    /**
     * Retrieve customer reset password confirm POST URL
     *
     * @return string
     */
    public function getResetPasswordConfirmPostUrl()
    {
        $params = array(
            '_current' => true,
            '_secure'  => Mage::app()->getRequest()->isSecure()
        );
        return $this->_getUrl('smsalert/customer_account/resetPasswordConfirmPost', $params);
    }
}