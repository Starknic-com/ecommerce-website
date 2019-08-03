<?php
/**
 * Renderer for Informaxion Sms banner in System Configuration
 *
 * @category   Qbikz
 * @package    Qbikz_InformaxionSms
 * @author     Qbikz Core Team <sergey.yur@gmail.com>
 */
class SmsWeb_SMSNotifications_Block_Adminhtml_System_Config_Form_Fieldset_Hint extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'smsweb/smsalert/system/config/fieldset/hint.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $elementOriginalData = $element->getOriginalData();
        if (isset($elementOriginalData['help_link'])) {
            $this->setHelpLink($elementOriginalData['help_link']);
        }
        $js = '';
        return $this->toHtml() . $this->helper('adminhtml/js')->getScript($js);
    }
}
