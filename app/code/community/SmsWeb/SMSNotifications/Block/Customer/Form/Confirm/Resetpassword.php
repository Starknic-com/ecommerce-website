<?php
class SmsWeb_SMSNotifications_Block_Customer_Form_Confirm_Resetpassword extends Mage_Core_Block_Template
{
    private $_sms = -1;

    /**
     * Retrieve SMS for form field
     *
     * @return string
     */
    public function getSmsRPCode()
    {
        if (-1 === $this->_sms) {
            $this->_sms = Mage::getSingleton('customer/session')->getSmsRPCode(true);
        }

        return $this->_sms;
    }

    public function getPostActionUrl()
    {
        return $this->helper('smsnotifications')->getResetPasswordConfirmPostUrl();
    }
}