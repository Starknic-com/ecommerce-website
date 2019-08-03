<?php
/**
 * SMS-services - SMS notification & SMS marketing
 *
 * @category   Qbikz
 * @package    Qbikz_InformaxionSms
 * @author     Qbikz Core Team <sergey.yur@gmail.com>
 */
class SmsWeb_SMSNotifications_Model_Sms_Data_Order_Status extends SmsWeb_SMSNotifications_Model_Sms_Data_Abstract
{
    /** @var  Mage_Sales_Model_Order */
    protected $_order;

    /** @var  string */
    protected $_comment;

    /** @var  string */
    protected $_oldStatus;

    /**
     * Init Class
     * Required parameter is "order"
     *
     * @param array $cnf
     * @throws Exception
     */
    public function __construct(array $cnf)
    {
        if (!array_key_exists('order', $cnf)) {
            throw new Exception(Mage::helper('imsms')->__('Order is not found'));
        }

        if (!$cnf['order'] instanceof Mage_Sales_Model_Order) {
            throw new Exception(Mage::helper('imsms')->__('Unknown order'));
        }

        $this->_order = $cnf['order'];

        if (isset($cnf['comment'])) {
            $this->_comment = $cnf['comment'];
        }

        $this->_setup();
    }

    /**
     * Setup params of the class
     */
    protected function _setup()
    {
        foreach ($this->_order->getStatusHistoryCollection() as $h) {
            $this->_oldStatus = $h->getStatus();
            break;
        }
    }

    /**
     * Return variables for SMS text/template
     *
     * @return array variables
     */
    protected function _getSmsVars()
    {
        $ret = array(
            'customer_firstname'   => $this->_order->getCustomerFirstname(),
            'customer_lastname'    => $this->_order->getCustomerLastname(),
            'order_id'             => $this->_order->getIncrementId(),
            'order_old_status'     => ucfirst($this->_oldStatus),
            'order_new_status'     => ucfirst($this->_order->getStatus()),
            'order_status_comment' => $this->_comment,
        );

        return $ret;
    }
}