<?php
/**
 * Renderer for Qbikz InformaxionSms information
 *
 * @method string getHomeUrl()
 * @method string getInfoUrl()
 * @method string getPhoneNumber()
 * @method string getEmail()
 *
 * @category   Qbikz
 * @package    Qbikz_InformaxionSms
 * @author     Qbikz Core Team <sergey.yur@gmail.com>
 */
 class SmsWeb_SMSNotifications_Block_Adminhtml_System_Config_Form_Field_Info extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Template path
     *
     * @var string
     */
    protected $_template = 'smsweb/smsalert/system/config/field/info.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $elementOriginalData = $element->getOriginalData();

        if (isset($elementOriginalData['home_link'])) {
            $url = sprintf('<a href="%s" target="_blank" title="%s">%s</a>', $elementOriginalData['home_link'], $this->__('Open in new window'), $elementOriginalData['home_link']);
            $this->setHomeUrl($url);
        }

        if (isset($elementOriginalData['info_link'])) {
            $this->setInfoUrl($elementOriginalData['info_link']);
        }

        if (isset($elementOriginalData['phone_number'])) {
            $this->setPhoneNumber($elementOriginalData['phone_number']);
        }

        if (isset($elementOriginalData['email'])) {
            $this->setEmail(sprintf('<a title="%s" href="mailto:%s?subject=%s&body=%s">%s</a>',
                $this->__('Write message'),
                $elementOriginalData['email'],
                $this->__('Support of SMS Alert'),
                $this->__('Hello,'),
                $elementOriginalData['email']
            ));
        }

        $columns = ($this->getRequest()->getParam('website') || $this->getRequest()->getParam('store')) ? 5 : 4;
        return $this->_decorateRowHtml($element, "<td colspan='$columns'>" . $this->toHtml() . '</td>');
    }
}