<?php
class MGS_Mpanel_Block_Edit_Block extends Mage_Core_Block_Template
{
	protected $_blockId;
	protected $_themeName;
	protected $_block;
	
	public function __construct()
	{
		$this->_blockId = 'block'.$this->getRequest()->getParam('id');
		$this->_themeName = $this->getRequest()->getParam('layout');
		$storeId = Mage::app()->getStore()->getId();
		
		$block = Mage::getModel('mpanel/blocks')
			->getCollection()
			->addFieldToFilter('name', $this->_blockId)
			->addFieldToFilter('theme_name', $this->_themeName)
			->addFieldToFilter('store_id', $storeId)
			->getFirstItem();
		$this->_block = $block;
	}
	
	
}