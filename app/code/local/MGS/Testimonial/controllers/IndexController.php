<?php
class MGS_Testimonial_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/testimonial?id=15 
    	 *  or
    	 * http://site.com/testimonial/id/15 	
    	 */
    	/* 
		$testimonial_id = $this->getRequest()->getParam('id');

  		if($testimonial_id != null && $testimonial_id != '')	{
			$testimonial = Mage::getModel('testimonial/testimonial')->load($testimonial_id)->getData();
		} else {
			$testimonial = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($testimonial == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$testimonialTable = $resource->getTableName('testimonial');
			
			$select = $read->select()
			   ->from($testimonialTable,array('testimonial_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$testimonial = $read->fetchRow($select);
		}
		Mage::register('testimonial', $testimonial);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}