<?php
class MGS_Mpanel_Model_Source_Tabs{

    public function toOptionArray() {
        return array(
			array('value' => '', 'label' => ''),
			array('value' => 'static', 'label' => Mage::helper('mpanel')->__('Static block')),
			array('value' => 'attribute', 'label' => Mage::helper('mpanel')->__('Product Attribute'))
        );
    }
}