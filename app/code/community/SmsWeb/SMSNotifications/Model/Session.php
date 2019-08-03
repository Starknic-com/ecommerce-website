<?php
/**
 * SMS session model
 *
 * @method Qbikz_InformaxionSms_Model_Session setAuthUser(string $val)
 * @method string getAuthUser()
 * @method Qbikz_InformaxionSms_Model_Session setAuthPassword(string $val)
 * @method string getAuthPassword()
 * @method Qbikz_InformaxionSms_Model_Session unsSmsCode()
 * @method Qbikz_InformaxionSms_Model_Session setRpToken(string $val)
 * @method string getRpToken()
 *
 * @category   Qbikz
 * @package    Qbikz_InformaxionSms
 * @author     Qbikz Core Team <sergey.yur@gmail.com>
 */
class SmsWeb_SMSNotifications_Model_Session extends Mage_Core_Model_Session_Abstract
{
    public function __construct($data=array())
    {
        $name = isset($data['name']) ? $data['name'] : null;
        $this->init('smsalert', $name);
    }

    /**
     * Retrieve Session SMS Code
     *
     * @var bool $clear
     *
     * @return string A unique SMS code for customer
     */
    public function getSmsCode($clear = false)
    {
        if (!$this->getData('sms_code')) {
            $this->setData('sms_code', Mage::helper('smsalert')->generateSmsCode());
        }

        return $this->getData('sms_code', $clear);
    }

    /**
     * Retrieve new Session SMS Code
     *
     * @return string A unique SMS code for customer
     */
    public function newSmsCode()
    {
        return $this->unsSmsCode()->getSmsCode();
    }

    /**
     * Retrieve Session SMS Code of restore password
     *
     * @var bool $clear
     *
     * @return string A unique SMS code for customer
     */
    public function getSmsRPCode($clear = false)
    {
        if (!$this->getData('sms_rpcode')) {
            $this->setData('sms_rpcode', Mage::helper('imsms')->generateSmsCode());
        }

        return $this->getData('sms_rpcode', $clear);
    }

    /**
     * Retrieve new Session SMS Code of restore password
     *
     * @return string A unique SMS code for customer
     */
    public function newSmsRPCode()
    {
        return $this->unsSmsCode()->getSmsRPCode();
    }

    /**
     * Unset SMS restore password code from the object
     *
     * @return $this
     */
    public function unsSmsRPCode()
    {
        $this->unsetData('sms_rpcode');

        return $this;
    }

    /**
     * @return bool
     */
    public function isValidRpToken($token)
    {
        return $this->getRpToken() == $token;
    }
}
