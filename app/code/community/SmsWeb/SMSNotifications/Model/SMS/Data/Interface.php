<?php
interface SmsWeb_SMSNotifications_Model_Sms_Data_Interface
{
    /**
     * Return rendered of SMS/text
     *
     * @param string $text
     * @return array
     */
    public function renderSms($text);
}