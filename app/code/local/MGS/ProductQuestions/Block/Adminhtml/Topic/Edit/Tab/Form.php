<?php

/* * ****************************************************
 * Package   : ProductQuestions
 * Author    : http://www.arrowhitech.com
 * Copyright : (c) 2013 - ArrowHiTech.Com
 * ***************************************************** */
?>
<?php

class MGS_ProductQuestions_Block_Adminhtml_Topic_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('topic_form', array('legend' => Mage::helper('productquestions')->__('Topic Details')));

        $fieldset->addField('title', 'text', array(
            'label' => Mage::helper('productquestions')->__('Title'),
            'required' => true,
            'class' => 'required-entry',
            'name' => 'title',
        ));

//        $fieldset->addField('parent_id', 'select', array(
//            'label' => Mage::helper('productquestions')->__('Parent Topic'),
//            'required' => false,
//            'name' => 'parent_id',
//            'values' => Mage::getSingleton('productquestions/topic')->getOptionArray(),
//        ));

        $fieldset->addField('identifier', 'text', array(
            'label' => Mage::helper('productquestions')->__('Identifier'),
            'required' => false,
            'class' => 'validate-identifier',
            'name' => 'identifier',
        ));

        $fieldset->addField('show_on_block', 'select', array(
            'label' => Mage::helper('productquestions')->__('Show On Block'),
            'name' => 'show_on_block',
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('productquestions')->__('Yes'),
                ),
                array(
                    'value' => 0,
                    'label' => Mage::helper('productquestions')->__('No'),
                ),
            ),
        ));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('productquestions')->__('Status'),
            'name' => 'status',
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('productquestions')->__('Enabled'),
                ),
                array(
                    'value' => 0,
                    'label' => Mage::helper('productquestions')->__('Disabled'),
                ),
            ),
        ));

        if (Mage::getSingleton('adminhtml/session')->getTopicData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getTopicData());
            Mage::getSingleton('adminhtml/session')->setTopicData(null);
        } elseif (Mage::registry('topic_data')) {
            $form->setValues(Mage::registry('topic_data')->getData());
        }
        return parent::_prepareForm();
    }

}
