<?php

class Ei_Creditpoint_Model_Creditpoint extends Mage_Core_Model_Abstract
{
    public function _construct()
    {	
     parent::_construct();
     $this->_init('creditpoint/creditpoint');
        
    }
}
