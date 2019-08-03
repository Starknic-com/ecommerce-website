<?php

class MGS_Mpanel_Helper_Data extends MGS_Mgscore_Helper_Data {

	protected $_ids;

    // Check to accept to use builder panel
    public function acceptToUsePanel() {
        if ($this->showButton() && (Mage::getSingleton('core/session')->getUsePanel() == 1)) {
            return true;
        }
        return false;
    }

    public function showButton() {

        if (Mage::getStoreConfig('mpanel/general/enabled')) {

            $logedAccountId = Mage::getSingleton('customer/session')->getCustomer()->getEmail();

            $acceptAccounts = Mage::getStoreConfig('mpanel/general/account');
            if ($acceptAccounts == '') {
                return false;
            }
            $acceptAccountIds = explode(',', $acceptAccounts);

            if ((count($acceptAccountIds) > 0) && (in_array($logedAccountId, $acceptAccountIds))) {
                return true;
            }
        }

        return false;
    }

    // Check cms page, if is cms page return true
    public function isCmsPage() {
        $module = Mage::app()->getRequest()->getModuleName();
        if ($module == 'cms') {
            return true;
        }
        return false;
    }

    // Check customer page, if is customer page return true
    public function isCustomerPage() {
        $module = Mage::app()->getRequest()->getModuleName();
        $controller = Mage::app()->getRequest()->getControllerName();
        $action = Mage::app()->getRequest()->getActionName();
        $str = $module . '-' . $controller . '-' . $action;
        if ($str == 'customer-account-index' || $str == 'customer-account-edit' || $str == 'customer-address-index' || $str == 'sales-order-history' || $str == 'sales-billing_agreement-index' || $str == 'sales-recurring_profile-index' || $str == 'review-customer-index' || $str == 'tag-customer-index' || $str == 'wishlist-index-index' || $str == 'oauth-customer_token-index' || $str == 'newsletter-manage-index' || $str == 'downloadable-customer-products' || $str == 'productquestions-index-index') {
            return true;
        }
        return false;
    }

    // Check homepage, if is homepage return true
    public function isHomepage() {
        if (Mage::getSingleton('cms/page')->getIdentifier() == 'home' && Mage::app()->getFrontController()->getRequest()->getRouteName() == 'cms') {
            return true;
        }
        return false;
    }

    // check category and product detail page
    public function isCatalogPage() {
        $module = Mage::app()->getRequest()->getModuleName();
        if ($module == 'catalog') {
            return true;
        }
        return false;
    }

    // check category page
    public function isCategoryPage() {
        $controller = Mage::app()->getRequest()->getControllerName();
        if ($controller == 'category') {
            return true;
        }
        return false;
    }

    // check product page
    public function isProductPage() {
        $controller = Mage::app()->getRequest()->getControllerName();
        if ($controller == 'product') {
            return true;
        }
        return false;
    }

    // Get all page layout of site (1 column, 2 columns left...), return dropdown html
    public function getPageLayoutHtml($pageId) {
        $page = Mage::getModel('cms/page')->load($pageId);

        $storeIds = $page->getStoreId();
        $html = '';

        if (count($storeIds) > 0) {
            foreach ($storeIds as $storeId) {
                $html .= '<input type="hidden" name="stores[]" value="' . $storeId . '"/>';
            }
        }

        $html .= '<select name="root_template" class="page-layout" onchange="this.form.submit();">';

        foreach (Mage::getSingleton('page/config')->getPageLayouts() as $layout) {
            $label = $layout->getLabel();
            $value = $layout->getCode();
            $html .= '<option value="' . $value . '"';
            if ($page->getRootTemplate() == $value) {
                $html .= ' selected="selected"';
            }
            $html .= '>' . $label . '</option>';
        }

        $html .= '</select>';

        return $html;
    }

