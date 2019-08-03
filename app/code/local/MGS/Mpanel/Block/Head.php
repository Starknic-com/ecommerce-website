<?php
class MGS_Mpanel_Block_Head extends Mage_Core_Block_Template
{
	
	protected function _prepareLayout()
    {
		if(Mage::getStoreConfig('mpanel/general/enabled')){
			$headBlock = $this->getLayout()->getBlock('head');
			$setting = $this->helper('mpanel')->getThemeSettings();
			$fonts = array();
			$fonts[] = $setting['font'];
			
			if(!in_array($setting['h1'], $fonts)){
				$fonts[] = $setting['h1'];
			}
			
			if(!in_array($setting['h2'], $fonts)){
				$fonts[] = $setting['h2'];
			}
			
			if(!in_array($setting['h3'], $fonts)){
				$fonts[] = $setting['h3'];
			}
			
			if(!in_array($setting['price'], $fonts)){
				$fonts[] = $setting['price'];
			}
			
			if(!in_array($setting['menu'], $fonts)){
				$fonts[] = $setting['menu'];
			}
			
			$links = '';
			
			foreach($fonts as $_font){
				$links .= '<link href="//fonts.googleapis.com/css?family='.$_font.'" rel="stylesheet" type="text/css"/>';
			}
			
			if($setting['custom_css']){
				$headBlock->addCss('css/custom.css');
			}
			
			$headBlock->addLinkRel('search', '/catalogsearch/advanced/index" />'.$links.'
			<link rel="stylesheet" type="text/css" media="screen" href="'.Mage::getUrl('mpanel/index/style').'"/>
			<link rel="author" href="'.Mage::getBaseUrl().'');
			return parent::_prepareLayout();
		}
    }
}