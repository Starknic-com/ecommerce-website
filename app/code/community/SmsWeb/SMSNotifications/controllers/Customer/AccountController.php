<?php
require_once 'Mage/Customer/controllers/AccountController.php';

/**
 * Customer account controller
 *
 * @category   Qbikz
 * @package    Qbikz_InformaxionSms
 * @author     Qbikz Core Team <sergey.yur@gmail.com>
 */
class SmsWeb_SMSNotifications_Customer_AccountController extends Mage_Customer_AccountController
{
    public function loginConfirmAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
		//print_r($this->getLayout()->getUpdate()->getHandles());
		//exit();
    }

    public function loginConfirmPostAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/loginConfirm');
            return;
        }

        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('customer/account/');
            return;
        }

        $session = $this->_getSession();

        if ($this->getRequest()->isPost()) {
            $smscode = $this->getRequest()->getPost('smscode');
            if (!empty($smscode)) {
                try {
                    $username = Mage::getSingleton('smsnotifications/session')->getAuthUser();
                    $password = Mage::getSingleton('smsnotifications/session')->getAuthPassword();

                    /** @var $customer Mage_Customer_Model_Customer */
                    $customer = Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
                    if ($customer->authenticate($username, $password)) {
						$bill = $customer->getDefaultBillingAddress();
						
					   if (Mage::helper('smsnotifications/data')->verifyotp($smscode,$bill->getTelephone())) {
                            $session->login($username, $password);
                        } else {
                            $session->addError($this->__('The SMS code "%s" is not confirmed.', $smscode));
                            $session->setSms($smscode);
                            return $this->_redirect('*/*/loginConfirm');
                        }
                        Mage::getSingleton('smsnotifications/session')->unsSmsCode();
                    } else {
                        $session->addError($this->__('Auth called error. Please repeat it again.'));
                        return $this->_redirect('customer/account/login');
                    }
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = Mage::helper('customer')->getEmailConfirmationUrl($username);
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $session->addError($message);
                    $session->setSms($smscode);
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $session->addError($this->__('SMS code is required.'));
            }
        }
        $state = "/";
        if(($referer = Mage::getSingleton('customer/session')->getBeforeAuthUrl(true))){
            $state = $referer;
        }
        if (strpos($state, "customer/account") != false) { 
            $state = Mage::getBaseUrl();
        }
        $this->_redirect($state);
        //$this->_redirect('customer/account/');
    }

    public function resetPasswordConfirmAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
    }

    public function resetPasswordConfirmPostAction()
    {
        $session = $this->_getSession();

        $customerId = (int) $this->getRequest()->getQuery('id');
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = $this->_getModel('customer/customer')->load($customerId);
		$bill = $customer->getDefaultBillingAddress();
        if (!$customer->getId()) {
            $session->addError($this->__('Unknown customer.'));
        } else if ($this->getRequest()->isPost()) {
            $smscode = $this->getRequest()->getPost('smscode');
			
            if (!empty($smscode)) {
                try {
					
                    if (!(Mage::helper('smsnotifications/data')->verifyotp($smscode,$bill->getTelephone()))) {
                        $session->addError($this->__('The SMS code "%s" is not confirmed.', $smscode));
                        $session->setSmsRPCode($smscode);
                        return $this->_redirect('*/*/resetPasswordConfirm', array('_current'=>true));
                    }
                    Mage::getSingleton('smsnotifications/session')->unsSmsRPCode();
                    Mage::getSingleton('smsnotifications/session')->setRpToken($customer->getRpToken());

                    return $this->_redirect('customer/account/resetpassword', array('_current'=>true));
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        default:
                            $message = $e->getMessage();
                    }
                    $session->addError($message);
                    $session->setSmsCode($smscode);
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $session->addError($this->__('SMS code is required.'));
            }
        }

        return $this->_redirect('*/*/resetPasswordConfirm', array('_current'=>true));
    }
}