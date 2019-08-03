<?php
require_once 'Mage/Checkout/controllers/CartController.php';
require_once(Mage::getBaseDir('lib') . '/smsalert/Smsalert.php');
class Starknic_Starknic_CartController extends Mage_Checkout_CartController
{
    public function isMobileVerReqAction()
    {
        if(!Mage::getSingleton('customer/session')->isLoggedIn() && empty(Mage::getSingleton('core/session')->getMobNumber())){
            $result = array("type"=>"required", "message"=>"");
        }else{
            $result = array("type"=>"not_required", "message"=>"");
        }
        $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    public function mobileVerifyAction()
    {
        $customer_email = $_GET['mobNo']."@starknic.com";
        $custExist = false;
        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer->loadByEmail($customer_email);
        if($customer->getId())
        {
            $custExist = true;
            Mage::getSingleton('core/session')->setMobNumber($_GET['mobNo']);
        }
        if(!$custExist && !Mage::getSingleton('customer/session')->isLoggedIn() && empty(Mage::getSingleton('core/session')->getMobNumber())){
            $otp = rand(100000, 999999);
            $_SESSION['session_otp'] = $otp;
            Mage::getSingleton('core/session')->setMobNumber($_GET['mobNo']);
            $message= $otp." is the OTP for your Starknic Account."; // write your msg here between ""
            $result = Mage::helper('smsnotifications/data')->sendSms($message,$_GET['mobNo']);
            if($result) {
                $result = array("type"=>"success", "message"=>"OTP successfully sent.");
            } else {
                $result = array("type"=>"error", "message"=>"Failed to sent OTP");
            }
        }else{
            $result = array("type"=>"not_required", "message"=>"");
        }
        $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    public function verifyOTPAction()
    {
        $otp = $_GET['otp'];
        
        if ($otp == $_SESSION['session_otp']) {
            unset($_SESSION['session_otp']);
            $result = array("type"=>"success", "message"=>"Your mobile number is verified!");
            $websiteId = Mage::app()->getWebsite()->getId();
            $store = Mage::app()->getStore();
            $firstname = "";
            $lastname = "";
            $email = Mage::getSingleton('core/session')->getMobNumber()."@starknic.com";
            $password = "00000000";
        
            $customer = Mage::getModel("customer/customer");
            $customer->setWebsiteId($websiteId)
                     ->setStore($store)
                     ->setFirstname($firstname)
                     ->setLastname($lastname)
                     ->setEmail($email)
                     ->setPassword($password);
            $customer->save();
        } else {
            $result = array("type"=>"error", "message"=>"Mobile number verification failed");
        }
        $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    public function buyNowAction()
    {
        $result = true;
        $productId = $_GET['productId'];
        $cart = Mage::getModel('checkout/session')->getQuote();
        try {
            $count = count(Mage::helper('checkout/cart')->getItemsCount());
            Mage::log($count, null, "ashok2.log", "true");
            if(Mage::helper('checkout/cart')->getItemsCount() > 0 ) {
                foreach ($cart->getAllVisibleItems() as $item) 
                {
                  Mage::log($item->getQty(), null, "ashok2.log", "true");
                  if ($item->getProduct()->getId() == $productId) {
                      if($item->getQty() != 1) {
                          $item->setQty(1);
                          $item->save();
                          $cart->save();
                      }
                  }else {
                        $cartHelper = Mage::helper('checkout/cart');
                        $cartItemId = $item->getId();
                        $cartHelper->getCart()->removeItem($cartItemId)->save();
                  }
                }
            } else {
                $product = Mage::getModel('catalog/product')->load($productId);
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                $stockQty = (int)$stock->getQty();
                $stockAvl = $stock->getIsInStock();
                if($stockQty > 0 && $stockAvl) {
                    if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                        $quote = Mage::getModel('checkout/cart');
                        $quote->init();
                        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
                        Mage::log($customerId, null, "ashok2.log", "true");
                        $customer = Mage::getModel('customer/customer')->load($customerId);
                        Mage::log(10, null, "ashok2.log", "true");
                        //$quote = Mage::getModel('sales/quote')->loadByCustomer($customer);
                        Mage::log(11, null, "ashok2.log", "true");
                        $quote->addProduct($product, array( 'product_id' => $productId, 'qty' => 1));
                        Mage::log(12, null, "ashok2.log", "true");
                        //$quote->collectTotals()->save();
                        $quote->save();
                        Mage::log(13, null, "ashok2.log", "true");
                    } else {
                        $cart = Mage::getSingleton('checkout/cart');
                        $cart->init();
                        $paramater = array('product' => $productId,
                                           'qty' => '1',
                                           'form_key' => Mage::getSingleton('core/session')->getFormKey()
                        );
                        $request = new Varien_Object();
                        $request->setData($paramater);
                        $cart->addProduct($product, $request);
                        $cart->save();
                    }
                }else {
                    $result = false;
                }
            }
        }
        catch (Exception $e) {
            $result = false;
        }
        $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    public function isAvailableAction()
    {
        $isAvailable = false;
        $productId = $_GET['productId'];
        $product = Mage::getModel('catalog/product')->load($productId);
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $stockQty = (int)$stock->getQty();
        $stockAvl = $stock->getIsInStock();
        if($stockQty > 0 && $stockAvl) {
            $isAvailable = true;
        }
        $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($isAvailable));
    }
}
?> 