    // Get all page layout (1 column, 2 columns left...) for catalog page
    public function getCatalogLayoutUpdate() {
        if (Mage::registry('current_product')) {
            $product = Mage::registry('current_product');
            $currentLayout = $product->getPageLayout();
        } else {
            $category = Mage::registry('current_category');
            $currentLayout = $category->getPageLayout();
        }
        $layout = Mage::getSingleton('page/source_layout')->toOptionArray();
        array_unshift($layout, array('value' => '', 'label' => Mage::helper('catalog')->__('No layout updates')));

        $html = '<select name="general[page_layout]" class="page-layout" onchange="this.form.submit();">';
        foreach ($layout as $_layout) {
            $html .= '<option value="' . $_layout['value'] . '"';
            if ($currentLayout == $_layout['value']) {
                $html .= ' selected="selected"';
            }
            $html .= '>' . $_layout['label'] . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    public function getPageSettings() {
        if (Mage::registry('current_product')) {
            return $this->getLayout()->createBlock('core/template')->setTemplate('mgs/mpanel/panel/product-settings.phtml')->toHtml();
        } else {
            $category = Mage::registry('current_category');
            return $this->getLayout()->createBlock('core/template')->setCategory($category)->setTemplate('mgs/mpanel/panel/category-settings.phtml')->toHtml();
        }
    }

    // Check homepage has use builder panel or not
    public function useHomepageBuilder() {
        $storeId = Mage::app()->getStore()->getId();
        $homeStore = Mage::getModel('mpanel/store')
                ->getCollection()
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('status', 1);
        if (count($homeStore) > 0) {
            return true;
        }
        return false;
    }

    // Return html of dropdown homepage config (Use CMS Page, Use Homepage Builder)
    public function getHomepageConfigHtml() {
        /* $html = '<select name="status" id="homepageconfig" onchange="checkBuilder(this.value)">';
          $html .= '<option value="0">'.$this->__('Use CMS Page').'</option>';
          $html .= '<option value="1"';

          if($this->useHomepageBuilder()){
          $html .= ' selected="selected"';
          }

          $html .= '>'.$this->__('Use Homepage Builder').'</option>';
          $html .= '</select>'; */

        $html = '<div class="form-group"><div class="checkbox"><label for="homesetting">
				<input type="checkbox" id="homesetting" onclick="checkBuilder(); switchBuilder();"';

        if ($this->useHomepageBuilder()) {
            $html .= ' checked="checked"';
        }

        $html .= '/> ' . $this->__('Use Builder');
        $html .= '</label></div></div>';

        return $html;
    }

    // Get all homepage layout from database
    public function getHomeLayouts() {
        $layouts = Mage::getModel('mpanel/home')
                ->getCollection();
        return $layouts;
    }

    // Check a layout have active or not
    public function isActiveLayout($layoutName) {
        $storeId = Mage::app()->getStore()->getId();
        $homeStore = Mage::getModel('mpanel/store')
                ->getCollection()
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('name', $layoutName);
        if ($homeStore->getFirstItem()->getStatus()) {
            return true;
        }
        return false;
    }

    // Get WYSIWYG Editor config
    public function getConfig($data = array()) {
        $config = new Varien_Object();

        $config->setData(array(
            'enabled' => true,
            'hidden' => 1,
            'use_container' => false,
            'add_variables' => false,
            'add_widgets' => true,
            'no_display' => false,
            'translator' => Mage::helper('cms'),
            'encode_directives' => true,
            'directives_url' => str_replace('https','http',Mage::getUrl('mpanel/wysiwyg/directive')),
            'widget_window_url' => str_replace('https','http',Mage::getUrl('mpanel/adminhtml_widget/index')),
            'popup_css' =>
            Mage::getBaseUrl('js') . 'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/dialog.css',
            'content_css' =>
            Mage::getBaseUrl('js') . 'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/content.css',
            'width' => '100%',
            'plugins' => array(
            /* array(
              'name'=>'magentovariable',
              'src'=>  Mage::getBaseUrl('js').'mage/adminhtml/wysiwyg/tiny_mce/plugins/magentovariable/editor_plugin.js',
              'options'=> array(
              'title'=>'Insert Variable...',
              'url'=> Mage::getUrl('mpanel/variable/wysiwygPlugin'),
              'onclick'=> array(
              'search'=> array(
              'html_id'
              ),
              'subject'=> "MagentovariablePlugin.loadChooser('".Mage::getUrl('mpanel/variable/wysiwygPlugin')."', '{{html_id}}');"
              ),
              'class'=> 'add-variable plugin'
              )
              ),

              array(
              'name'=>'magentowidget',
              'src'=> Mage::getBaseUrl('js').'mage/adminhtml/wysiwyg/tiny_mce/plugins/magentowidget/editor_plugin.js',

              ) */
            ),
            'directives_url_quoted' => str_replace('https','http',Mage::getUrl('mpanel/wysiwyg/directive'))
        ));

        //$config->setData('directives_url_quoted', preg_quote($config->getData('directives_url')));

        $config->addData(array(
            'add_images' => true,
            'files_browser_window_url' => str_replace('https','http',Mage::getUrl('mpanel/wysiwyg/index')),
            'files_browser_window_width' => (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_width'),
            'files_browser_window_height' => (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_height'),
            'widget_plugin_src' => Mage::getBaseUrl('js') . 'mage/adminhtml/wysiwyg/tiny_mce/plugins/magentowidget/editor_plugin.js',
            'widget_images_url' => Mage::getDesign()->getSkinUrl('images/widget', array('_area' => 'adminhtml')),
        ));


        if (is_array($data)) {
            $config->addData($data);
        }

        Mage::dispatchEvent('cms_wysiwyg_config_prepare', array('config' => $config));

        return $config;
    }

    // Get edit panel of a block
    public function getEditPanel($layout, $id) {
        $html = '<div class="edit-panel parent-panel"><ul>';
        $html .='<li><a href="' . Mage::getUrl('mpanel/edit/block', array('layout' => $layout, 'id' => $id)) . '" class="popup-link" title="' . $this->__('Edit') . '"><em class="fa fa-gear"></em></a></li>';
        $html .='</ul></div>';

        return $html;
    }

    // Get edit panel of a header
    public function getEditHeaderPanel() {
        $html = '<div class="edit-panel"><ul><li><a class="popup-link" href="' . Mage::getUrl('mpanel/edit/header') . '" title="' . $this->__('Edit Header') . '"><em class="fa fa-gear"></em></a></li></ul></div>';
        return $html;
    }

    // Get edit panel of a footer
    public function getEditFooterPanel() {
        $html = '<div class="edit-panel"><ul><li><a class="popup-link" href="' . Mage::getUrl('mpanel/edit/footer') . '" title="' . $this->__('Edit Footer') . '"><em class="fa fa-gear"></em></a></li></ul></div>';
        return $html;
    }

    // Add edit panel for logo
    public function getEditLogoPanel() {
        $html = '<div class="edit-panel logo-panel child-panel"><ul>';
        $html .= '<li><a href="' . Mage::getUrl('mpanel/post/logo') . '" class="popup-link" title="' . $this->__('Upload Logo') . '"><em class="fa fa-edit">&nbsp;</em></a></li>';
        $html .= '</ul></div>';

        return $html;
    }

    // Add edit panel for category image
    public function getEditCategoryImage($id) {
        $html = '<div class="edit-panel inline-panel"><ul>';
        $html .= '<li><a href="' . Mage::getUrl('mpanel/post/categoryImage', array('id' => $id)) . '" class="popup-link" title="' . $this->__('Upload Image For This Category') . '"><em class="fa fa-edit">&nbsp;</em></a></li>';
        $html .= '</ul></div>';

        return $html;
    }

    // Add edit panel for product tab
    public function getDeleteProductTab($alias) {
        $html = '<div class="edit-panel child-panel"><ul>';
        $html .= '<li><a href="' . Mage::getUrl('mpanel/post/deleteProductTab', array('alias' => $alias)) . '" onclick="return confirm(\'' . $this->__('Are you sure you would like to remove this tab?') . '\')" title="' . $this->__('Delete') . '"><em class="fa fa-trash">&nbsp;</em></a></li>';
        $html .= '</ul></div>';

        return $html;
    }

    // Add edit panel for category description
    public function getEditCategoryDescription($id) {
        $html = '<div class="edit-panel inline-panel"><ul>';
        $html .= '<li><a href="' . Mage::getUrl('mpanel/post/categoryDescription', array('id' => $id)) . '" class="popup-link" title="' . $this->__('Edit Description For This Category') . '"><em class="fa fa-edit">&nbsp;</em></a></li>';
        $html .= '</ul></div>';

        return $html;
    }

    // Add edit panel for welcome text and copyright
    public function getEditStoreConfig($tag, $text) {
        $html = '<div class="edit-panel inline-panel ' . $tag . '-config"><ul>';
        $html .= '<li><a href="#" onclick="toggleEl(\'' . $tag . '\'); return false" class="edit-inline" title="' . $this->__('Edit') . '"><em class="fa fa-edit">&nbsp;</em></a><div class="input-inline" style="display:none" id="' . $tag . '">';
		
		if($tag=='design-footer-copyright'){
			$html .= '<textarea type="text" id="' . $tag . '-input" class="input-text edit-input">'.$text.'</textarea>';
		}
		else{
			$html .= '<input type="text" value="' . $text . '" id="' . $tag . '-input" class="input-text edit-input"/>';
		}
		
		$html .= '<button type="button" onclick="saveStoreConfig(\'' . $tag . '\',\'' . $this->__('Save') . '\',\'' . $this->__('Saving...') . '\')" class="btn btn-primary btn-save-config">' . $this->__('Save') . '</button></div></li>';
        $html .= '</ul></div>';
        return $html;
    }

    // Add edit panel for gmap
    public function getEditMapPanel() {
        $html = '<div class="edit-panel map-panel"><ul>';
        $html .= '<li><a href="' . Mage::getUrl('mpanel/post/map') . '" class="popup-link" title="' . $this->__('Edit Map Information') . '"><em class="fa fa-edit">&nbsp;</em></a></li>';
        $html .= '</ul></div>';

        return $html;
    }

    // Add edit panel for contact information
    public function getEditContactInfoPanel() {
        $html = '<div class="edit-panel contact-info-panel"><ul>';
        $html .= '<li><a href="' . Mage::getUrl('mpanel/post/info') . '" class="popup-link" title="' . $this->__('Edit Contact Information') . '"><em class="fa fa-edit">&nbsp;</em></a></li>';
        $html .= '</ul></div>';

        return $html;
    }

    // Get block content by layout and block_id
    public function getBlockContent($layout, $id) {
        
    }

    //Return content of a homepage if homepage use builder panel
    public function getLayoutConfig() {
        $storeId = Mage::app()->getStore()->getId();

        $config = Mage::getModel('mpanel/store')
                ->getCollection()
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('status', 1);

        if ($this->acceptToUsePanel()) {
            return $this->getLayout()->createBlock('mpanel/template')->setTemplate('mgs/mpanel/template/admin/' . $config->getFirstItem()->getName() . '.phtml')->toHtml();
        } else {
            return $this->getLayout()->createBlock('mpanel/template')->setTemplate('mgs/mpanel/template/' . $config->getFirstItem()->getName() . '.phtml')->toHtml();
        }
    }

    //Return content of a category right if use builder panel
    public function getLayoutConfigCategoryRight() {
        if ($this->acceptToUsePanel()) {
            return $this->getLayout()->createBlock('mpanel/template')->setTemplateLayout('category_right')->setBlockName('block_category_right')->setTemplate('mgs/mpanel/template/admin/category_right.phtml')->toHtml();
        } else {
            return $this->getLayout()->createBlock('mpanel/template')->setTemplateLayout('category_right')->setBlockName('block_category_right')->setTemplate('mgs/mpanel/template/category_right.phtml')->toHtml();
        }
    }

    //Return content of a category left if use builder panel
    public function getLayoutConfigCategoryLeft() {
        if ($this->acceptToUsePanel()) {
            return $this->getLayout()->createBlock('mpanel/template')->setTemplateLayout('category_left')->setBlockName('block_category_left')->setTemplate('mgs/mpanel/template/admin/category_left.phtml')->toHtml();
        } else {
            return $this->getLayout()->createBlock('mpanel/template')->setTemplateLayout('category_left')->setBlockName('block_category_left')->setTemplate('mgs/mpanel/template/category_left.phtml')->toHtml();
        }
    }

    //Return content of a cms right if use builder panel
    public function getLayoutConfigCmsRight() {
        if ($this->acceptToUsePanel()) {
            return $this->getLayout()->createBlock('mpanel/template')->setTemplateLayout('cms_right')->setBlockName('block_cms_right')->setTemplate('mgs/mpanel/template/admin/cms_right.phtml')->toHtml();
        } else {
            return $this->getLayout()->createBlock('mpanel/template')->setTemplateLayout('cms_right')->setBlockName('block_cms_right')->setTemplate('mgs/mpanel/template/cms_right.phtml')->toHtml();
        }
    }

    //Return content of a cms left if use builder panel
    public function getLayoutConfigCmsLeft() {
        if ($this->acceptToUsePanel()) {
            return $this->getLayout()->createBlock('mpanel/template')->setTemplateLayout('cms_left')->setBlockName('block_cms_left')->setTemplate('mgs/mpanel/template/admin/cms_left.phtml')->toHtml();
        } else {
            return $this->getLayout()->createBlock('mpanel/template')->setTemplateLayout('cms_left')->setBlockName('block_cms_left')->setTemplate('mgs/mpanel/template/cms_left.phtml')->toHtml();
        }
    }

    //Return content of a customer right if use builder panel
    public function getLayoutConfigCustomerRight() {
        if ($this->acceptToUsePanel()) {
            return $this->getLayout()->createBlock('mpanel/template')->setTemplateLayout('customer_right')->setBlockName('block_customer_right')->setTemplate('mgs/mpanel/template/admin/customer_right.phtml')->toHtml();
        } else {
            return $this->getLayout()->createBlock('mpanel/template')->setTemplateLayout('customer_right')->setBlockName('block_customer_right')->setTemplate('mgs/mpanel/template/customer_right.phtml')->toHtml();
        }
    }

    //Return content of a customer left if use builder panel
    public function getLayoutConfigCustomerLeft() {
        if ($this->acceptToUsePanel()) {
            return $this->getLayout()->createBlock('mpanel/template')->setTemplateLayout('customer_left')->setBlockName('block_customer_left')->setTemplate('mgs/mpanel/template/admin/customer_left.phtml')->toHtml();
        } else {
            return $this->getLayout()->createBlock('mpanel/template')->setTemplateLayout('customer_left')->setBlockName('block_customer_left')->setTemplate('mgs/mpanel/template/customer_left.phtml')->toHtml();
        }
    }

    // Return new position for a child block
    public function getNewPositionOfChild($storeId, $blockName, $templateLayout) {
        $child = Mage::getModel('mpanel/childs')
			->getCollection()
			->addFieldToFilter('store_id', $storeId)
			->addFieldToFilter('block_name', $blockName)
			->addFieldToFilter('home_name', $templateLayout)
			->setOrder('position', 'DESC')
			->getFirstItem();
		
		if($child->getId()){
			$position = (int)$child->getPosition() + 1;
		}
		else{
			$position = 1;
		}

        return $position;
    }

    public function getEditChild($layout, $block, $child, $type) {
        $html = '<div class="edit-panel child-panel"><ul>';
		
		$html .= '<li class="sort-handle"><a href="#" onclick="return false;" title="' . $this->__('Move') . '"><em class="fa fa-arrows">&nbsp;</em></a></li>';
		
        $html .= '<li><a href="' . Mage::getUrl('mpanel/index/form', array('template' => $layout, 'block' => $block, 'id' => $child, 'type' => $type)) . '" class="popup-link" title="' . $this->__('Edit') . '"><em class="fa fa-edit">&nbsp;</em></a></li>';
		
		$html .= '<li class="change-col"><a href="javascript:void(0)" title="' . $this->__('Change column setting') . '"><em class="fa fa-columns">&nbsp;</em></a><ul>';
		
		for($i=1; $i<=12; $i++){
			$html .= '<li><a href="'.Mage::getUrl('mpanel/edit/col', array('id' => $child, 'col' => $i)).'" onclick="changeBlockCol(this.href); return false"><span>'.$i.'/12</span></a></li>';
		}
		
		$html .= '</ul></li>';
        
        $html .= '<li><a href="' . Mage::getUrl('mpanel/post/delete', array('id' => $child)) . '" onclick="return confirm(\'' . $this->__('Are you sure you would like to remove this block?') . '\')" title="' . $this->__('Delete') . '"><em class="fa fa-trash">&nbsp;</em></a></li>';
        $html .= '</ul></div>';

        return $html;
    }

    public function getEditChildInCategory($layout, $block, $child, $type, $category_id, $product_id) {
        if ($type == 'core') {
            if ($product_id) {
                $html = '<div class="edit-panel child-panel"><ul>';
                $html .= '<li class="sort-handle"><a href="#" onclick="return false;" title="' . $this->__('Move') . '"><em class="fa fa-arrows">&nbsp;</em></a></li>';
                $html .= '<li><a href="' . Mage::getUrl('mpanel/post/deleteInCategory', array('template' => $layout, 'type' => $type, 'block' => $child, 'category_id' => $category_id, 'product_id' => $product_id)) . '" onclick="return confirm(\'' . $this->__('Are you sure you would like to remove this block?') . '\')" title="' . $this->__('Delete') . '"><em class="fa fa-trash">&nbsp;</em></a></li>';
                $html .= '</ul></div>';
            } else {
                $html = '<div class="edit-panel child-panel"><ul>';
                $html .= '<li class="sort-handle"><a href="#" onclick="return false;" title="' . $this->__('Move') . '"><em class="fa fa-arrows">&nbsp;</em></a></li>';
                $html .= '<li><a href="' . Mage::getUrl('mpanel/post/deleteInCategory', array('template' => $layout, 'type' => $type, 'block' => $child, 'category_id' => $category_id)) . '" onclick="return confirm(\'' . $this->__('Are you sure you would like to remove this block?') . '\')" title="' . $this->__('Delete') . '"><em class="fa fa-trash">&nbsp;</em></a></li>';
                $html .= '</ul></div>';
            }
        } else {
            if ($product_id) {
                $html = '<div class="edit-panel child-panel"><ul>';
                $html .= '<li><a href="' . Mage::getUrl('mpanel/index/formInCategory', array('template' => $layout, 'block' => $block, 'id' => $child, 'type' => $type, 'category_id' => $category_id, 'product_id' => $product_id)) . '" class="popup-link" title="' . $this->__('Edit') . '"><em class="fa fa-edit">&nbsp;</em></a></li>';
                $html .= '<li class="sort-handle"><a href="#" onclick="return false;" title="' . $this->__('Move') . '"><em class="fa fa-arrows">&nbsp;</em></a></li>';
                $html .= '<li><a href="' . Mage::getUrl('mpanel/post/deleteInCategory', array('id' => $child, 'category_id' => $category_id, 'product_id' => $product_id)) . '" onclick="return confirm(\'' . $this->__('Are you sure you would like to remove this block?') . '\')" title="' . $this->__('Delete') . '"><em class="fa fa-trash">&nbsp;</em></a></li>';
                $html .= '</ul></div>';
            } else {
                $html = '<div class="edit-panel child-panel"><ul>';
                $html .= '<li><a href="' . Mage::getUrl('mpanel/index/formInCategory', array('template' => $layout, 'block' => $block, 'id' => $child, 'type' => $type, 'category_id' => $category_id)) . '" class="popup-link" title="' . $this->__('Edit') . '"><em class="fa fa-edit">&nbsp;</em></a></li>';
                $html .= '<li class="sort-handle"><a href="#" onclick="return false;" title="' . $this->__('Move') . '"><em class="fa fa-arrows">&nbsp;</em></a></li>';
                $html .= '<li><a href="' . Mage::getUrl('mpanel/post/deleteInCategory', array('id' => $child, 'category_id' => $category_id)) . '" onclick="return confirm(\'' . $this->__('Are you sure you would like to remove this block?') . '\')" title="' . $this->__('Delete') . '"><em class="fa fa-trash">&nbsp;</em></a></li>';
                $html .= '</ul></div>';
            }
        }

        return $html;
    }

    public function getEditChildInCms($layout, $block, $child, $type, $page_id) {
        if ($type == 'core') {
            $html = '<div class="edit-panel child-panel"><ul>';
            $html .= '<li class="sort-handle"><a href="#" onclick="return false;" title="' . $this->__('Move') . '"><em class="fa fa-arrows">&nbsp;</em></a></li>';
            $html .= '<li><a href="' . Mage::getUrl('mpanel/post/deleteInCms', array('template' => $layout, 'type' => $type, 'block' => $child, 'page_id' => $page_id)) . '" onclick="return confirm(\'' . $this->__('Are you sure you would like to remove this block?') . '\')" title="' . $this->__('Delete') . '"><em class="fa fa-trash">&nbsp;</em></a></li>';
            $html .= '</ul></div>';
        } else {
            $html = '<div class="edit-panel child-panel"><ul>';
            $html .= '<li><a href="' . Mage::getUrl('mpanel/index/formInCms', array('template' => $layout, 'block' => $block, 'id' => $child, 'type' => $type, 'page_id' => $page_id)) . '" class="popup-link" title="' . $this->__('Edit') . '"><em class="fa fa-edit">&nbsp;</em></a></li>';
            $html .= '<li class="sort-handle"><a href="#" onclick="return false;" title="' . $this->__('Move') . '"><em class="fa fa-arrows">&nbsp;</em></a></li>';
            $html .= '<li><a href="' . Mage::getUrl('mpanel/post/deleteInCms', array('id' => $child, 'page_id' => $page_id)) . '" onclick="return confirm(\'' . $this->__('Are you sure you would like to remove this block?') . '\')" title="' . $this->__('Delete') . '"><em class="fa fa-trash">&nbsp;</em></a></li>';
            $html .= '</ul></div>';
        }

        return $html;
    }
    
    public function getEditChildInCustomer($layout, $block, $child, $type) {
        if ($type == 'core') {
            $html = '<div class="edit-panel child-panel"><ul>';
            $html .= '<li class="sort-handle"><a href="#" onclick="return false;" title="' . $this->__('Move') . '"><em class="fa fa-arrows">&nbsp;</em></a></li>';
            $html .= '<li><a href="' . Mage::getUrl('mpanel/post/deleteInCustomer', array('template' => $layout, 'type' => $type, 'block' => $child)) . '" onclick="return confirm(\'' . $this->__('Are you sure you would like to remove this block?') . '\')" title="' . $this->__('Delete') . '"><em class="fa fa-trash">&nbsp;</em></a></li>';
            $html .= '</ul></div>';
        } else {
            $html = '<div class="edit-panel child-panel"><ul>';
            $html .= '<li><a href="' . Mage::getUrl('mpanel/index/formInCustomer', array('template' => $layout, 'block' => $block, 'id' => $child, 'type' => $type)) . '" class="popup-link" title="' . $this->__('Edit') . '"><em class="fa fa-edit">&nbsp;</em></a></li>';
            $html .= '<li class="sort-handle"><a href="#" onclick="return false;" title="' . $this->__('Move') . '"><em class="fa fa-arrows">&nbsp;</em></a></li>';
            $html .= '<li><a href="' . Mage::getUrl('mpanel/post/deleteInCustomer', array('id' => $child)) . '" onclick="return confirm(\'' . $this->__('Are you sure you would like to remove this block?') . '\')" title="' . $this->__('Delete') . '"><em class="fa fa-trash">&nbsp;</em></a></li>';
            $html .= '</ul></div>';
        }

        return $html;
    }

    public function renderHtmlContent($templateLayout, $blockName, $currentCategoryId, $key, $value, $isAdmin, $currentProductId) {
        $blocks = array(
            'categoryNavigation' => array(
                'block' => 'mpanel/navigation',
                'template' => 'mgs/mpanel/template/category-navigation.phtml'
            ),
            'subCategories' => array(
                'block' => 'catalog/navigation',
                'template' => 'catalog/navigation/left.phtml'
            ),
            'layeredNavigation' => array(
                'block' => 'catalog/layer_view',
                'template' => 'catalog/layer/view.phtml'
            ),
            'cartSidebar' => array(
                'block' => 'checkout/cart_sidebar',
                'template' => 'checkout/cart/sidebar.phtml'
            ),
            'compareSidebar' => array(
                'block' => 'catalog/product_compare_sidebar',
                'template' => 'catalog/product/compare/sidebar.phtml'
            ),
            'reorderSidebar' => array(
                'block' => 'sales/reorder_sidebar',
                'template' => 'sales/reorder/sidebar.phtml'
            ),
            'poll' => array(
                'block' => 'poll/activePoll',
                'poll_template' => array(
                    'poll' => 'poll/active.phtml',
                    'results' => 'poll/result.phtml'
                )
            ),
            'productViewed' => array(
                'block' => 'reports/product_viewed',
                'template' => 'reports/product_viewed.phtml'
            ),
            'wishlistSidebar' => array(
                'block' => 'wishlist/customer_sidebar',
                'template' => 'wishlist/sidebar.phtml'
            ),
            'tagsPopular' => array(
                'block' => 'tag/popular',
                'template' => 'tag/popular.phtml'
            ),
            'newsletter' => array(
                'block' => 'newsletter/subscribe',
                'template' => 'newsletter/subscribe.phtml'
            ),
            'productRelated' => array(
                'block' => 'catalog/product_list_related',
                'template' => 'catalog/product/list/related.phtml'
            )
        );
        foreach ($blocks as $block => $data) {
            if ($block == $key) {
                if ($key == 'subCategories') {
                    if ($this->isCategoryPage()) {
                        $html = '';
                        if ($isAdmin) {
                            $html .= '<div class="sort-item builder-container child-builder" id="' . $templateLayout . '_' . $blockName . '_' . $key . '">';
                            $html .= $this->getEditChildInCategory($templateLayout, $blockName, $key, 'core', $currentCategoryId, $currentProductId);
                        }
                        $html .= $this->getLayout()
                                ->createBlock($data['block'])
                                ->setTemplate($data['template'])
                                ->toHtml();
                        if ($isAdmin) {
                            $html .= '</div>';
                        }
                        echo $html;
                    }
                } else if ($key == 'layeredNavigation') {
                    if ($this->isCategoryPage()) {
                        $html = '';
                        if ($isAdmin) {
                            $html .= '<div class="sort-item builder-container child-builder" id="' . $templateLayout . '_' . $blockName . '_' . $key . '">';
                            $html .= $this->getEditChildInCategory($templateLayout, $blockName, $key, 'core', $currentCategoryId, $currentProductId);
                        }
                        $html .= $this->getLayout()
                                ->createBlock($data['block'])
                                ->setTemplate($data['template'])
                                ->toHtml();
                        if ($isAdmin) {
                            $html .= '</div>';
                        }
                        echo $html;
                    }
                } else if ($key == 'productRelated') {
                    if ($this->isProductPage()) {
                        $html = '';
                        if ($isAdmin) {
                            $html .= '<div class="sort-item builder-container child-builder" id="' . $templateLayout . '_' . $blockName . '_' . $key . '">';
                            $html .= $this->getEditChildInCategory($templateLayout, $blockName, $key, 'core', $currentCategoryId, $currentProductId);
                        }
                        $html .= $this->getLayout()
                                ->createBlock($data['block'])
                                ->setTemplate($data['template'])
                                ->toHtml();
                        if ($isAdmin) {
                            $html .= '</div>';
                        }
                        echo $html;
                    }
                } else if ($key == 'poll') {
                    $arr = explode('-', $value);
                    if (isset($arr[1])) {
                        $html = '';
                        if ($isAdmin) {
                            $html .= '<div class="sort-item builder-container child-builder" id="' . $templateLayout . '_' . $blockName . '_' . $key . '">';
                            $html .= $this->getEditChildInCategory($templateLayout, $blockName, $key, 'core', $currentCategoryId, $currentProductId);
                        }
                        $poll = $this->getLayout()->createBlock($data['block'])
                                ->setPollId($arr[1]);
                        foreach ($data['poll_template'] as $k => $v) {
                            $poll->setPollTemplate($v, $k);
                        }
                        $html .= $poll->toHtml();
                        if ($isAdmin) {
                            $html .= '</div>';
                        }
                        echo $html;
                    }
                } else {
                    $html = '';
                    if ($isAdmin) {
                        $html .= '<div class="sort-item builder-container child-builder" id="' . $templateLayout . '_' . $blockName . '_' . $key . '">';
                        $html .= $this->getEditChildInCategory($templateLayout, $blockName, $key, 'core', $currentCategoryId, $currentProductId);
                    }
                    $html .= $this->getLayout()
                            ->createBlock($data['block'])
                            ->setTemplate($data['template'])
                            ->toHtml();
                    if ($isAdmin) {
                        $html .= '</div>';
                    }
                    echo $html;
                }
            } else {
                if (strpos($key, 'promoBanner') !== false) {
                    $id = str_replace('promoBanner', '', $key);
                    $promo = Mage::getModel('promobanners/promobanners')->load($id);
                    if ($id && $promo->getId()) {
                        $html = '';
                        if ($isAdmin) {
                            $html .= '<div class="sort-item builder-container child-builder" id="' . $templateLayout . '_' . $blockName . '_' . $key . '">';
                            $html .= $this->getEditChildInCategory($templateLayout, $blockName, $key, 'core', $currentCategoryId, $currentProductId);
                        } else {
                            $html .= '<div class="block block-banner">';
                        }
                        $html .= $this->getLayout()
                                ->createBlock('promobanners/promobanners')
                                ->setBannerId($id)
                                ->setTemplate('mgs/promobanners/banner.phtml')
                                ->toHtml();
                        $html .= '</div>';
                        echo $html;
                    }
                    break;
                }
            }
        }
    }

    public function renderHtmlContentInCms($templateLayout, $blockName, $pageId, $key, $value, $isAdmin) {
        $blocks = array(
            'categoryNavigation' => array(
                'block' => 'mpanel/navigation',
                'template' => 'mgs/mpanel/template/category-navigation.phtml'
            ),
            'cartSidebar' => array(
                'block' => 'checkout/cart_sidebar',
                'template' => 'checkout/cart/sidebar.phtml'
            ),
            'compareSidebar' => array(
                'block' => 'catalog/product_compare_sidebar',
                'template' => 'catalog/product/compare/sidebar.phtml'
            ),
            'reorderSidebar' => array(
                'block' => 'sales/reorder_sidebar',
                'template' => 'sales/reorder/sidebar.phtml'
            ),
            'poll' => array(
                'block' => 'poll/activePoll',
                'poll_template' => array(
                    'poll' => 'poll/active.phtml',
                    'results' => 'poll/result.phtml'
                )
            ),
            'productViewed' => array(
                'block' => 'reports/product_viewed',
                'template' => 'reports/product_viewed.phtml'
            ),
            'wishlistSidebar' => array(
                'block' => 'wishlist/customer_sidebar',
                'template' => 'wishlist/sidebar.phtml'
            ),
            'tagsPopular' => array(
                'block' => 'tag/popular',
                'template' => 'tag/popular.phtml'
            ),
            'newsletter' => array(
                'block' => 'newsletter/subscribe',
                'template' => 'newsletter/subscribe.phtml'
            )
        );
        foreach ($blocks as $block => $data) {
            if ($block == $key) {
                if ($key == 'poll') {
                    $arr = explode('-', $value);
                    if (isset($arr[1])) {
                        $html = '';
                        if ($isAdmin) {
                            $html .= '<div class="sort-item builder-container child-builder" id="' . $templateLayout . '_' . $blockName . '_' . $key . '">';
                            $html .= $this->getEditChildInCms($templateLayout, $blockName, $key, 'core', $pageId);
                        }
                        $poll = $this->getLayout()->createBlock($data['block'])
                                ->setPollId($arr[1]);
                        foreach ($data['poll_template'] as $k => $v) {
                            $poll->setPollTemplate($v, $k);
                        }
                        $html .= $poll->toHtml();
                        if ($isAdmin) {
                            $html .= '</div>';
                        }
                        echo $html;
                    }
                } else {
                    $html = '';
                    if ($isAdmin) {
                        $html .= '<div class="sort-item builder-container child-builder" id="' . $templateLayout . '_' . $blockName . '_' . $key . '">';
                        $html .= $this->getEditChildInCms($templateLayout, $blockName, $key, 'core', $pageId);
                    }
                    $html .= $this->getLayout()
                            ->createBlock($data['block'])
                            ->setTemplate($data['template'])
                            ->toHtml();
                    if ($isAdmin) {
                        $html .= '</div>';
                    }
                    echo $html;
                }
            } else {
                if (strpos($key, 'promoBanner') !== false) {
                    $id = str_replace('promoBanner', '', $key);
                    $promo = Mage::getModel('promobanners/promobanners')->load($id);
                    if ($id && $promo->getId()) {
                        $html = '';
                        if ($isAdmin) {
                            $html .= '<div class="sort-item builder-container child-builder" id="' . $templateLayout . '_' . $blockName . '_' . $key . '">';
                            $html .= $this->getEditChildInCms($templateLayout, $blockName, $key, 'core', $pageId);
                        } else {
                            $html .= '<div class="block block-banner">';
                        }
                        $html .= $this->getLayout()
                                ->createBlock('promobanners/promobanners')
                                ->setBannerId($id)
                                ->setTemplate('mgs/promobanners/banner.phtml')
                                ->toHtml();
                        $html .= '</div>';
                        echo $html;
                    }
                    break;
                }
            }
        }
    }
    
    public function renderHtmlContentInCustomer($templateLayout, $blockName, $key, $value, $isAdmin) {
        $blocks = array(
            'categoryNavigation' => array(
                'block' => 'mpanel/navigation',
                'template' => 'mgs/mpanel/template/category-navigation.phtml'
            ),
            'cartSidebar' => array(
                'block' => 'checkout/cart_sidebar',
                'template' => 'checkout/cart/sidebar.phtml'
            ),
            'compareSidebar' => array(
                'block' => 'catalog/product_compare_sidebar',
                'template' => 'catalog/product/compare/sidebar.phtml'
            ),
            'reorderSidebar' => array(
                'block' => 'sales/reorder_sidebar',
                'template' => 'sales/reorder/sidebar.phtml'
            ),
            'poll' => array(
                'block' => 'poll/activePoll',
                'poll_template' => array(
                    'poll' => 'poll/active.phtml',
                    'results' => 'poll/result.phtml'
                )
            ),
            'productViewed' => array(
                'block' => 'reports/product_viewed',
                'template' => 'reports/product_viewed.phtml'
            ),
            'wishlistSidebar' => array(
                'block' => 'wishlist/customer_sidebar',
                'template' => 'wishlist/sidebar.phtml'
            ),
            'tagsPopular' => array(
                'block' => 'tag/popular',
                'template' => 'tag/popular.phtml'
            ),
            'newsletter' => array(
                'block' => 'newsletter/subscribe',
                'template' => 'newsletter/subscribe.phtml'
            )
        );
        foreach ($blocks as $block => $data) {
            if ($block == $key) {
                if ($key == 'poll') { // Block poll
                    $arr = explode('-', $value);
                    if (isset($arr[1])) {
                        $html = '';
                        if ($isAdmin) {
                            $html .= '<div class="sort-item builder-container child-builder" id="' . $templateLayout . '_' . $blockName . '_' . $key . '">';
                            $html .= $this->getEditChildInCustomer($templateLayout, $blockName, $key, 'core');
                        }
                        $poll = $this->getLayout()->createBlock($data['block'])
                                ->setPollId($arr[1]);
                        foreach ($data['poll_template'] as $k => $v) {
                            $poll->setPollTemplate($v, $k);
                        }
                        $html .= $poll->toHtml();
                        if ($isAdmin) {
                            $html .= '</div>';
                        }
                        echo $html;
                    }
                } else { // General block
                    $html = '';
                    if ($isAdmin) {
                        $html .= '<div class="sort-item builder-container child-builder" id="' . $templateLayout . '_' . $blockName . '_' . $key . '">';
                        $html .= $this->getEditChildInCustomer($templateLayout, $blockName, $key, 'core');
                    }
                    $html .= $this->getLayout()
                            ->createBlock($data['block'])
                            ->setTemplate($data['template'])
                            ->toHtml();
                    if ($isAdmin) {
                        $html .= '</div>';
                    }
                    echo $html;
                }
            } else { // if block banner
                if (strpos($key, 'promoBanner') !== false) {
                    $id = str_replace('promoBanner', '', $key);
                    $promo = Mage::getModel('promobanners/promobanners')->load($id);
                    if ($id && $promo->getId()) {
                        $html = '';
                        if ($isAdmin) {
                            $html .= '<div class="sort-item builder-container child-builder" id="' . $templateLayout . '_' . $blockName . '_' . $key . '">';
                            $html .= $this->getEditChildInCustomer($templateLayout, $blockName, $key, 'core');
                        } else {
                            $html .= '<div class="block block-banner">';
                        }
                        $html .= $this->getLayout()
                                ->createBlock('promobanners/promobanners')
                                ->setBannerId($id)
                                ->setTemplate('mgs/promobanners/banner.phtml')
                                ->toHtml();
                        $html .= '</div>';
                        echo $html;
                    }
                    break;
                }
            }
        }
    }

    public function getEditTwitterConfig() {
        $html = '<div class="edit-panel child-panel"><ul>';
        $html .= '<li><a href="' . Mage::getUrl('mpanel/edit/twitter') . '" class="popup-link" title="' . $this->__('Edit') . '"><em class="fa fa-edit">&nbsp;</em></a></li>';
        $html .= '</ul></div>';

        return $html;
    }

    // convert col from number of products for responsive
    public function convertColRow($numberOfProduct) {
        switch ($numberOfProduct) {
            case 1:
                $col = 12;
                break;
            case 2:
                $col = 6;
                break;
            case 3:
                $col = 4;
                break;
            case 4:
                $col = 3;
                break;
            case 6:
                $col = 2;
                break;
            default:
                $col = 3;
                break;
        }
        return $col;
    }

    // change title of product tabs by type of the tab
    public function changeTabTitle($type) {

        $title = '';
        switch ($type) {
            // New Products
            case 'new_products':
                $title = Mage::helper('mpanel')->__('New Products');
                break;

            // Best Selling Products
            case 'hot_products':
                $title = Mage::helper('mpanel')->__('Best Selling Products');
                break;

            // Featured Products
            case 'featured_products':
                $title = Mage::helper('mpanel')->__('Featured Products');
                break;
        }

        return $title;
    }

    //get theme color
    public function getThemeColor() {
        return array(
            array('value' => 'blue', 'label' => Mage::helper('mpanel')->__('Blue')),
            array('value' => 'light-blue', 'label' => Mage::helper('mpanel')->__('Light Blue')),
            array('value' => 'green', 'label' => Mage::helper('mpanel')->__('Green')),
            array('value' => 'light-green', 'label' => Mage::helper('mpanel')->__('Light Green')),
            array('value' => 'orange', 'label' => Mage::helper('mpanel')->__('Orange')),
            array('value' => 'gold', 'label' => Mage::helper('mpanel')->__('Gold')),
            array('value' => 'purple', 'label' => Mage::helper('mpanel')->__('Purple')),
            array('value' => 'red', 'label' => Mage::helper('mpanel')->__('Red')),
            array('value' => 'tael', 'label' => Mage::helper('mpanel')->__('Tael')),
            array('value' => 'violet', 'label' => Mage::helper('mpanel')->__('Violet')),
            array('value' => 'yellow', 'label' => Mage::helper('mpanel')->__('Yellow')),
            array('value' => 'pink', 'label' => Mage::helper('mpanel')->__('Pink')),            
        );
    }
	
	//get background pattern
    public function getBackgroundPattern() {
        return array(
			array('value' => '', 'label' => ''),
			array('value' => 'gray_jean', 'label' => Mage::helper('mpanel')->__('Gray jean')),
			array('value' => 'linedpaper', 'label' => Mage::helper('mpanel')->__('Linedpaper')),
			array('value' => 'az_subtle', 'label' => Mage::helper('mpanel')->__('Az subtle')),
			array('value' => 'blizzard', 'label' => Mage::helper('mpanel')->__('Blizzard')),
			array('value' => 'denim', 'label' => Mage::helper('mpanel')->__('Denim')),
			array('value' => 'fancy_deboss', 'label' => Mage::helper('mpanel')->__('Fancy deboss')),
			array('value' => 'honey_im_subtle', 'label' => Mage::helper('mpanel')->__('Honey im subtle')),
			array('value' => 'linen', 'label' => Mage::helper('mpanel')->__('Linen')),
			array('value' => 'pw_maze_white', 'label' => Mage::helper('mpanel')->__('Pw maze white')),
			array('value' => 'skin_side_up', 'label' => Mage::helper('mpanel')->__('Skin side up')),
			array('value' => 'stitched_wool', 'label' => Mage::helper('mpanel')->__('Stitched wool')),
			array('value' => 'straws', 'label' => Mage::helper('mpanel')->__('Straws')),
			array('value' => 'subtle_grunge', 'label' => Mage::helper('mpanel')->__('Subtle grunge')),
			array('value' => 'textured_stripes', 'label' => Mage::helper('mpanel')->__('Textured stripes')),
			array('value' => 'wild_oliva', 'label' => Mage::helper('mpanel')->__('Wild oliva')),
			array('value' => 'worn_dots', 'label' => Mage::helper('mpanel')->__('Worn dots')),
			array('value' => 'bright_squares', 'label' => Mage::helper('mpanel')->__('Bright squares')),
			array('value' => 'random_grey_variations', 'label' => Mage::helper('mpanel')->__('Random grey variations')),
        );
    }

    // get include fonts
    public function getFonts() {
        return array(
            array('css-name' => 'Lato', 'font-name' => Mage::helper('mpanel')->__('Lato')),
            array('css-name' => 'Open+Sans', 'font-name' => Mage::helper('mpanel')->__('Open Sans')),
            array('css-name' => 'Roboto', 'font-name' => Mage::helper('mpanel')->__('Roboto')),
            array('css-name' => 'Roboto Slab', 'font-name' => Mage::helper('mpanel')->__('Roboto Slab')),
            array('css-name' => 'Oswald', 'font-name' => Mage::helper('mpanel')->__('Oswald')),
            array('css-name' => 'Source+Sans+Pro', 'font-name' => Mage::helper('mpanel')->__('Source Sans Pro')),
            array('css-name' => 'PT+Sans', 'font-name' => Mage::helper('mpanel')->__('PT Sans')),
            array('css-name' => 'PT+Serif', 'font-name' => Mage::helper('mpanel')->__('PT Serif')),
            array('css-name' => 'Droid+Serif', 'font-name' => Mage::helper('mpanel')->__('Droid Serif')),
            array('css-name' => 'Josefin+Slab', 'font-name' => Mage::helper('mpanel')->__('Josefin Slab')),
            array('css-name' => 'Montserrat', 'font-name' => Mage::helper('mpanel')->__('Montserrat')),
            array('css-name' => 'Ubuntu', 'font-name' => Mage::helper('mpanel')->__('Ubuntu')),
            array('css-name' => 'Titillium+Web', 'font-name' => Mage::helper('mpanel')->__('Titillium Web')),
            array('css-name' => 'Noto+Sans', 'font-name' => Mage::helper('mpanel')->__('Noto Sans')),
            array('css-name' => 'Lora', 'font-name' => Mage::helper('mpanel')->__('Lora')),
            array('css-name' => 'Playfair+Display', 'font-name' => Mage::helper('mpanel')->__('Playfair Display')),
            array('css-name' => 'Bree+Serif', 'font-name' => Mage::helper('mpanel')->__('Bree Serif')),
            array('css-name' => 'Vollkorn', 'font-name' => Mage::helper('mpanel')->__('Vollkorn')),
            array('css-name' => 'Alegreya', 'font-name' => Mage::helper('mpanel')->__('Alegreya')),
            array('css-name' => 'Noto+Serif', 'font-name' => Mage::helper('mpanel')->__('Noto Serif')),
        );
    }

    // get all theme settings
    public function getThemeSettings() {
        $setting = array(
            'enabled' => Mage::getStoreConfig('mpanel/general/enabled'),
            'token' => Mage::getStoreConfig('mpanel/twitter/token'),
            'token_secret' => Mage::getStoreConfig('mpanel/twitter/token_secret'),
            'consumer_key' => Mage::getStoreConfig('mpanel/twitter/consumer_key'),
            'consumer_secret' => Mage::getStoreConfig('mpanel/twitter/consumer_secret'),
            'twitter_title' => Mage::getStoreConfig('mpanel/twitter/twitter_title'),
            'twitter_user' => Mage::getStoreConfig('mpanel/twitter/twitter_user'),
            'twitter_count' => Mage::getStoreConfig('mpanel/twitter/twitter_count'),
            'truncate' => Mage::getStoreConfig('mpanel/twitter/truncate'),
            'enabled_gmap' => Mage::getStoreConfig('mpanel/contact/enabled'),
            'address' => Mage::getStoreConfig('mpanel/contact/address'),
            'html' => Mage::getStoreConfig('mpanel/contact/html'),
            'image' => Mage::getStoreConfig('mpanel/contact/image'),
            'sku' => Mage::getStoreConfig('mpanel/product_details/sku'),
            'email_friend' => Mage::getStoreConfig('mpanel/product_details/email_friend'),
            'reviews_summary' => Mage::getStoreConfig('mpanel/product_details/reviews_summary'),
            'alert_urls' => Mage::getStoreConfig('mpanel/product_details/alert_urls'),
            'wishlist_compare' => Mage::getStoreConfig('mpanel/product_details/wishlist_compare'),
            'short_description' => Mage::getStoreConfig('mpanel/product_details/short_description'),
            'upsell_products' => Mage::getStoreConfig('mpanel/product_details/upsell_products'),
            'page_width' => Mage::getStoreConfig('mgs_theme/general/page_width'),
            'right_to_left' => Mage::getStoreConfig('mgs_theme/general/right_to_left'),
            'layout' => Mage::getStoreConfig('mgs_theme/general/layout'),
            'layout_style' => Mage::getStoreConfig('mgs_theme/general/layout_style'),
            'logo' => Mage::getStoreConfig('mgs_theme/general/logo'),
            'sticky_menu' => Mage::getStoreConfig('mgs_theme/general/sticky_menu'),
            'back_to_top' => Mage::getStoreConfig('mgs_theme/general/back_to_top'),
            'preloader' => Mage::getStoreConfig('mgs_theme/general/preloader'),
            'snippets' => Mage::getStoreConfig('mgs_theme/general/snippets'),
            'custom_css' => Mage::getStoreConfig('mgs_theme/general/custom_css'),
            'bg_color' => Mage::getStoreConfig('mgs_theme/background/bg_color'),
            'bg_upload' => Mage::getStoreConfig('mgs_theme/background/bg_upload'),
            'bg_image' => Mage::getStoreConfig('mgs_theme/background/bg_image'),
            'bg_repeat' => Mage::getStoreConfig('mgs_theme/background/bg_repeat'),
            'bg_position_x' => Mage::getStoreConfig('mgs_theme/background/bg_position_x'),
            'bg_position_y' => Mage::getStoreConfig('mgs_theme/background/bg_position_y'),
            'theme_color' => Mage::getStoreConfig('mgs_theme/color/theme_color'),
            'font' => Mage::getStoreConfig('mgs_theme/fonts/font'),
            'h1' => Mage::getStoreConfig('mgs_theme/fonts/h1'),
            'h2' => Mage::getStoreConfig('mgs_theme/fonts/h2'),
            'h3' => Mage::getStoreConfig('mgs_theme/fonts/h3'),
            'h4' => Mage::getStoreConfig('mgs_theme/fonts/h4'),
            'h5' => Mage::getStoreConfig('mgs_theme/fonts/h5'),
            'h6' => Mage::getStoreConfig('mgs_theme/fonts/h6'),
            'price' => Mage::getStoreConfig('mgs_theme/fonts/price'),
            'menu' => Mage::getStoreConfig('mgs_theme/fonts/menu'),
            'megamenu' => Mage::getStoreConfig('megamenu/general/enabled'),
            'ajaxcart' => Mage::getStoreConfig('ajaxcart/general/active'),
            'quickview' => Mage::getStoreConfig('quickview/general/active'),
            'deals' => Mage::getStoreConfig('deals/general/enabled'),
            'oscheckout' => Mage::getStoreConfig('oscheckout/general/enabled'),
            'catalog_layout' => Mage::getStoreConfig('mpanel/catalog/layout'),
            'product_layout' => Mage::getStoreConfig('mpanel/catalog/product_layout'),
            'product_per_row' => Mage::getStoreConfig('mpanel/catalog/product_per_row'),
            'catalog_featured' => Mage::getStoreConfig('mpanel/catalog/featured'),
            'catalog_hot' => Mage::getStoreConfig('mpanel/catalog/hot'),
            'catalog_brands' => Mage::getStoreConfig('mpanel/catalog/brands'),
            'picture_ratio' => Mage::getStoreConfig('mpanel/catalog/picture_ratio'),
            'new_label' => Mage::getStoreConfig('mpanel/catalog/new_label'),
            'sale_label' => Mage::getStoreConfig('mpanel/catalog/sale_label'),
            'price_slider' => Mage::getStoreConfig('mpanel/catalog/price_slider'),
            'more_view' => Mage::getStoreConfig('mpanel/catalog/more_view'),
            'preload' => Mage::getStoreConfig('mpanel/catalog/preload'),
            'wishlist_button' => Mage::getStoreConfig('mpanel/catalog/wishlist_button'),
            'compare_button' => Mage::getStoreConfig('mpanel/catalog/compare_button'),
        );
        return $setting;
    }
	
	public function getBlogSettings(){
		$setting = array(
            'enabled' => Mage::getStoreConfig('blog/blog/enabled'),
            'title' => Mage::getStoreConfig('blog/blog/title'),
            'keywords' => Mage::getStoreConfig('blog/blog/keywords'),
            'description' => Mage::getStoreConfig('blog/blog/description'),
            'layout' => Mage::getStoreConfig('blog/blog/layout'),
            'dateformat' => Mage::getStoreConfig('blog/blog/dateformat'),
            'blogcrumbs' => Mage::getStoreConfig('blog/blog/blogcrumbs'),
            'readmore' => Mage::getStoreConfig('blog/blog/readmore'),
            'useshortcontent' => Mage::getStoreConfig('blog/blog/useshortcontent'),
            'parse_cms' => Mage::getStoreConfig('blog/blog/parse_cms'),
            'perpage' => Mage::getStoreConfig('blog/blog/perpage'),
            'bookmarkspost' => Mage::getStoreConfig('blog/blog/bookmarkspost'),
            'bookmarkslist' => Mage::getStoreConfig('blog/blog/bookmarkslist'),
            'categories_urls' => Mage::getStoreConfig('blog/blog/categories_urls'),
            'sorter' => Mage::getStoreConfig('blog/blog/sorter'),
            'left' => Mage::getStoreConfig('blog/menu/left'),
            'right' => Mage::getStoreConfig('blog/menu/right'),
            'footer' => Mage::getStoreConfig('blog/menu/footer'),
            'top' => Mage::getStoreConfig('blog/menu/top'),
            'category' => Mage::getStoreConfig('blog/menu/category'),
            'tagcloud_size' => Mage::getStoreConfig('blog/menu/tagcloud_size'),
            'recent' => Mage::getStoreConfig('blog/menu/recent'),
            'comments_enabled' => Mage::getStoreConfig('blog/comments/enabled'),
            'login' => Mage::getStoreConfig('blog/comments/login'),
            'approval' => Mage::getStoreConfig('blog/comments/approval'),
            'loginauto' => Mage::getStoreConfig('blog/comments/loginauto'),
            'recipient_email' => Mage::getStoreConfig('blog/comments/recipient_email'),
            'sender_email_identity' => Mage::getStoreConfig('blog/comments/sender_email_identity'),
            'email_template' => Mage::getStoreConfig('blog/comments/email_template'),
            'page_count' => Mage::getStoreConfig('blog/comments/page_count')
        );
        return $setting;
	}
	
	// get one step checkout settings
	public function getCheckoutSettings(){
		$setting = array(
            'checkout_title' => Mage::getStoreConfig('oscheckout/general/checkout_title'),
            'checkout_link' => Mage::getStoreConfig('oscheckout/general/checkout_link'),
            'guest_checkout' => Mage::getStoreConfig('oscheckout/registration/guest_checkout'),
            'company' => Mage::getStoreConfig('oscheckout/display/company'),
            'telephone' => Mage::getStoreConfig('oscheckout/display/telephone'),
            'fax' => Mage::getStoreConfig('oscheckout/display/fax'),
            'address' => Mage::getStoreConfig('oscheckout/display/address'),
            'discount' => Mage::getStoreConfig('oscheckout/display/discount'),
            'terms_enabled' => Mage::getStoreConfig('oscheckout/terms/enabled'),
            'terms_title' => Mage::getStoreConfig('oscheckout/terms/title'),
            'terms_label' => Mage::getStoreConfig('oscheckout/terms/label'),
            'terms_contents' => Mage::getStoreConfig('oscheckout/terms/contents'),
            'comment_enabled' => Mage::getStoreConfig('oscheckout/comment/enabled'),
            'comment_title' => Mage::getStoreConfig('oscheckout/comment/title'),
            'show_grid' => Mage::getStoreConfig('oscheckout/comment/show_grid'),
        );
        return $setting;
	}
	
	// get product questions settings
	public function getFaqsSettings(){
		$setting = array(
            'who_ask' => Mage::getStoreConfig('productquestions/general/who_ask'),
            'who_answer' => Mage::getStoreConfig('productquestions/general/who_answer'),
            'automatic' => Mage::getStoreConfig('productquestions/general/automatic'),
            'rate' => Mage::getStoreConfig('productquestions/general/rate'),
            'who_rate' => Mage::getStoreConfig('productquestions/general/who_rate'),
            'visibility' => Mage::getStoreConfig('productquestions/general/visibility'),
            'active' => Mage::getStoreConfig('productquestions/question_email/active'),
            'notification' => Mage::getStoreConfig('productquestions/question_email/notification'),
            'admin_email' => Mage::getStoreConfig('productquestions/question_email/admin_email'),
            'email_sender' => Mage::getStoreConfig('productquestions/question_email/email_sender'),
            'admin_question_template' => Mage::getStoreConfig('productquestions/question_email/admin_question_template'),
            'admin_answer_template' => Mage::getStoreConfig('productquestions/question_email/admin_answer_template'),
            'question_template' => Mage::getStoreConfig('productquestions/question_email/question_template'),
            'answer_template' => Mage::getStoreConfig('productquestions/question_email/answer_template'),
            'title' => Mage::getStoreConfig('productquestions/faqs_page/title'),
            'url_key' => Mage::getStoreConfig('productquestions/faqs_page/url_key'),
            'faqs_link_to_toplink' => Mage::getStoreConfig('productquestions/faqs_page/faqs_link_to_toplink'),
            'meta_keywords' => Mage::getStoreConfig('productquestions/faqs_page/meta_keywords'),
            'meta_description' => Mage::getStoreConfig('productquestions/faqs_page/meta_description'),
            'accordition' => Mage::getStoreConfig('productquestions/faqs_page/accordition'),
            'sort_by' => Mage::getStoreConfig('productquestions/faqs_page/sort_by'),
            'block_active' => Mage::getStoreConfig('productquestions/faqs_block/active'),
            'block_title' => Mage::getStoreConfig('productquestions/faqs_block/block_title'),
            'number_of_topics' => Mage::getStoreConfig('productquestions/faqs_block/number_of_topics'),
            'enabled' => Mage::getStoreConfig('productquestions/recaptcha/enabled'),
            'public_key' => Mage::getStoreConfig('productquestions/recaptcha/public_key'),
            'private_key' => Mage::getStoreConfig('productquestions/recaptcha/private_key'),
            'theme' => Mage::getStoreConfig('productquestions/recaptcha/theme'),
            'lang' => Mage::getStoreConfig('productquestions/recaptcha/lang'),
        );
        return $setting;
	}

    public function getFileByType($type){
		$theme = '';
		
		if(Mage::app()->getStore()->isAdmin()){
			if(Mage::app()->getRequest()->getParam('store') || Mage::app()->getRequest()->getParam('website')){
				if($storeCode = Mage::app()->getRequest()->getParam('store')){
					$store = Mage::getModel("core/store")->load($storeCode);
					$storeId = $store->getId();
					$theme = Mage::getStoreConfig('design/theme/default', $storeId);
				}
				else{
					if($websiteCode = Mage::app()->getRequest()->getParam('website')){
						$website = Mage::getModel("core/website")->load($websiteCode);
						$theme = Mage::app()->getWebsite($website)->getConfig('design/theme/default');
					}
				}
			}
			else{
				$theme = Mage::app()->getWebsite(0)->getConfig('design/theme/default');
			}
		}
		else{
			$storeId = Mage::app()->getStore()->getId();
			$theme = Mage::getStoreConfig('design/theme/default', $storeId);
		}
		
		$result = array();
		
		$dir = Mage::getBaseDir() . '/skin/frontend/mgstheme/'.$theme.'/asset/'.$type.'/';
		
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				$i=0;
				while (($file = readdir($dh)) !== false) {
					$file_parts = pathinfo($dir.$file);
					if(isset($file_parts['extension']) && $file_parts['extension']=='jpg'){
						$i++;
						$fileName = str_replace('.jpg','',$file);
						$result[] = array('value' => $fileName, 'label' => Mage::helper('mpanel')->__('Version %s', $i));
					}
				}
				closedir($dh);
			}
		}
		
        return $result;
	}

    // get header versions for config
    public function getHeaderVersion() {
		return $this->getFileByType('headers');
    }

    // get footer versions for config
    public function getFooterVersion() {
        return $this->getFileByType('footers');
    }

    // get first image from another content
    public function getFirstImage($content, $url) {
        $html = '';

        $helper = Mage::helper('cms');
        $processor = $helper->getPageTemplateProcessor();
        $content = $processor->filter($content);

        $first_img = '';
        $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches);

        if (isset($matches[1][0])) {
            $first_img = $matches[1][0];
            $html = '<div class="blog-image entry"><a href="'.$url.'"><img class="img-responsive" alt="" src="' . $first_img . '"/></a></div>';
        }
        return $html;
    }

    public function getPageObject() {
        $result = array();
        if (Mage::app()->getRequest()->getModuleName() == 'deals') {
            $result = array(
                'type' => 'store_config',
                'id' => 'deals',
                'page_title' => Mage::getStoreConfig('deals/deals_page/title'),
                'meta_keywords' => Mage::getStoreConfig('deals/deals_page/meta_keyword'),
                'meta_description' => Mage::getStoreConfig('deals/deals_page/meta_description'),
            );
        } else {
            if (!$this->isCatalogPage()) {
                $cmsPageUrlKey = Mage::getSingleton('cms/page')->getIdentifier();
                $page = Mage::getBlockSingleton('cms/page')->getPage();
                $result = array(
                    'type' => 'page_id',
                    'id' => $page->getId(),
                    'page_title' => $page->getTitle(),
                    'meta_keywords' => $page->getMetaKeywords(),
                    'meta_description' => $page->getMetaDescription(),
                );
            } else {
                if ($this->isCategoryPage()) {
                    $category = Mage::registry('current_category');
                    $result = array(
                        'type' => 'category_id',
                        'id' => $category->getId(),
                        'page_title' => $category->getMetaTitle(),
                        'meta_keywords' => $category->getMetaKeywords(),
                        'meta_description' => $category->getMetaDescription()
                    );
                } else {
                    $product = Mage::registry('current_product');
                    $result = array(
                        'type' => 'product_id',
                        'id' => $product->getId(),
                        'page_title' => $product->getMetaTitle(),
                        'meta_keywords' => $product->getMetaKeyword(),
                        'meta_description' => $product->getMetaDescription()
                    );
                }
            }
        }
        return $result;
    }

    public function getBlockIdByIndentifier($indetifier) {
        $storeId = Mage::app()->getStore()->getId();

        $collection = Mage::getModel('cms/block')->getCollection();
        $storeTable = Mage::getSingleton('core/resource')->getTableName('cms_block_store');
        $collection->getSelect()
                ->joinLeft(array('store' => $storeTable), 'main_table.block_id =store.block_id', array('store.store_id'))
                ->where('identifier="' . $indetifier . '"')
                ->where('store_id IN (?)', array(0, $storeId))
                ->order('store_id DESC');
        return $collection->getFirstItem()->getId();
    }

    public function convertRatioToSize() {
        $ratio = Mage::getStoreConfig('mpanel/catalog/picture_ratio');

        $result = array();
        switch ($ratio) {
            // 1/1 Square
            case 1:
                $result = array('width' => 600, 'height' => 600);
                break;
            // 1/2 Portrait
            case 2:
                $result = array('width' => 600, 'height' => 1200);
                break;
            // 2/3 Portrait
            case 3:
                $result = array('width' => 600, 'height' => 900);
                break;
            // 3/4 Portrait
            case 4:
                $result = array('width' => 600, 'height' => 800);
                break;
            // 2/1 Landscape
            case 5:
                $result = array('width' => 600, 'height' => 300);
                break;
            // 3/2 Landscape
            case 6:
                $result = array('width' => 600, 'height' => 400);
                break;
            // 4/3 Landscape
            case 7:
                $result = array('width' => 600, 'height' => 450);
                break;
        }

        return $result;
    }
	
	public function convertRatioToMinSize() {
        $ratio = Mage::getStoreConfig('mpanel/catalog/picture_ratio');

        $result = array();
        switch ($ratio) {
            // 1/1 Square
            case 1:
                $result = array('width' => 120, 'height' => 120);
                break;
            // 1/2 Portrait
            case 2:
                $result = array('width' => 120, 'height' => 240);
                break;
            // 2/3 Portrait
            case 3:
                $result = array('width' => 120, 'height' => 180);
                break;
            // 3/4 Portrait
            case 4:
                $result = array('width' => 120, 'height' => 160);
                break;
            // 2/1 Landscape
            case 5:
                $result = array('width' => 120, 'height' => 60);
                break;
            // 3/2 Landscape
            case 6:
                $result = array('width' => 120, 'height' => 80);
                break;
            // 4/3 Landscape
            case 7:
                $result = array('width' => 120, 'height' => 90);
                break;
        }

        return $result;
    }

    // get product label html
    public function getProductLabel($product) {
        $newLabel = Mage::getStoreConfig('mpanel/catalog/new_label');
        $saleLabel = Mage::getStoreConfig('mpanel/catalog/sale_label');

        $now = date("Y-m-d H:m:s");
        $newFromDate = $product->getNewsFromDate();
        $newToDate = $product->getNewsToDate();

        $html = '';

        if (!(empty($newToDate) && empty($newFromDate)) && ($newFromDate < $now || empty($newFromDate)) && ($newToDate > $now || empty($newToDate)) && ($newLabel != '')) {
            $html.='<div class="product-label new-label"><span class="new">' . $newLabel . '</span></div>';
        }
		
		$specialPrice = number_format($product->getFinalPrice(), 2);
		$regularPrice = number_format($product->getPrice(), 2);
		if(($specialPrice != $regularPrice) && ($saleLabel!='')){
            $html.='<div class="product-label sale-label"><span class="sale">'.$saleLabel.'</span></div>';
		}
		
		return $html;
	}
	
	public function getExistCmsStatic($type){
		$storeId = Mage::app()->getStore()->getId();
		
		$collection = Mage::getModel('cms/block')->getCollection();
		$storeTable = Mage::getSingleton('core/resource')->getTableName('cms_block_store');
		
		$childCollection =Mage::getModel('mpanel/childs')->getCollection()
			->addFieldToSelect('static_block_id');
		
		if($type!='NOT IN'){
			$childCollection->addFieldToFilter('type', 'static');
		}
		$childCollection->getSelect()->distinct(true);
		
		$arrExist = array();
		if(count($childCollection)>0){
			foreach($childCollection as $_child){
				$arrExist[] = $_child->getStaticBlockId();
			}
		}
		
		if(count($arrExist)>0){
			$collection->getSelect()
				->joinLeft(array('store'=>$storeTable), 'main_table.block_id =store.block_id', array('store.store_id'))
				->where('store.store_id IN (?)',array(0,$storeId))
				->where('main_table.block_id '.$type.' (?)', $arrExist)
				->order('store.store_id DESC');
		}
		return $collection;
	}
	
	public function getCol(){
		$perrow = Mage::getStoreConfig('mpanel/catalog/product_per_row');
		
		switch ($perrow) {
			case 2:
				return '6';
				break;
			case 3:
				return '4';
				break;
			case 4:
				return '3';
				break;
			case 5:
				return 'custom-5';
				break;
			case 6:
				return '2';
				break;
			case 7:
				return 'custom-7';
				break;
			case 8:
				return 'custom-8';
				break;
		}
		
	}
	
	// get all action of my account page
	public function getMyAccountActionName(){
		return array(
			'customer_account_index', 
			'customer_account_edit', 
			'customer_address_index', 
			'customer_address_form', 
			'sales_order_history',
			'sales_order_view',
			'sales_billing_agreement_index',
			'sales_recurring_profile_index',
			'review_customer_index',
			'review_customer_view',
			'tag_customer_index',
			'wishlist_index_index',
			'oauth_customer_token_index',
			'newsletter_manage_index',
			'downloadable_customer_products',
		);
	}
	
	public function getAnimationClass(){
		return array(
			array('label'=>$this->__('Choose Animation Effect'),'value'=>''),
			array('label'=>'bounce','value'=>'bounce'),
			array('label'=>'flash','value'=>'flash'),
			array('label'=>'pulse','value'=>'pulse'),
			array('label'=>'rubberBand','value'=>'rubberBand'),
			array('label'=>'shake','value'=>'shake'),
			array('label'=>'swing','value'=>'swing'),
			array('label'=>'tada','value'=>'tada'),
			array('label'=>'wobble','value'=>'wobble'),
			array('label'=>'bounceIn','value'=>'bounceIn'),
			// array('label'=>'bounceInDown','value'=>'bounceInDown'),
			// array('label'=>'bounceInLeft','value'=>'bounceInLeft'),
			// array('label'=>'bounceInRight','value'=>'bounceInRight'),
			// array('label'=>'bounceInUp','value'=>'bounceInUp'),
			array('label'=>'fadeIn','value'=>'fadeIn'),
			array('label'=>'fadeInDown','value'=>'fadeInDown'),
			array('label'=>'fadeInDownBig','value'=>'fadeInDownBig'),
			array('label'=>'fadeInLeft','value'=>'fadeInLeft'),
			array('label'=>'fadeInLeftBig','value'=>'fadeInLeftBig'),
			array('label'=>'fadeInRight','value'=>'fadeInRight'),
			array('label'=>'fadeInRightBig','value'=>'fadeInRightBig'),
			array('label'=>'fadeInUp','value'=>'fadeInUp'),
			array('label'=>'fadeInUpBig','value'=>'fadeInUpBig'),
			array('label'=>'flip','value'=>'flip'),
			array('label'=>'flipInX','value'=>'flipInX'),
			array('label'=>'flipInY','value'=>'flipInY'),
			array('label'=>'lightSpeedIn','value'=>'lightSpeedIn'),
			array('label'=>'rotateIn','value'=>'rotateIn'),
			array('label'=>'rotateInDownLeft','value'=>'rotateInDownLeft'),
			array('label'=>'rotateInDownRight','value'=>'rotateInDownRight'),
			array('label'=>'rotateInUpLeft','value'=>'rotateInUpLeft'),
			array('label'=>'rotateInUpRight','value'=>'rotateInUpRight'),
			//array('label'=>'hinge','value'=>'hinge'),
			array('label'=>'rollIn','value'=>'rollIn'),
			array('label'=>'zoomIn','value'=>'zoomIn'),
			array('label'=>'zoomInDown','value'=>'zoomInDown'),
			array('label'=>'zoomInLeft','value'=>'zoomInLeft'),
			array('label'=>'zoomInRight','value'=>'zoomInRight'),
			array('label'=>'zoomInUp','value'=>'zoomInUp')
		);
	}
	
	
	public function isCategoryActive($category)
    {
        if ($id=Mage::app()->getRequest()->getParam('id')) {
			$child = Mage::getModel('mpanel/childs')->load($id);
			$settings = json_decode($child->getSetting(), true);
			
			if($settings['category_id']!=0){
				//$currentCategory = Mage::getModel('catalog/category')->load($settings['category_id']);
				return in_array($category->getId(), $settings['category_id']);
			}
			return false;
        }
        return false;
    }

    public function getCategoryCollection()
    {
        $collection = $this->getData('category_collection');
        if (is_null($collection)) {
            $collection = Mage::getModel('catalog/category')->getCollection();

            /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
            $collection->addAttributeToSelect('name');
        }
        return $collection;
    }
	
	public function getTreeCategory($category, $parent, $ids = array()){
		$rootCategoryId = Mage::app()->getWebsite(true)->getDefaultStore()->getRootCategoryId();
		
		$categoryIds = array();
		
		if ($id=Mage::app()->getRequest()->getParam('id')) {
			
			$child = Mage::getModel('mpanel/childs')->load($id);
			$settings = json_decode($child->getSetting(), true);
		
			if($settings['category_id']!=0){
				$categoryIds = $settings['category_id'];
			}
        }
		
		$children = $category->getChildrenCategories();
		$childrenCount = count($children);
		
		$htmlLi = '<li>';
		$html[] = $htmlLi;
		if($this->isCategoryActive($category)){
			$ids[] = $category->getId();
			$this->_ids = implode(",", $ids);
		}
		
		$html[] = '<a id="node'.$category->getId().'">';

		if($rootCategoryId!=$category->getId()){
			$html[] = '<input lang="'.$category->getId().'" type="checkbox" id="radio'.$category->getId().'" name="setting[category_id][]" value="'.$category->getId().'" class="radio checkbox'.$parent.'"';
			
			if(in_array($category->getId(), $categoryIds)){
				$html[] = ' checked="checked"';
			}
			$html[] = '/>';
		}
		
		$html[] = '<label for="radio'.$category->getId().'">' . $category->getName() . '</label>';

		$html[] = '</a>';
		
		$htmlChildren = '';
		if($childrenCount>0){
			foreach ($children as $child) {

				$_child = Mage::getModel('catalog/category')->load($child->getId());
				$htmlChildren .= $this->getTreeCategory($_child, $category->getId(), $ids);
			}
		}
		if (!empty($htmlChildren)) {
            $html[] = '<ul id="container'.$category->getId().'">';
            $html[] = $htmlChildren;
            $html[] = '</ul>';
        }

        $html[] = '</li>';
        $html = implode("\n", $html);
        return $html;
	}
	
	public function hasDifferentLayout(){
		$id = Mage::app()->getRequest()->getParam('id');
		$collection = Mage::getModel('mpanel/layout')
			->getCollection()
			->addFieldToFilter('page_type', 'category')
			->addFieldToFilter('indentifier', $id);
		if(count($collection)>0){
			return true;
		}
		return false;
	}
	
	public function getCategoryProductLayout(){
		$theme = Mage::getSingleton('core/design_package')->getTheme('frontend');
		
		$dir = Mage::getBaseDir() . '/app/design/frontend/mgstheme/'.$theme.'/template/mgs/mpanel/products/category_products/';
		$arrLayout = array();
		if (is_dir($dir))
		{
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					$file_parts = pathinfo($dir.$file);
					if(isset($file_parts['extension']) && $file_parts['extension']=='phtml'){
						$layout = $_layout = str_replace('.phtml','',$file);
						$layout = $this->convertThemeName($layout);
						$arrLayout[$file] = $layout;
					}
				}
			}
		}
		return $arrLayout;
	}
	
	public function convertThemeName($theme){
		$themeName = str_replace('_',' ',$theme);
		return ucfirst($themeName);
	}
	
	public function getContentOfBlock($block){
		switch ($block->getType()) {
			case 'separator':
				return $this->getLayout()->createBlock('core/template')->setBlockData($block)->setTemplate('mgs/mpanel/template/separator.phtml')->toHtml();
				break;
			case 'static':
				return $this->getLayout()->createBlock('cms/block')->setBlockId($block->getStaticBlockId())->toHtml();
				break;
			default:
				$helper = Mage::helper('cms');
				$processor = $helper->getPageTemplateProcessor();
				return $processor->filter($block->getBlockContent());
				break;
		}
	}
}