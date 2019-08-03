<?php
abstract class SmsWeb_SMSNotifications_Model_Sms_Data_Abstract implements SmsWeb_SMSNotifications_Model_Sms_Data_Interface
{
    /**
     * Return rendered of SMS by variables
     *
     * @param string $text
     *
     * @return string
     */
    public function renderSms($text)
    {
        foreach ($this->_getSmsVars() as $k => $v) {
            $text = str_replace(sprintf('{%s}',$k), $v, $text);
        }

        return trim($text);
    }

    /**
     * Return variables for SMS text/template
     *
     * @return array variables
     */
    abstract protected function _getSmsVars();
}