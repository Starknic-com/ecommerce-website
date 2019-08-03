<?php

class Mage_Ccavenuepay_Model_Source_TransactionMode
{
    public function toOptionArray() {
        return [
            [
                'value' => "TEST",
                'label' => __('Test')
            ],
            [
                'value' => "LIVE",
                'label' => __('Live')
            ]
        ];
    }
}
