<?php

class MGS_Mpanel_PostController extends Mage_Core_Controller_Front_Action {

    // check to accept use panel
    protected function _checkAccept() {
        if (!Mage::helper('mpanel')->acceptToUsePanel()) {
            $this->_redirectUrl(Mage::getUrl());
            return;
        }
    }

    // save cms page config
    public function cmsAction() {
        $this->_checkAccept();
        $storeId = Mage::app()->getStore()->getId();
        $data = $this->getRequest()->getPost();
        $model = Mage::getModel('cms/page');
        $model->load($data['page_id'])->setId($data['page_id']);
        $model->setData($data);

        try {
            $model->save();

            if (isset($data['groups'])) {
                $this->saveSetting($data, $storeId, 'mgs_theme');
            }

            $this->_redirectUrl($data['current_url']);
            return;
        } catch (Mage_Core_Exception $e) {
            echo $e->getMessage();
        }
    }

    // save builder config
    public function layoutAction() {
        $this->_checkAccept();
        $storeId = Mage::app()->getStore()->getId();

        $data = $this->getRequest()->getPost();
        //echo '<pre>'; print_r($data); die();

        if (isset($_FILES['groups']['name']) && $_FILES['groups']['name'] != '') {
            $this->saveSetting($data, $storeId, 'mgs_theme');
        }

        if (isset($data['mgs_theme'])) {
            //$this->saveSetting($data, $storeId, 'mgs_theme');

            $this->saveSettings('mgs_theme', $data['mgs_theme'], $storeId);
        }

        if (isset($data['catalog_config'])) {
            $this->saveCatalogConfig($data['catalog_config'], $storeId);
        }

        if (isset($data['extension'])) {
            $this->saveExtensionConfig($data['extension'], $storeId);
        }

        if (isset($data['store_config'])) {
            $this->saveDealSetting($data, $storeId);
        }

        if (isset($data['my_account'])) {
            $this->saveMyAccountLayout($data['my_account'], $storeId);
        }

        if (isset($data['category_id'])) {
            $this->saveCategoryData($data);
        }

        if (isset($data['product_id'])) {
            $this->saveConfigProductDetail($data['product_details'], $storeId);
            $this->saveProductData($data);
        }

        if (isset($data['blog'])) {
            $this->saveSettings('blog', $data['blog'], $storeId);
        }

        if (isset($data['productquestions'])) {
            $this->saveSettings('productquestions', $data['productquestions'], $storeId);
        }

        if (isset($data['oscheckout'])) {
            $this->saveSettings('oscheckout', $data['oscheckout'], $storeId);
        }

        if (isset($data['status']) && isset($data['layout'])) {
            if ($data['status'] == 0 || $data['layout'] == '') {
                $homeLayout = Mage::getModel('mpanel/store')
                        ->getCollection()
                        ->addFieldToFilter('store_id', $storeId);

                if (count($homeLayout) > 0) {
                    foreach ($homeLayout as $layout) {
                        Mage::getModel('mpanel/store')->setStatus(0)->setId($layout->getId())->save();
                    }
                }
            } else {
                $homeLayout = Mage::getModel('mpanel/store')
                        ->getCollection()
                        ->addFieldToFilter('store_id', $storeId);
                $model = Mage::getModel('mpanel/store');

                $model->setStatus(1)->setName($data['layout'])->setStoreId($storeId);

                if ($homeLayout->getFirstItem()) {
                    $model->setId($homeLayout->getFirstItem()->getId())->save();
                }
            }
        }

        if (isset($data['page_id'])) {
            $cmsModel = Mage::getModel('cms/page');
            $cmsModel->load($data['page_id'])->setId($data['page_id']);
            $cmsModel->setTitle($data['title'])->setMetaKeywords($data['meta_keywords'])->setMetaDescription($data['meta_description']);

            try {
                $cmsModel->save();
            } catch (Mage_Core_Exception $e) {
                
            }
        }

        $this->_redirectUrl($data['current_url']);
        return;
    }

    public function saveCatalogConfig($data, $storeId) {
        $iDefaultStoreId = Mage::app()
                ->getWebsite()
                ->getDefaultGroup()
                ->getDefaultStoreId();
        $config = new Mage_Core_Model_Config();

        if ($iDefaultStoreId == $storeId) {
            foreach ($data as $field => $value) {
                $config->saveConfig('mpanel/catalog/' . $field, $value);
            }
        } else {
            foreach ($data as $field => $value) {
                $config->saveConfig('mpanel/catalog/' . $field, $value, 'stores', $storeId);
            }
        }
    }

    public function saveExtensionConfig($data, $storeId) {
        $iDefaultStoreId = Mage::app()
                ->getWebsite()
                ->getDefaultGroup()
                ->getDefaultStoreId();
        $config = new Mage_Core_Model_Config();

        if ($iDefaultStoreId == $storeId) {
            foreach ($data as $extension => $array) {
                foreach ($array as $field => $value) {
                    $config->saveConfig($extension . '/general/' . $field, $value);
                }
            }
        } else {
            foreach ($data as $extension => $array) {
                foreach ($array as $field => $value) {
                    $config->saveConfig($extension . '/general/' . $field, $value, 'stores', $storeId);
                }
            }
        }
    }

    // Save my account layout
    public function saveMyAccountLayout($data, $storeId) {
        $iDefaultStoreId = Mage::app()
                ->getWebsite()
                ->getDefaultGroup()
                ->getDefaultStoreId();
        $config = new Mage_Core_Model_Config();

        if ($iDefaultStoreId == $storeId) {
            $config->saveConfig('mpanel/my_account/layout', $data['layout']);
        } else {
            $config->saveConfig('mpanel/my_account/layout', $data['layout'], 'stores', $storeId);
        }
    }

    // Save settings
    public function saveSettings($section, $data, $storeId) {
        $iDefaultStoreId = Mage::app()
                ->getWebsite()
                ->getDefaultGroup()
                ->getDefaultStoreId();
        $config = new Mage_Core_Model_Config();

        if ($iDefaultStoreId == $storeId) {
            foreach ($data as $group => $_group) {
                foreach ($_group as $field => $value) {
                    if ($field == 'bg_color' && $value != '') {
                        $value = '#' . $value;
                    }
                    $config->saveConfig($section . '/' . $group . '/' . $field, $value);
                }
            }
        } else {
            foreach ($data as $group => $_group) {
                foreach ($_group as $field => $value) {
                    if ($field == 'bg_color' && $value != '') {
                        $value = '#' . $value;
                    }
                    $config->saveConfig($section . '/' . $group . '/' . $field, $value, 'stores', $storeId);
                }
            }
        }
    }

    public function saveConfigProductDetail($data, $storeId) {
        $iDefaultStoreId = Mage::app()
                ->getWebsite()
                ->getDefaultGroup()
                ->getDefaultStoreId();

        $config = new Mage_Core_Model_Config();

        if ($iDefaultStoreId == $storeId) {
            foreach ($data as $field => $value) {
                $config->saveConfig('mpanel/product_details/' . $field, $value);
            }
        } else {
            foreach ($data as $field => $value) {
                $config->saveConfig('mpanel/product_details/' . $field, $value, 'stores', $storeId);
            }
        }
    }

    public function saveDealSetting($data, $storeId) {
        $iDefaultStoreId = Mage::app()
                ->getWebsite()
                ->getDefaultGroup()
                ->getDefaultStoreId();

        $config = new Mage_Core_Model_Config();

        try {

            if ($iDefaultStoreId == $storeId) {
                $config->saveConfig('deals/deals_page/title', $data['title']);
                $config->saveConfig('deals/deals_page/meta_keyword', $data['meta_keywords']);
                $config->saveConfig('deals/deals_page/meta_description', $data['meta_description']);
            }

            $config->saveConfig('deals/deals_page/title', $data['title'], 'stores', $storeId);
            $config->saveConfig('deals/deals_page/meta_keyword', $data['meta_keywords'], 'stores', $storeId);
            $config->saveConfig('deals/deals_page/meta_description', $data['meta_description'], 'stores', $storeId);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    // Save category setttings
    public function saveCategoryData($data) {
        $data['general']['meta_title'] = $data['title'];
        $data['general']['meta_keywords'] = $data['meta_keywords'];
        $data['general']['meta_description'] = $data['meta_description'];

        $category = Mage::getModel('catalog/category');
        $category->setStoreId(0);
        $category->load($data['category_id']);

        $category->addData($data['general']);

        try {
            $category->save();
        } catch (Exception $e) {
            
        }
    }

    // Save data for product
    public function saveProductData($data) {
        $productData = array();
        $productData['meta_title'] = $data['title'];
        $productData['meta_keyword'] = $data['meta_keywords'];
        $productData['meta_description'] = $data['meta_description'];
        $productData['page_layout'] = $data['general']['page_layout'];
        try {
            Mage::getSingleton('catalog/product_action')
                    ->updateAttributes(array($data['product_id']), $productData, 0);
        } catch (Exception $e) {
            echo $e->getMessage();
            die();
        }
    }

    public function logoAction() {
        if ($data = $this->getRequest()->getPost()) {
            $storeId = Mage::app()->getStore()->getId();
            $this->saveSetting($data, $storeId, 'mgs_theme');
            Mage::getSingleton('core/session')->setSaved(1);
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function categoryImageAction() {
        if ($data = $this->getRequest()->getPost()) {
            Mage::getSingleton('core/session')->setSaved(1);
            try {
                if (isset($data['delete']) && (int) $data['delete'] == 1) {
                    $category = Mage::getModel('catalog/category')->load($data['id']);
                    $category->setImage('');
                    $iDefaultStoreId = Mage::app()
                            ->getWebsite()
                            ->getDefaultGroup()
                            ->getDefaultStoreId();
                    $storeId = Mage::app()->getStore()->getId();
                    if ($iDefaultStoreId == $storeId) {
                        $category->setStoreId(0);
                    }
                    $category->save();
                } else {
                    if (isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
                        try {
                            /* Starting upload */
                            $uploader = new Varien_File_Uploader('filename');

                            // Any extention would work
                            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                            $uploader->setAllowRenameFiles(false);

                            // Set the file upload mode 
                            // false -> get the file directly in the specified folder
                            // true -> get the file in the product like folders 
                            //	(file.jpg will go in something like /media/f/i/file.jpg)
                            $uploader->setFilesDispersion(false);

                            // We set media as the upload dir
                            $path = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'category' . DS;
                            $uploader->save($path, $_FILES['filename']['name']);
                        } catch (Exception $e) {
                            
                        }
                        //this way the name is saved in DB
                        $filename = $_FILES['filename']['name'];
                        $category = Mage::getModel('catalog/category')->load($data['id']);
                        $category->setImage($filename);
                        $iDefaultStoreId = Mage::app()
                                ->getWebsite()
                                ->getDefaultGroup()
                                ->getDefaultStoreId();
                        $storeId = Mage::app()->getStore()->getId();
                        if ($iDefaultStoreId == $storeId) {
                            $category->setStoreId(0);
                        }
                        $category->save();
                    }
                }
            } catch (Exception $ex) {
                
            }
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function categoryDescriptionAction() {
        if ($data = $this->getRequest()->getPost()) {
            Mage::getSingleton('core/session')->setSaved(1);
            try {
                $category = Mage::getModel('catalog/category')->load($data['id']);
                $category->setDescription($data['description']);
                $iDefaultStoreId = Mage::app()
                        ->getWebsite()
                        ->getDefaultGroup()
                        ->getDefaultStoreId();
                $storeId = Mage::app()->getStore()->getId();
                if ($iDefaultStoreId == $storeId) {
                    $category->setStoreId(0);
                }
                $category->save();
            } catch (Exception $ex) {
                
            }
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editCmsContentAction() {
        if ($data = $this->getRequest()->getPost()) {
            Mage::getSingleton('core/session')->setSaved(1);
            try {
                $cmsPage = Mage::getModel('cms/page')->load($data['id']);
                $cms = Mage::getModel('cms/page');
                $cms->setData($cmsPage->getData());
                $cms->setContent($data['content']);
                $cms->setId($data['id']);
                $cms->save();
            } catch (Exception $ex) {
                
            }
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function createProductTabAction() {
        $result = array();
        $storeId = Mage::app()->getStore()->getStoreId();
        $iDefaultStoreId = Mage::app()
                ->getWebsite()
                ->getDefaultGroup()
                ->getDefaultStoreId();
        try {
            if ($data = $this->getRequest()->getParams()) {
                $alias = $data['alias'];
                switch ($alias) {
                    case 'description':
                        $coreConfig = Mage::getModel('core/config');
                        $coreConfig->saveConfig('mpanel/product_tabs/description', '1', 'stores', $storeId);
                        break;
                    case 'additional':
                        $coreConfig = Mage::getModel('core/config');
                        $coreConfig->saveConfig('mpanel/product_tabs/aditional', '1', 'stores', $storeId);
                        break;
                    case 'reviews':
                        $coreConfig = Mage::getModel('core/config');
                        $coreConfig->saveConfig('mpanel/product_tabs/reviews', '1', 'stores', $storeId);
                        break;
                    case 'product_tag_list':
                        $coreConfig = Mage::getModel('core/config');
                        $coreConfig->saveConfig('mpanel/product_tabs/tags', '1', 'stores', $storeId);
                        break;
                    default:
                        break;
                }
                $result['result'] = 'success';
            }
        } catch (Exception $ex) {
            $result['result'] = $ex->getMessage();
        }
        echo json_encode($result);
    }

    public function saveProductTabAction() {
        $result = array();
        $storeId = Mage::app()->getStore()->getStoreId();
        $iDefaultStoreId = Mage::app()
                ->getWebsite()
                ->getDefaultGroup()
                ->getDefaultStoreId();
        try {
            if ($data = $this->getRequest()->getPost()) {
                if (!Mage::getStoreConfig('mpanel/product_tabs/custom_tab_one')) {
                    $coreConfig = Mage::getModel('core/config');
                    $coreConfig->saveConfig('mpanel/product_tabs/custom_tab_one', $data['type'], 'stores', $storeId);
                    $coreConfig->saveConfig('mpanel/product_tabs/custom_tab_one_title', $data['title'], 'stores', $storeId);
                    $coreConfig->saveConfig('mpanel/product_tabs/custom_tab_one_value', $data['value'], 'stores', $storeId);
                } else if (!Mage::getStoreConfig('mpanel/product_tabs/custom_tab_two')) {
                    $coreConfig = Mage::getModel('core/config');
                    $coreConfig->saveConfig('mpanel/product_tabs/custom_tab_two', $data['type'], 'stores', $storeId);
                    $coreConfig->saveConfig('mpanel/product_tabs/custom_tab_two_title', $data['title'], 'stores', $storeId);
                    $coreConfig->saveConfig('mpanel/product_tabs/custom_tab_two_value', $data['value'], 'stores', $storeId);
                } else if (!Mage::getStoreConfig('mpanel/product_tabs/custom_tab_three')) {
                    $coreConfig = Mage::getModel('core/config');
                    $coreConfig->saveConfig('mpanel/product_tabs/custom_tab_three', $data['type'], 'stores', $storeId);
                    $coreConfig->saveConfig('mpanel/product_tabs/custom_tab_three_title', $data['title'], 'stores', $storeId);
                    $coreConfig->saveConfig('mpanel/product_tabs/custom_tab_three_value', $data['value'], 'stores', $storeId);
                }
                $result['result'] = 'success';
            }
        } catch (Exception $ex) {
            $result['result'] = $ex->getMessage();
        }
        echo json_encode($result);
    }

    public function deleteProductTabAction() {
        $storeId = Mage::app()->getStore()->getStoreId();
        $iDefaultStoreId = Mage::app()
                ->getWebsite()
                ->getDefaultGroup()
                ->getDefaultStoreId();
        if ($data = $this->getRequest()->getParams()) {
            try {
                $alias = $data['alias'];
                switch ($alias) {
                    case 'description':
                        $coreConfig = Mage::getModel('core/config');
                        $coreConfig->saveConfig('mpanel/product_tabs/description', '0', 'stores', $storeId);
                        break;
                    case 'additional':
                        $coreConfig = Mage::getModel('core/config');
                        $coreConfig->saveConfig('mpanel/product_tabs/aditional', '0', 'stores', $storeId);
                        break;
                    case 'reviews':
                        $coreConfig = Mage::getModel('core/config');
                        $coreConfig->saveConfig('mpanel/product_tabs/reviews', '0', 'stores', $storeId);
                        break;
                    case 'product_tag_list':
                        $coreConfig = Mage::getModel('core/config');
                        $coreConfig->saveConfig('mpanel/product_tabs/tags', '0', 'stores', $storeId);
                        break;
                    case 'customtab1':
                        $coreConfig = Mage::getModel('core/config');
                        $coreConfig->saveConfig('mpanel/product_tabs/custom_tab_one', null, 'stores', $storeId);
                        $coreConfig->saveConfig('mpanel/product_tabs/custom_tab_one_title', null, 'stores', $storeId);
                        $coreConfig->saveConfig('mpanel/product_tabs/custom_tab_one_value', null, 'stores', $storeId);
                        break;
                    case 'customtab2':
                        $coreConfig = Mage::getModel('core/config');
                        $coreConfig->saveConfig('mpanel/product_tabs/custom_tab_two', null, 'stores', $storeId);
                        $coreConfig->saveConfig('mpanel/product_tabs/custom_tab_two_title', null, 'stores', $storeId);
                        $coreConfig->saveConfig('mpanel/product_tabs/custom_tab_two_value', null, 'stores', $storeId);
                        break;
                    case 'customtab3':
                        $coreConfig = Mage::getModel('core/config');
                        $coreConfig->saveConfig('mpanel/product_tabs/custom_tab_three', null, 'stores', $storeId);
                        $coreConfig->saveConfig('mpanel/product_tabs/custom_tab_three_title', null, 'stores', $storeId);
                        $coreConfig->saveConfig('mpanel/product_tabs/custom_tab_three_value', null, 'stores', $storeId);
                        break;
                    default:
                        break;
                }
            } catch (Exception $ex) {
                
            }
        }
        $this->_redirectReferer();
        return;
    }

    public function mapAction() {
        if ($data = $this->getRequest()->getPost()) {
            $storeId = Mage::app()->getStore()->getId();
            $this->saveSetting($data, $storeId, 'mpanel');
            Mage::getSingleton('core/session')->setSaved(1);
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function infoAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function staticAction() {
        if ($data = $this->getRequest()->getPost()) {
            $blockId = $data['block_id'];

            $block = Mage::getModel('cms/block');
            $block->load($blockId);
            $block->setData($block->getData())->setContent($data['content'])->setId($blockId);
            try {
                $block->save();
                $result['result'] = 'success';
                $result['content'] = $block->getContent();
            } catch (Exception $e) {
                $result['result'] = $e->getMessage();
            }

            echo json_encode($result);
        }
    }

    // save store config setting
    public function saveSetting($data, $storeId, $section) {
        $iDefaultStoreId = Mage::app()
                ->getWebsite()
                ->getDefaultGroup()
                ->getDefaultStoreId();

        $groups = $data['groups'];

        if (isset($groups['background']['fields']['bg_color']['value'])) {
            if ($groups['background']['fields']['bg_color']['value'] != '') {
                $groups['background']['fields']['bg_color']['value'] = '#' . $groups['background']['fields']['bg_color']['value'];
            }
        }

        if (isset($_FILES['groups']['name']) && is_array($_FILES['groups']['name'])) {
            /**
             * Carefully merge $_FILES and $_POST information
             * None of '+=' or 'array_merge_recursive' can do this correct
             */
            foreach ($_FILES['groups']['name'] as $groupName => $group) {
                if (is_array($group)) {
                    foreach ($group['fields'] as $fieldName => $field) {
                        if (!empty($field['value'])) {
                            $groups[$groupName]['fields'][$fieldName] = array('value' => $field['value']);
                        }
                    }
                }
            }
        }
        try {
            $website = Mage::app()->getWebsite()->getCode();
            $store = Mage::app()->getStore()->getCode();

            if ($iDefaultStoreId == $storeId) {
                Mage::getSingleton('adminhtml/config_data')
                        ->setSection($section)
                        ->setWebsite(null)
                        ->setStore(null)
                        ->setGroups($groups)
                        ->save();
            } else {
                Mage::getSingleton('adminhtml/config_data')
                        ->setSection($section)
                        ->setWebsite($website)
                        ->setStore($store)
                        ->setGroups($groups)
                        ->save();
            }

            // reinit configuration
            Mage::getConfig()->reinit();
            Mage::dispatchEvent('admin_system_config_section_save_after', array(
                'website' => $website,
                'store' => $store,
                'section' => $section
            ));
            Mage::app()->reinitStores();
        } catch (Mage_Core_Exception $e) {
            
        } catch (Exception $e) {
            
        }
    }

    // save parent block settings
    public function blockAction() {
        $this->_checkAccept();
        if ($data = $this->getRequest()->getPost()) {

            if (isset($data['remove_background']) && ($data['remove_background'] == 1)) {
                $data['background_image'] = '';
                $data['parallax'] = 0;
            } else {

                if (isset($_FILES['background_image']['name']) && $_FILES['background_image']['name'] != '') {
                    try {
                        /* Starting upload */
                        $uploader = new Varien_File_Uploader('background_image');

                        // Any extention would work
                        $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                        $uploader->setAllowRenameFiles(false);

                        // Set the file upload mode 
                        // false -> get the file directly in the specified folder
                        // true -> get the file in the product like folders 
                        //	(file.jpg will go in something like /media/f/i/file.jpg)
                        $uploader->setFilesDispersion(false);

                        // We set media as the upload dir
                        $path = Mage::getBaseDir('media') . DS . 'mpanel' . DS . 'background' . DS;
                        $uploader->save($path, $_FILES['background_image']['name']);
                    } catch (Exception $e) {
                        
                    }

                    //this way the name is saved in DB
                    $data['background_image'] = $_FILES['background_image']['name'];
                }
            }

            $storeId = Mage::app()->getStore()->getId();

            $block = Mage::getModel('mpanel/blocks')->getCollection()
                    ->addFieldToFilter('name', $data['name'])
                    ->addFieldToFilter('theme_name', $data['theme_name'])
                    ->addFieldToFilter('store_id', $storeId)
                    ->getFirstItem();

            $model = Mage::getModel('mpanel/blocks');
            $model->setData($data)->setStoreId($storeId);

            if ($block->getId()) {
                $model->setId($block->getId());
            }

            try {
                $model->save();
                Mage::getSingleton('core/session')->setSaved(1);
            } catch (Exception $e) {
                
            }
        }

        $this->_redirectUrl($data['current_url']);
        return;
    }

    // save child block setting and data
    public function childAction() {
        $this->_checkAccept();
        if ($data = $this->getRequest()->getPost()) {

            if (isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('filename');
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);

                    // Set media as the upload dir
                    $path = Mage::getBaseDir('media') . DS . 'promobanners' . DS;
                    $result = $uploader->save($path, $_FILES['filename']['name']);
                    $data['filename'] = $result['file'];
                } catch (Exception $e) {
                    
                }
            }

            $staticBlockModel = Mage::getModel('cms/block');
            $childModel = Mage::getModel('mpanel/childs');
			
			$data['store_id'] = Mage::app()->getStore()->getId();
			
			$data = $this->validateData($data);

            if ($data['type'] == 'static') {
				$staticBlockModel->setData($data['static_block']);
            }
			
            $check = 0;
            if ($data['type'] == 'duplicate_static') {
                $data['type'] = 'static';
                $check = 1;
				$data['static_block_id'] = $data['duplicate_static_block'];
            }
			
			if($data['type'] == 'separator'){
                $data['col'] = 12;
				$data['setting'] = json_encode($data['setting']);
            }
			
			if($data['type'] == 'separator' || $data['type'] == 'static' || $data['type'] == 'duplicate_static'){
				unset($data['block_content']);
			}

            $childModel->setData($data);
			
			

            if ($check) {
				unset($data['static_block_id']);
                $data['type'] = 'duplicate_static';
            }

            if (isset($data['static_block_id']) && ($data['static_block_id'] != '')) {
                $staticBlockModel->setId($data['static_block_id']);
            }

            $result = array();
            try {
				if ($data['type'] == 'static') {
					$staticBlockModel->save();
					
					if (isset($data['static_block_id']) && ($data['static_block_id'] != '')) {
                        $childModel->setStaticBlockId($data['static_block_id']);
                    } else {
                        if ($data['type'] != 'duplicate_static') {
                            $childModel->setStaticBlockId($staticBlockModel->getId());
                        } else {
                            $childModel->setStaticBlockId($data['duplicate_static_block']);
                        }
                    }
				}

                if (isset($data['child_id']) && ($data['child_id'] != '')) {
                    $childModel->setId($data['child_id']);
                } else {
                    $childModel->setPosition(Mage::helper('mpanel')->getNewPositionOfChild($data['store_id'], $data['block_name'], $data['home_name']));
                }

                $childModel->save();

                $result['result'] = 'success';
            } catch (Exception $e) {
                $result['result'] = $e->getMessage();
            }

            if ($data['type'] == 'promo_banner' || $data['type'] == 'revolution') {
                $this->_redirectUrl($data['current_url']);
                return;
            }

            echo json_encode($result);
        }
    }

    public function childInCategoryAction() {
        $this->_checkAccept();
        $result = array();
        if ($data = $this->getRequest()->getPost()) {
            if (isset($data['block_id'])) {
                $model = Mage::getModel('cms/block')->load($data['block_id']);
                try {
                    $currentCategoryId = $data['category_id'];
                    $parentRight = array();
                    $parentLeft = array();
                    if (isset($data['product_id']) && isset($data['category_id'])) {
                        $layoutCollectionParent = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'product'))
                                ->addFieldToFilter('indentifier', array('eq' => 0));
                    } else {
                        $layoutCollectionParent = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'category'))
                                ->addFieldToFilter('indentifier', array('eq' => 0));
                    }
                    if (count($layoutCollectionParent)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollectionParent->getFirstItem()->getId());
                        $parentRight = unserialize($layout->getRight());
                        $parentLeft = unserialize($layout->getLeft());
                    }
                    if (isset($data['product_id']) && isset($data['category_id'])) {
                        $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'product'))
                                ->addFieldToFilter('indentifier', array('eq' => $data['product_id']));
                    } else {
                        $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'category'))
                                ->addFieldToFilter('indentifier', array('eq' => $currentCategoryId));
                    }
                    if (count($layoutCollection)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                        if (isset($data['position']) && $data['position'] == 'right') {
                            $right = unserialize($layout->getRight());
                            unset($right[$model->getData('identifier')]);
                            $right[$data['identifier']] = $model->getId();
                            //$finalRightArray = array_merge($parentRight, $right);
                            $finalRightArray = $right;
                            $layout->setRight(serialize($finalRightArray));
                        } else {
                            $left = unserialize($layout->getLeft());
                            unset($left[$model->getData('identifier')]);
                            $left[$data['identifier']] = $model->getId();
                            $finalLeftArray = array_merge($parentLeft, $left);
                            $layout->setLeft(serialize($finalLeftArray));
                        }
                        $layout->save();
                    }
                    $model->setData($data);
                    $model->save();
                    $result['result'] = 'success';
                } catch (Exception $e) {
                    $result['result'] = $e->getMessage();
                }
            } else {
                $model = Mage::getModel('cms/block');
                $model->setData($data);
                try {
                    $model->save();
                    $currentCategoryId = $data['category_id'];
                    $parentRight = array();
                    $parentLeft = array();
                    if (isset($data['product_id']) && isset($data['category_id'])) {
                        $layoutCollectionParent = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'product'))
                                ->addFieldToFilter('indentifier', array('eq' => 0));
                    } else {
                        $layoutCollectionParent = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'category'))
                                ->addFieldToFilter('indentifier', array('eq' => 0));
                    }
                    if (count($layoutCollectionParent)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollectionParent->getFirstItem()->getId());
                        $parentRight = unserialize($layout->getRight());
                        $parentLeft = unserialize($layout->getLeft());
                    }
                    if (isset($data['product_id']) && isset($data['category_id'])) {
                        $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'product'))
                                ->addFieldToFilter('indentifier', array('eq' => $data['product_id']));
                    } else {
                        $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'category'))
                                ->addFieldToFilter('indentifier', array('eq' => $currentCategoryId));
                    }
                    if (count($layoutCollection)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                        if (isset($data['position']) && $data['position'] == 'right') {
                            $right = unserialize($layout->getRight());
                            $right[$model->getData('identifier')] = $model->getId();
                            //$finalRightArray = array_merge($parentRight, $right);
                            $finalRightArray = $right;
                            $layout->setRight(serialize($finalRightArray));
                        } else {
                            $left = unserialize($layout->getLeft());
                            $left[$model->getData('identifier')] = $model->getId();
                            $finalLeftArray = array_merge($parentLeft, $left);
                            $layout->setLeft(serialize($finalLeftArray));
                        }
                        $layout->save();
                    } else {
                        $layout = Mage::getModel('mpanel/layout');
                        if (isset($data['product_id']) && isset($data['category_id'])) {
                            $layout->setPageType('product');
                            $layout->setIndentifier($data['product_id']);
                        } else {
                            $layout->setPageType('category');
                            $layout->setIndentifier($currentCategoryId);
                        }
                        if (isset($data['position']) && $data['position'] == 'right') {
                            $parentRight[$model->getData('identifier')] = $model->getId();
                            $layout->setRight(serialize($parentRight));
                            if (count($parentLeft)) {
                                $layout->setLeft(serialize($parentLeft));
                            }
                        } else {
                            $parentLeft[$model->getData('identifier')] = $model->getId();
                            $layout->setLeft(serialize($parentLeft));
                            if (count($parentRight)) {
                                $layout->setRight(serialize($parentRight));
                            }
                        }
                        $layout->save();
                    }
                    $result['result'] = 'success';
                } catch (Exception $e) {
                    $result['result'] = $e->getMessage();
                }
            }
        }
        echo json_encode($result);
    }

    public function createStaticBlockAction() {
        $this->_checkAccept();
        $result = array();
        $storeId = Mage::app()->getStore()->getStoreId();
        $iDefaultStoreId = Mage::app()
                ->getWebsite()
                ->getDefaultGroup()
                ->getDefaultStoreId();
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('cms/block');
            $model->setData($data);
            try {
                $model->save();
                $result['value'] = $model->getIdentifier();
                $result['result'] = 'success';
            } catch (Exception $e) {
                $result['result'] = $e->getMessage();
            }
        }
        echo json_encode($result);
    }

    public function childInCmsAction() {
        $this->_checkAccept();
        $result = array();
        if ($data = $this->getRequest()->getPost()) {
            if (isset($data['block_id'])) {
                $model = Mage::getModel('cms/block')->load($data['block_id']);
                try {
                    $pageId = $data['page_id'];
                    $parentRight = array();
                    $parentLeft = array();
                    $layoutCollectionParent = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'cms'))
                            ->addFieldToFilter('indentifier', array('eq' => 0));
                    if (count($layoutCollectionParent)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollectionParent->getFirstItem()->getId());
                        $parentRight = unserialize($layout->getRight());
                        $parentLeft = unserialize($layout->getLeft());
                    }
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'cms'))
                            ->addFieldToFilter('indentifier', array('eq' => $pageId));
                    if (count($layoutCollection)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                        if (isset($data['position']) && $data['position'] == 'right') {
                            $right = unserialize($layout->getRight());
                            unset($right[$model->getData('identifier')]);
                            $right[$data['identifier']] = $model->getId();
                            //$finalRightArray = array_merge($parentRight, $right);
                            $finalRightArray = $right;
                            $layout->setRight(serialize($finalRightArray));
                        } else {
                            $left = unserialize($layout->getLeft());
                            unset($left[$model->getData('identifier')]);
                            $left[$data['identifier']] = $model->getId();
                            $finalLeftArray = array_merge($parentLeft, $left);
                            $layout->setLeft(serialize($finalLeftArray));
                        }
                        $layout->save();
                    }
                    $model->setData($data);
                    $model->save();
                    $result['result'] = 'success';
                } catch (Exception $e) {
                    $result['result'] = $e->getMessage();
                }
            } else {
                $model = Mage::getModel('cms/block');
                $model->setData($data);
                try {
                    $model->save();
                    $pageId = $data['page_id'];
                    $parentRight = array();
                    $parentLeft = array();
                    $layoutCollectionParent = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'cms'))
                            ->addFieldToFilter('indentifier', array('eq' => 0));
                    if (count($layoutCollectionParent)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollectionParent->getFirstItem()->getId());
                        $parentRight = unserialize($layout->getRight());
                        $parentLeft = unserialize($layout->getLeft());
                    }
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'cms'))
                            ->addFieldToFilter('indentifier', array('eq' => $pageId));
                    if (count($layoutCollection)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                        if (isset($data['position']) && $data['position'] == 'right') {
                            $right = unserialize($layout->getRight());
                            $right[$model->getData('identifier')] = $model->getId();
                            //$finalRightArray = array_merge($parentRight, $right);
                            $finalRightArray = $right;
                            $layout->setRight(serialize($finalRightArray));
                        } else {
                            $left = unserialize($layout->getLeft());
                            $left[$model->getData('identifier')] = $model->getId();
                            $finalLeftArray = array_merge($parentLeft, $left);
                            $layout->setLeft(serialize($finalLeftArray));
                        }
                        $layout->save();
                    } else {
                        $layout = Mage::getModel('mpanel/layout');
                        $layout->setPageType('cms');
                        $layout->setIndentifier($pageId);
                        if (isset($data['position']) && $data['position'] == 'right') {
                            $parentRight[$model->getData('identifier')] = $model->getId();
                            $layout->setRight(serialize($parentRight));
                            if (count($parentLeft)) {
                                $layout->setLeft(serialize($parentLeft));
                            }
                        } else {
                            $parentLeft[$model->getData('identifier')] = $model->getId();
                            $layout->setLeft(serialize($parentLeft));
                            if (count($parentRight)) {
                                $layout->setRight(serialize($parentRight));
                            }
                        }
                        $layout->save();
                    }
                    $result['result'] = 'success';
                } catch (Exception $e) {
                    $result['result'] = $e->getMessage();
                }
            }
        }
        echo json_encode($result);
    }

    public function childInCustomerAction() {
        $this->_checkAccept();
        $result = array();
        if ($data = $this->getRequest()->getPost()) {
            if (isset($data['block_id'])) {
                $model = Mage::getModel('cms/block')->load($data['block_id']);
                try {
                    $parentRight = array();
                    $parentLeft = array();
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'cms'))
                            ->addFieldToFilter('indentifier', array('eq' => 0));
                    if (count($layoutCollection)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                        if (isset($data['position']) && $data['position'] == 'right') {
                            $right = unserialize($layout->getRight());
                            unset($right[$model->getData('identifier')]);
                            $right[$data['identifier']] = $model->getId();
                            $layout->setRight(serialize($right));
                        } else {
                            $left = unserialize($layout->getLeft());
                            unset($left[$model->getData('identifier')]);
                            $left[$data['identifier']] = $model->getId();
                            $layout->setLeft(serialize($left));
                        }
                        $layout->save();
                    }
                    $model->setData($data);
                    $model->save();
                    $result['result'] = 'success';
                } catch (Exception $e) {
                    $result['result'] = $e->getMessage();
                }
            } else {
                $model = Mage::getModel('cms/block');
                $model->setData($data);
                try {
                    $model->save();
                    $parentRight = array();
                    $parentLeft = array();
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'customer'))
                            ->addFieldToFilter('indentifier', array('eq' => 0));
                    if (count($layoutCollection)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                        if (isset($data['position']) && $data['position'] == 'right') {
                            $right = unserialize($layout->getRight());
                            $right[$model->getData('identifier')] = $model->getId();
                            $layout->setRight(serialize($right));
                        } else {
                            $left = unserialize($layout->getLeft());
                            $left[$model->getData('identifier')] = $model->getId();
                            $layout->setLeft(serialize($left));
                        }
                        $layout->save();
                    } else {
                        $layout = Mage::getModel('mpanel/layout');
                        $layout->setPageType('customer');
                        $layout->setIndentifier(0);
                        if (isset($data['position']) && $data['position'] == 'right') {
                            $parentRight[$model->getData('identifier')] = $model->getId();
                            $layout->setRight(serialize($parentRight));
                            if (count($parentLeft)) {
                                $layout->setLeft(serialize($parentLeft));
                            }
                        } else {
                            $parentLeft[$model->getData('identifier')] = $model->getId();
                            $layout->setLeft(serialize($parentLeft));
                            if (count($parentRight)) {
                                $layout->setRight(serialize($parentRight));
                            }
                        }
                        $layout->save();
                    }
                    $result['result'] = 'success';
                } catch (Exception $e) {
                    $result['result'] = $e->getMessage();
                }
            }
        }
        echo json_encode($result);
    }

    public function newInCategoryAction() {
        $this->_checkAccept();
        $result = array();
        if ($data = $this->getRequest()->getParams()) {
            try {
                $currentCategoryId = $data['category_id'];
                $parentRight = array();
                $parentLeft = array();
                if (isset($data['product_id']) && isset($data['category_id'])) {
                    $layoutCollectionParent = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'product'))
                            ->addFieldToFilter('indentifier', array('eq' => 0));
                } else {
                    $layoutCollectionParent = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'category'))
                            ->addFieldToFilter('indentifier', array('eq' => 0));
                }
                if (count($layoutCollectionParent)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollectionParent->getFirstItem()->getId());
                    $parentRight = unserialize($layout->getRight());
                    $parentLeft = unserialize($layout->getLeft());
                }
                if (isset($data['product_id']) && isset($data['category_id'])) {
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'product'))
                            ->addFieldToFilter('indentifier', array('eq' => $data['product_id']));
                } else {
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'category'))
                            ->addFieldToFilter('indentifier', array('eq' => $currentCategoryId));
                }
                if (count($layoutCollection)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                    if (isset($data['template']) && $data['template'] == 'category_right') {
                        $right = unserialize($layout->getRight());
                        $right[$data['core_block']] = $data['core_block'];
                        //$finalRightArray = array_merge($parentRight, $right);
                        $finalRightArray = $right;
                        $layout->setRight(serialize($finalRightArray));
                    } else {
                        $left = unserialize($layout->getLeft());
                        $left[$data['core_block']] = $data['core_block'];
                        $finalLeftArray = array_merge($parentLeft, $left);
                       if(is_array($parentLeft)){
							$finalLeftArray = array_merge($parentLeft, $left);
						}else{
							$finalLeftArray = $left;
						}
                        
                        $layout->setLeft(serialize($finalLeftArray));
                    }
                    $layout->save();
                } else {
                    $layout = Mage::getModel('mpanel/layout');
                    if (isset($data['product_id']) && isset($data['category_id'])) {
                        $layout->setPageType('product');
                        $layout->setIndentifier($data['product_id']);
                    } else {
                        $layout->setPageType('category');
                        $layout->setIndentifier($currentCategoryId);
                    }
                    if (isset($data['template']) && $data['template'] == 'category_right') {
                        $parentRight[$data['core_block']] = $data['core_block'];
                        $layout->setRight(serialize($parentRight));
                        if (count($parentLeft)) {
                            $layout->setLeft(serialize($parentLeft));
                        }
                    } else {
                        $parentLeft[$data['core_block']] = $data['core_block'];
                        $layout->setLeft(serialize($parentLeft));
                        if (count($parentRight)) {
                            $layout->setRight(serialize($parentRight));
                        }
                    }
                    $layout->save();
                }
                $result['result'] = 'success';
            } catch (Exception $e) {
                $result['result'] = $e->getMessage();
            }
        }
        echo json_encode($result);
    }

    public function newInCmsAction() {
        $this->_checkAccept();
        $result = array();
        if ($data = $this->getRequest()->getParams()) {
            try {
                $pageId = $data['page_id'];
                $parentRight = array();
                $parentLeft = array();
                $layoutCollectionParent = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'cms'))
                        ->addFieldToFilter('indentifier', array('eq' => 0));
                if (count($layoutCollectionParent)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollectionParent->getFirstItem()->getId());
                    $parentRight = unserialize($layout->getRight());
                    $parentLeft = unserialize($layout->getLeft());
                }
                $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'cms'))
                        ->addFieldToFilter('indentifier', array('eq' => $pageId));
                if (count($layoutCollection)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                    if (isset($data['template']) && $data['template'] == 'cms_right') {
                        $right = unserialize($layout->getRight());
                        $right[$data['core_block']] = $data['core_block'];
                        //$finalRightArray = array_merge($parentRight, $right);
                        $finalRightArray = $right;
                        $layout->setRight(serialize($finalRightArray));
                    } else {
                        $left = unserialize($layout->getLeft());
                        $left[$data['core_block']] = $data['core_block'];
                        $finalLeftArray = array_merge($parentLeft, $left);
                        $layout->setLeft(serialize($finalLeftArray));
                    }
                    $layout->save();
                } else {
                    $layout = Mage::getModel('mpanel/layout');
                    $layout->setPageType('cms');
                    $layout->setIndentifier($pageId);
                    if (isset($data['template']) && $data['template'] == 'cms_right') {
                        $parentRight[$data['core_block']] = $data['core_block'];
                        $layout->setRight(serialize($parentRight));
                        if (count($parentLeft)) {
                            $layout->setLeft(serialize($parentLeft));
                        }
                    } else {
                        $parentLeft[$data['core_block']] = $data['core_block'];
                        $layout->setLeft(serialize($parentLeft));
                        if (count($parentRight)) {
                            $layout->setRight(serialize($parentRight));
                        }
                    }
                    $layout->save();
                }
                $result['result'] = 'success';
            } catch (Exception $e) {
                $result['result'] = $e->getMessage();
            }
        }
        echo json_encode($result);
    }

    public function newInCustomerAction() {
        $this->_checkAccept();
        $result = array();
        if ($data = $this->getRequest()->getParams()) {
            try {
                $parentRight = array();
                $parentLeft = array();
                $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'customer'))
                        ->addFieldToFilter('indentifier', array('eq' => 0));
                if (count($layoutCollection)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                    if (isset($data['template']) && $data['template'] == 'customer_right') {
                        $right = unserialize($layout->getRight());
                        $right[$data['core_block']] = $data['core_block'];
                        $layout->setRight(serialize($right));
                    } else {
                        $left = unserialize($layout->getLeft());
                        $left[$data['core_block']] = $data['core_block'];
                        $layout->setLeft(serialize($left));
                    }
                    $layout->save();
                } else {
                    $layout = Mage::getModel('mpanel/layout');
                    $layout->setPageType('customer');
                    $layout->setIndentifier(0);
                    if (isset($data['template']) && $data['template'] == 'customer_right') {
                        $parentRight[$data['core_block']] = $data['core_block'];
                        $layout->setRight(serialize($parentRight));
                        if (count($parentLeft)) {
                            $layout->setLeft(serialize($parentLeft));
                        }
                    } else {
                        $parentLeft[$data['core_block']] = $data['core_block'];
                        $layout->setLeft(serialize($parentLeft));
                        if (count($parentRight)) {
                            $layout->setRight(serialize($parentRight));
                        }
                    }
                    $layout->save();
                }
                $result['result'] = 'success';
            } catch (Exception $e) {
                $result['result'] = $e->getMessage();
            }
        }
        echo json_encode($result);
    }

    public function pollInCategoryAction() {
        $this->_checkAccept();
        $result = array();
        if ($data = $this->getRequest()->getPost()) {
            try {
                $currentCategoryId = $data['category_id'];
                $parentRight = array();
                $parentLeft = array();
                if (isset($data['product_id']) && isset($data['category_id'])) {
                    $layoutCollectionParent = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'product'))
                            ->addFieldToFilter('indentifier', array('eq' => 0));
                } else {
                    $layoutCollectionParent = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'category'))
                            ->addFieldToFilter('indentifier', array('eq' => 0));
                }
                if (count($layoutCollectionParent)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollectionParent->getFirstItem()->getId());
                    $parentRight = unserialize($layout->getRight());
                    $parentLeft = unserialize($layout->getLeft());
                }
                if (isset($data['product_id']) && isset($data['category_id'])) {
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'product'))
                            ->addFieldToFilter('indentifier', array('eq' => $data['product_id']));
                } else {
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'category'))
                            ->addFieldToFilter('indentifier', array('eq' => $currentCategoryId));
                }
                if (count($layoutCollection)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                    if (isset($data['position']) && $data['position'] == 'right') {
                        $right = unserialize($layout->getRight());
                        $right[$data['core_block']] = $data['core_block'] . '-' . $data['poll_id'];
                        //$finalRightArray = array_merge($parentRight, $right);
                        $finalRightArray = $right;
                        $layout->setRight(serialize($finalRightArray));
                    } else {
                        $left = unserialize($layout->getLeft());
                        $left[$data['core_block']] = $data['core_block'] . '-' . $data['poll_id'];
                        $finalLeftArray = array_merge($parentLeft, $left);
                        $layout->setLeft(serialize($finalLeftArray));
                    }
                    $layout->save();
                } else {
                    $layout = Mage::getModel('mpanel/layout');
                    if (isset($data['product_id']) && isset($data['category_id'])) {
                        $layout->setPageType('product');
                        $layout->setIndentifier($data['product_id']);
                    } else {
                        $layout->setPageType('category');
                        $layout->setIndentifier($currentCategoryId);
                    }
                    if (isset($data['position']) && $data['position'] == 'right') {
                        $parentRight[$data['core_block']] = $data['core_block'] . '-' . $data['poll_id'];
                        $layout->setRight(serialize($parentRight));
                        if (count($parentLeft)) {
                            $layout->setLeft(serialize($parentLeft));
                        }
                    } else {
                        $parentLeft[$data['core_block']] = $data['core_block'] . '-' . $data['poll_id'];
                        $layout->setLeft(serialize($parentLeft));
                        if (count($parentRight)) {
                            $layout->setRight(serialize($parentRight));
                        }
                    }
                    $layout->save();
                }
                $result['result'] = 'success';
            } catch (Exception $e) {
                $result['result'] = $e->getMessage();
            }
        }
        echo json_encode($result);
    }

    public function pollInCmsAction() {
        $this->_checkAccept();
        $result = array();
        if ($data = $this->getRequest()->getPost()) {
            try {
                $pageId = $data['page_id'];
                $parentRight = array();
                $parentLeft = array();
                $layoutCollectionParent = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'cms'))
                        ->addFieldToFilter('indentifier', array('eq' => 0));
                if (count($layoutCollectionParent)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollectionParent->getFirstItem()->getId());
                    $parentRight = unserialize($layout->getRight());
                    $parentLeft = unserialize($layout->getLeft());
                }
                $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'cms'))
                        ->addFieldToFilter('indentifier', array('eq' => $pageId));
                if (count($layoutCollection)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                    if (isset($data['position']) && $data['position'] == 'right') {
                        $right = unserialize($layout->getRight());
                        $right[$data['core_block']] = $data['core_block'] . '-' . $data['poll_id'];
                        //$finalRightArray = array_merge($parentRight, $right);
                        $finalRightArray = $right;
                        $layout->setRight(serialize($finalRightArray));
                    } else {
                        $left = unserialize($layout->getLeft());
                        $left[$data['core_block']] = $data['core_block'] . '-' . $data['poll_id'];
                        $finalLeftArray = array_merge($parentLeft, $left);
                        $layout->setLeft(serialize($finalLeftArray));
                    }
                    $layout->save();
                } else {
                    $layout = Mage::getModel('mpanel/layout');
                    $layout->setPageType('cms');
                    $layout->setIndentifier($pageId);
                    if (isset($data['position']) && $data['position'] == 'right') {
                        $parentRight[$data['core_block']] = $data['core_block'] . '-' . $data['poll_id'];
                        $layout->setRight(serialize($parentRight));
                        if (count($parentLeft)) {
                            $layout->setLeft(serialize($parentLeft));
                        }
                    } else {
                        $parentLeft[$data['core_block']] = $data['core_block'] . '-' . $data['poll_id'];
                        $layout->setLeft(serialize($parentLeft));
                        if (count($parentRight)) {
                            $layout->setRight(serialize($parentRight));
                        }
                    }
                    $layout->save();
                }
                $result['result'] = 'success';
            } catch (Exception $e) {
                $result['result'] = $e->getMessage();
            }
        }
        echo json_encode($result);
    }

    public function pollInCustomerAction() {
        $this->_checkAccept();
        $result = array();
        if ($data = $this->getRequest()->getPost()) {
            try {
                $parentRight = array();
                $parentLeft = array();
                $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'customer'))
                        ->addFieldToFilter('indentifier', array('eq' => 0));
                if (count($layoutCollection)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                    if (isset($data['position']) && $data['position'] == 'right') {
                        $right = unserialize($layout->getRight());
                        $right[$data['core_block']] = $data['core_block'] . '-' . $data['poll_id'];
                        $layout->setRight(serialize($right));
                    } else {
                        $left = unserialize($layout->getLeft());
                        $left[$data['core_block']] = $data['core_block'] . '-' . $data['poll_id'];
                        $layout->setLeft(serialize($left));
                    }
                    $layout->save();
                } else {
                    $layout = Mage::getModel('mpanel/layout');
                    $layout->setPageType('customer');
                    $layout->setIndentifier(0);
                    if (isset($data['position']) && $data['position'] == 'right') {
                        $parentRight[$data['core_block']] = $data['core_block'] . '-' . $data['poll_id'];
                        $layout->setRight(serialize($parentRight));
                        if (count($parentLeft)) {
                            $layout->setLeft(serialize($parentLeft));
                        }
                    } else {
                        $parentLeft[$data['core_block']] = $data['core_block'] . '-' . $data['poll_id'];
                        $layout->setLeft(serialize($parentLeft));
                        if (count($parentRight)) {
                            $layout->setRight(serialize($parentRight));
                        }
                    }
                    $layout->save();
                }
                $result['result'] = 'success';
            } catch (Exception $e) {
                $result['result'] = $e->getMessage();
            }
        }
        echo json_encode($result);
    }

    public function setPromoAction() {
        $this->_checkAccept();
        if ($data = $this->getRequest()->getParams()) {
            try {
                $currentCategoryId = $data['category_id'];
                $parentRight = array();
                $parentLeft = array();
                if (isset($data['product_id']) && isset($data['category_id'])) {
                    $layoutCollectionParent = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'product'))
                            ->addFieldToFilter('indentifier', array('eq' => 0));
                } else {
                    $layoutCollectionParent = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'category'))
                            ->addFieldToFilter('indentifier', array('eq' => 0));
                }
                if (count($layoutCollectionParent)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollectionParent->getFirstItem()->getId());
                    $parentRight = unserialize($layout->getRight());
                    $parentLeft = unserialize($layout->getLeft());
                }
                if (isset($data['product_id']) && isset($data['category_id'])) {
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'product'))
                            ->addFieldToFilter('indentifier', array('eq' => $data['product_id']));
                } else {
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'category'))
                            ->addFieldToFilter('indentifier', array('eq' => $currentCategoryId));
                }
                if (count($layoutCollection)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                    if (isset($data['template']) && $data['template'] == 'category_right') {
                        $right = unserialize($layout->getRight());
                        $right['promoBanner' . $data['promobanners_id']] = 'promoBanner' . $data['promobanners_id'];
                        //$finalRightArray = array_merge($parentRight, $right);
                        $finalRightArray = $right;
                        //echo '<pre>'; var_dump($finalRightArray); die();
                        $layout->setRight(serialize($finalRightArray));
                    } else {
                        $left = unserialize($layout->getLeft());
                        $left['promoBanner' . $data['promobanners_id']] = 'promoBanner' . $data['promobanners_id'];
                        $finalLeftArray = array_merge($parentLeft, $left);
                        $layout->setLeft(serialize($finalLeftArray));
                    }
                    $layout->save();
                } else {
                    $layout = Mage::getModel('mpanel/layout');
                    if (isset($data['product_id']) && isset($data['category_id'])) {
                        $layout->setPageType('product');
                        $layout->setIndentifier($data['product_id']);
                    } else {
                        $layout->setPageType('category');
                        $layout->setIndentifier($currentCategoryId);
                    }
                    if (isset($data['template']) && $data['template'] == 'category_right') {
                        $parentRight['promoBanner' . $data['promobanners_id']] = 'promoBanner' . $data['promobanners_id'];
                        $layout->setRight(serialize($parentRight));
                        if (count($parentLeft)) {
                            $layout->setLeft(serialize($parentLeft));
                        }
                    } else {
                        $parentLeft['promoBanner' . $data['promobanners_id']] = 'promoBanner' . $data['promobanners_id'];
                        $layout->setLeft(serialize($parentLeft));
                        if (count($parentRight)) {
                            $layout->setRight(serialize($parentRight));
                        }
                    }
                    $layout->save();
                }
            } catch (Exception $e) {
                
            }
        }
        if ($this->getRequest()->getParam('category_id') && $this->getRequest()->getParam('product_id')) {
            $oRewrite = Mage::getModel('core/url_rewrite')
                    ->setStoreId(Mage::app()->getStore()->getStoreId())
                    ->getCollection()
                    ->addFieldToFilter('category_id', array('eq' => $currentCategoryId))
                    ->addFieldToFilter('product_id', array('eq' => $this->getRequest()->getParam('product_id')))
                    ->getFirstItem();
        } else {
            $oRewrite = Mage::getModel('core/url_rewrite')
                    ->setStoreId(Mage::app()->getStore()->getStoreId())
                    ->getCollection()
                    ->addFieldToFilter('category_id', array('eq' => $currentCategoryId))
                    ->getFirstItem();
        }
        $this->_redirectUrl(Mage::getBaseUrl() . $oRewrite->getData('request_path'));
        return;
    }

    public function setPromoInCmsAction() {
        $this->_checkAccept();
        if ($data = $this->getRequest()->getParams()) {
            try {
                $pageId = $data['page_id'];
                $parentRight = array();
                $parentLeft = array();
                $layoutCollectionParent = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'cms'))
                        ->addFieldToFilter('indentifier', array('eq' => 0));
                if (count($layoutCollectionParent)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollectionParent->getFirstItem()->getId());
                    $parentRight = unserialize($layout->getRight());
                    $parentLeft = unserialize($layout->getLeft());
                }
                $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'cms'))
                        ->addFieldToFilter('indentifier', array('eq' => $pageId));
                if (count($layoutCollection)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                    if (isset($data['template']) && $data['template'] == 'cms_right') {
                        $right = unserialize($layout->getRight());
                        $right['promoBanner' . $data['promobanners_id']] = 'promoBanner' . $data['promobanners_id'];
                        //$finalRightArray = array_merge($parentRight, $right);
                        $finalRightArray = $right;
                        //echo '<pre>'; var_dump($finalRightArray); die();
                        $layout->setRight(serialize($finalRightArray));
                    } else {
                        $left = unserialize($layout->getLeft());
                        $left['promoBanner' . $data['promobanners_id']] = 'promoBanner' . $data['promobanners_id'];
                        $finalLeftArray = array_merge($parentLeft, $left);
                        $layout->setLeft(serialize($finalLeftArray));
                    }
                    $layout->save();
                } else {
                    $layout = Mage::getModel('mpanel/layout');
                    $layout->setPageType('cms');
                    $layout->setIndentifier($pageId);
                    if (isset($data['template']) && $data['template'] == 'cms_right') {
                        $parentRight['promoBanner' . $data['promobanners_id']] = 'promoBanner' . $data['promobanners_id'];
                        $layout->setRight(serialize($parentRight));
                        if (count($parentLeft)) {
                            $layout->setLeft(serialize($parentLeft));
                        }
                    } else {
                        $parentLeft['promoBanner' . $data['promobanners_id']] = 'promoBanner' . $data['promobanners_id'];
                        $layout->setLeft(serialize($parentLeft));
                        if (count($parentRight)) {
                            $layout->setRight(serialize($parentRight));
                        }
                    }
                    $layout->save();
                }
            } catch (Exception $e) {
                
            }
        }
        $cmsPage = Mage::getModel('cms/page')->load($this->getRequest()->getParam('page_id'));
        $this->_redirectUrl(Mage::getBaseUrl() . $cmsPage->getData('identifier'));
        return;
    }

    public function setPromoInCustomerAction() {
        $this->_checkAccept();
        if ($data = $this->getRequest()->getParams()) {
            try {
                $parentRight = array();
                $parentLeft = array();
                $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'customer'))
                        ->addFieldToFilter('indentifier', array('eq' => 0));
                if (count($layoutCollection)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                    if (isset($data['template']) && $data['template'] == 'customer_right') {
                        $right = unserialize($layout->getRight());
                        $right['promoBanner' . $data['promobanners_id']] = 'promoBanner' . $data['promobanners_id'];
                        $layout->setRight(serialize($right));
                    } else {
                        $left = unserialize($layout->getLeft());
                        $left['promoBanner' . $data['promobanners_id']] = 'promoBanner' . $data['promobanners_id'];
                        $layout->setLeft(serialize($left));
                    }
                    $layout->save();
                } else {
                    $layout = Mage::getModel('mpanel/layout');
                    $layout->setPageType('customer');
                    $layout->setIndentifier(0);
                    if (isset($data['template']) && $data['template'] == 'customer_right') {
                        $parentRight['promoBanner' . $data['promobanners_id']] = 'promoBanner' . $data['promobanners_id'];
                        $layout->setRight(serialize($parentRight));
                        if (count($parentLeft)) {
                            $layout->setLeft(serialize($parentLeft));
                        }
                    } else {
                        $parentLeft['promoBanner' . $data['promobanners_id']] = 'promoBanner' . $data['promobanners_id'];
                        $layout->setLeft(serialize($parentLeft));
                        if (count($parentRight)) {
                            $layout->setRight(serialize($parentRight));
                        }
                    }
                    $layout->save();
                }
            } catch (Exception $e) {
                
            }
        }
        $this->_redirect('customer/account/index');
        return;
    }

    public function promoInCategoryAction() {
        $this->_checkAccept();
        if ($data = $this->getRequest()->getPost()) {
            try {
                if ($data['chooser'] == 'new') {
                    if (isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
                        try {
                            /* Starting upload */
                            $uploader = new Varien_File_Uploader('filename');
                            // Any extention would work
                            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                            $uploader->setAllowRenameFiles(false);
                            // Set the file upload mode 
                            // false -> get the file directly in the specified folder
                            // true -> get the file in the product like folders 
                            //	(file.jpg will go in something like /media/f/i/file.jpg)
                            $uploader->setFilesDispersion(false);
                            // We set media as the upload dir
                            $path = Mage::getBaseDir('media') . DS . 'promobanners' . DS;
                            $uploader->save($path, $_FILES['filename']['name']);
                        } catch (Exception $e) {
                            
                        }
                        //this way the name is saved in DB
                        $data['filename'] = $_FILES['filename']['name'];
                    }
                    $model = Mage::getModel('promobanners/promobanners');
                    $model->setTitle($data['title']);
                    $model->setFilename($data['filename']);
                    $model->setStatus(1);
                    $model->setButton($data['button']);
                    $model->setUrl($data['url']);
                    $model->setContent($data['content']);
                    $model->save();
                    $promoId = $model->getId();
                } else {
                    $promoId = $data['exist_id'];
                }


                $currentCategoryId = $data['category_id'];
                $parentRight = array();
                $parentLeft = array();
                if (isset($data['product_id']) && isset($data['category_id'])) {
                    $layoutCollectionParent = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'product'))
                            ->addFieldToFilter('indentifier', array('eq' => 0));
                } else {
                    $layoutCollectionParent = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'category'))
                            ->addFieldToFilter('indentifier', array('eq' => 0));
                }
                if (count($layoutCollectionParent)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollectionParent->getFirstItem()->getId());
                    $parentRight = unserialize($layout->getRight());
                    $parentLeft = unserialize($layout->getLeft());
                }
                if (isset($data['product_id']) && isset($data['category_id'])) {
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'product'))
                            ->addFieldToFilter('indentifier', array('eq' => $data['product_id']));
                } else {
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'category'))
                            ->addFieldToFilter('indentifier', array('eq' => $currentCategoryId));
                }
                if (count($layoutCollection)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                    if (isset($data['position']) && $data['position'] == 'right') {
                        $right = unserialize($layout->getRight());
                        $right['promoBanner' . $promoId] = 'promoBanner' . $promoId;
                        //$finalRightArray = array_merge($parentRight, $right);
                        $finalRightArray = $right;
                        $layout->setRight(serialize($finalRightArray));
                    } else {
                        $left = unserialize($layout->getLeft());
                        $left['promoBanner' . $promoId] = 'promoBanner' . $promoId;
                        $finalLeftArray = array_merge($parentLeft, $left);
                        $layout->setLeft(serialize($finalLeftArray));
                    }
                    $layout->save();
                } else {
                    $layout = Mage::getModel('mpanel/layout');
                    if (isset($data['product_id']) && isset($data['category_id'])) {
                        $layout->setPageType('product');
                        $layout->setIndentifier($data['product_id']);
                    } else {
                        $layout->setPageType('category');
                        $layout->setIndentifier($currentCategoryId);
                    }
                    if (isset($data['position']) && $data['position'] == 'right') {
                        $parentRight['promoBanner' . $promoId] = 'promoBanner' . $promoId;
                        $layout->setRight(serialize($parentRight));
                        if (count($parentLeft)) {
                            $layout->setLeft(serialize($parentLeft));
                        }
                    } else {
                        $parentLeft['promoBanner' . $promoId] = 'promoBanner' . $promoId;
                        $layout->setLeft(serialize($parentLeft));
                        if (count($parentRight)) {
                            $layout->setRight(serialize($parentRight));
                        }
                    }
                    $layout->save();
                }
            } catch (Exception $e) {
                
            }
        }
        $d = $this->getRequest()->getPost();
        if (isset($d['category_id']) && isset($d['product_id']) && $d['category_id'] && $d['product_id']) {
            $oRewrite = Mage::getModel('core/url_rewrite')
                    ->setStoreId(Mage::app()->getStore()->getStoreId())
                    ->getCollection()
                    ->addFieldToFilter('category_id', array('eq' => $d['category_id']))
                    ->addFieldToFilter('product_id', array('eq' => $d['product_id']))
                    ->getFirstItem();
        } else {
            $oRewrite = Mage::getModel('core/url_rewrite')
                    ->setStoreId(Mage::app()->getStore()->getStoreId())
                    ->getCollection()
                    ->addFieldToFilter('category_id', array('eq' => $d['category_id']))
                    ->getFirstItem();
        }
        Mage::getSingleton('core/session')->setUrlRedirect(Mage::getBaseUrl() . $oRewrite->getData('request_path'));
        $this->_redirect('mpanel/index/after');
        return;
    }

    public function promoInCmsAction() {
        $this->_checkAccept();
        if ($data = $this->getRequest()->getPost()) {
            try {
                if ($data['chooser'] == 'new') {
                    if (isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
                        try {
                            /* Starting upload */
                            $uploader = new Varien_File_Uploader('filename');
                            // Any extention would work
                            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                            $uploader->setAllowRenameFiles(false);
                            // Set the file upload mode 
                            // false -> get the file directly in the specified folder
                            // true -> get the file in the product like folders 
                            //	(file.jpg will go in something like /media/f/i/file.jpg)
                            $uploader->setFilesDispersion(false);
                            // We set media as the upload dir
                            $path = Mage::getBaseDir('media') . DS . 'promobanners' . DS;
                            $uploader->save($path, $_FILES['filename']['name']);
                        } catch (Exception $e) {
                            
                        }
                        //this way the name is saved in DB
                        $data['filename'] = $_FILES['filename']['name'];
                    }
                    $model = Mage::getModel('promobanners/promobanners');
                    $model->setTitle($data['title']);
                    $model->setFilename($data['filename']);
                    $model->setStatus(1);
                    $model->setUrl($data['url']);
                    $model->setContent($data['content']);
                    $model->setButton($data['button']);
                    $model->save();

                    $promoId = $model->getId();
                } else {
                    $promoId = $data['exist_id'];
                }

                $pageId = $data['page_id'];
                $parentRight = array();
                $parentLeft = array();
                $layoutCollectionParent = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'cms'))
                        ->addFieldToFilter('indentifier', array('eq' => 0));
                if (count($layoutCollectionParent)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollectionParent->getFirstItem()->getId());
                    $parentRight = unserialize($layout->getRight());
                    $parentLeft = unserialize($layout->getLeft());
                }
                $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'cms'))
                        ->addFieldToFilter('indentifier', array('eq' => $pageId));
                if (count($layoutCollection)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                    if (isset($data['position']) && $data['position'] == 'right') {
                        $right = unserialize($layout->getRight());
                        $right['promoBanner' . $promoId] = 'promoBanner' . $promoId;
                        //$finalRightArray = array_merge($parentRight, $right);
                        $finalRightArray = $right;
                        $layout->setRight(serialize($finalRightArray));
                    } else {
                        $left = unserialize($layout->getLeft());
                        $left['promoBanner' . $promoId] = 'promoBanner' . $promoId;
                        $finalLeftArray = array_merge($parentLeft, $left);
                        $layout->setLeft(serialize($finalLeftArray));
                    }
                    $layout->save();
                } else {
                    $layout = Mage::getModel('mpanel/layout');
                    $layout->setPageType('cms');
                    $layout->setIndentifier($pageId);
                    if (isset($data['position']) && $data['position'] == 'right') {
                        $parentRight['promoBanner' . $promoId] = 'promoBanner' . $promoId;
                        $layout->setRight(serialize($parentRight));
                        if (count($parentLeft)) {
                            $layout->setLeft(serialize($parentLeft));
                        }
                    } else {
                        $parentLeft['promoBanner' . $promoId] = 'promoBanner' . $promoId;
                        $layout->setLeft(serialize($parentLeft));
                        if (count($parentRight)) {
                            $layout->setRight(serialize($parentRight));
                        }
                    }
                    $layout->save();
                }
            } catch (Exception $e) {
                
            }
        }
        $d = $this->getRequest()->getPost();
        $cmsPage = Mage::getModel('cms/page')->load($d['page_id']);
        Mage::getSingleton('core/session')->setUrlRedirect(Mage::getBaseUrl() . $cmsPage->getData('identifier'));
        $this->_redirect('mpanel/index/after');
        return;
    }

    public function promoInCustomerAction() {
        $this->_checkAccept();
        if ($data = $this->getRequest()->getPost()) {
            try {
                if ($data['chooser'] == 'new') {
                    if (isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
                        try {
                            /* Starting upload */
                            $uploader = new Varien_File_Uploader('filename');
                            // Any extention would work
                            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                            $uploader->setAllowRenameFiles(false);
                            // Set the file upload mode 
                            // false -> get the file directly in the specified folder
                            // true -> get the file in the product like folders 
                            //	(file.jpg will go in something like /media/f/i/file.jpg)
                            $uploader->setFilesDispersion(false);
                            // We set media as the upload dir
                            $path = Mage::getBaseDir('media') . DS . 'promobanners' . DS;
                            $uploader->save($path, $_FILES['filename']['name']);
                        } catch (Exception $e) {
                            
                        }
                        //this way the name is saved in DB
                        $data['filename'] = $_FILES['filename']['name'];
                    }
                    $model = Mage::getModel('promobanners/promobanners');
                    $model->setTitle($data['title']);
                    $model->setFilename($data['filename']);
                    $model->setStatus(1);
                    $model->setUrl($data['url']);
                    $model->setContent($data['content']);
                    $model->setButton($data['button']);
                    $model->save();

                    $promoId = $model->getId();
                } else {
                    $promoId = $data['exist_id'];
                }

                $parentRight = array();
                $parentLeft = array();
                $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'customer'))
                        ->addFieldToFilter('indentifier', array('eq' => 0));
                if (count($layoutCollection)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                    if (isset($data['position']) && $data['position'] == 'right') {
                        $right = unserialize($layout->getRight());
                        $right['promoBanner' . $promoId] = 'promoBanner' . $promoId;
                        $layout->setRight(serialize($right));
                    } else {
                        $left = unserialize($layout->getLeft());
                        $left['promoBanner' . $promoId] = 'promoBanner' . $promoId;
                        $layout->setLeft(serialize($left));
                    }
                    $layout->save();
                } else {
                    $layout = Mage::getModel('mpanel/layout');
                    $layout->setPageType('customer');
                    $layout->setIndentifier(0);
                    if (isset($data['position']) && $data['position'] == 'right') {
                        $parentRight['promoBanner' . $promoId] = 'promoBanner' . $promoId;
                        $layout->setRight(serialize($parentRight));
                        if (count($parentLeft)) {
                            $layout->setLeft(serialize($parentLeft));
                        }
                    } else {
                        $parentLeft['promoBanner' . $promoId] = 'promoBanner' . $promoId;
                        $layout->setLeft(serialize($parentLeft));
                        if (count($parentRight)) {
                            $layout->setRight(serialize($parentRight));
                        }
                    }
                    $layout->save();
                }
            } catch (Exception $e) {
                
            }
        }
        $this->_redirect('mpanel/index/redirectInCustomer');
        return;
    }

    public function validateData($data) {
        $storeId = Mage::app()->getStore()->getId();
        $data['store_id'] = $storeId;
		
		$type = $data['type'];
		$cmsBlock = array('content'=>'');

        //Data of cms page
		if($type == 'static'){
			if (isset($data['static_block_id']) && ($data['static_block_id'] != '')) {
				$staticBlockModel = Mage::getModel('cms/block')->load($data['static_block_id']);
				$cmsBlock = $staticBlockModel->getData();
			} else {
				$title = $data['title'];

				$cmsBlock = array(
					'title' => $title,
					'identifier' => 'mgs_panel_' . $data['home_name'] . '_' . $data['block_name'] . '_' . Mage::helper('mpanel')->getNewPositionOfChild($storeId, $data['block_name'], $data['home_name']),
					'is_active' => 1,
					'stores' => array($storeId)
				);
			}
		}

       

        //Block type
        switch ($type) {
            // Text & Image
            case 'static':
                //$data['content'] = str_replace('<img', '<img class="img-responsive"', $data['content']);
                $cmsBlock['content'] = $data['content'];
                $data['setting'] = json_encode($data['setting']);
                break;

            case 'duplicate_static':
                $cmsBlock['content'] = '';
                $data['setting'] = json_encode($data['setting']);
                break;

            // Category Navigation
            case 'category':
                $cmsBlock['content'] = '{{block type="mpanel/navigation" template="mgs/mpanel/template/navigation.phtml" title="' . $data['setting']['title'] . '"}}';
                $data['setting'] = json_encode($data['setting']);
                break;

            // Category Product
            case 'category_products':
                $categoryIds = implode(',', $data['setting']['category_id']);
				$cmsBlock['content'] = '{{block type="mpanel/products" template="mgs/mpanel/products/category_products/'.$data['setting']['view_mode'].'"';
				$cmsBlock['content'] .= ' title="' . $data['setting']['title'] . '" products_count="' . $data['setting']['products_count'] . '" column="' . $data['setting']['count_per_row'] . '" category_ids="' . $categoryIds . '"';
				$cmsBlock['content'] .= '}}';
                $data['setting'] = json_encode($data['setting']);
                break;

            // Testimonials
            case 'testimonials':
                $cmsBlock['content'] = '{{block type="testimonial/testimonial" template="mgs/testimonial/view.phtml" view_mode="'.$data['setting']['view_mode'].'" title="'.$data['setting']['title'].'" item_count="'.$data['setting']['item_count'].'"}}';
				$data['setting'] = json_encode($data['setting']);
                break;

            // Revolution slider
            case 'revolution':
                $cmsBlock['content'] = '{{widget type="revslider/slider_preview" id="' . $data['content'] . '"}}';
                $data['setting'] = $data['content'];
                Mage::getSingleton('core/session')->setSaved(1);
                break;

            // New Products
            case 'new_products':
                if ($data['setting']['view_mode'] == 'grid') {
                    $cmsBlock['content'] = '{{block type="mpanel/product_new" display_type="new_products" products_count="' . $data['setting']['product_count'] . '" slider="' . $data['setting']['slider']['active'] . '" count_per_row="' . $data['setting']['count_per_row'] . '" title="' . $data['setting']['title'] . '"';
                    if ($data['setting']['slider']['active'] == 1) {
                        $cmsBlock['content'] .= ' auto_play="' . $data['setting']['slider']['auto'] . '" stop_hover="' . $data['setting']['slider']['stop_hover'] . '" navigation="' . $data['setting']['slider']['navigation'] . '" pagination="' . $data['setting']['slider']['pagination'] . '"';
                    } else {
                        $cmsBlock['content'] .= ' load_more="' . $data['setting']['load_more'] . '"';
                    }
                    $cmsBlock['content'] .= ' template="mgs/mpanel/products/new_products.phtml"}}';
                } else {
                    $cmsBlock['content'] = '{{block type="mpanel/product_new" products_count="' . $data['setting']['product_count'] . '" title="' . $data['setting']['title'] . '" template="mgs/mpanel/products/list/new_products.phtml"}}';
                }
                $data['setting'] = json_encode($data['setting']);
                break;

            // Featured Products
            case 'featured_products':
                if ($data['setting']['view_mode'] == 'grid') {
                    $cmsBlock['content'] = '{{block type="mpanel/products" products_count="' . $data['setting']['product_count'] . '" slider="' . $data['setting']['slider']['active'] . '" count_per_row="' . $data['setting']['count_per_row'] . '" title="' . $data['setting']['title'] . '"';
                    if ($data['setting']['slider']['active'] == 1) {
                        $cmsBlock['content'] .= ' auto_play="' . $data['setting']['slider']['auto'] . '" stop_hover="' . $data['setting']['slider']['stop_hover'] . '" navigation="' . $data['setting']['slider']['navigation'] . '" pagination="' . $data['setting']['slider']['pagination'] . '"';
                    } else {
                        $cmsBlock['content'] .= ' load_more="' . $data['setting']['load_more'] . '"';
                    }
                    $cmsBlock['content'] .= ' template="mgs/mpanel/products/featured_products.phtml"}}';
                } else {
                    $cmsBlock['content'] = '{{block type="mpanel/products" products_count="' . $data['setting']['product_count'] . '" title="' . $data['setting']['title'] . '" template="mgs/mpanel/products/list/featured_products.phtml"}}';
                }


                $data['setting'] = json_encode($data['setting']);
                break;

            // Best Selling Products
            case 'hot':
                if ($data['setting']['view_mode'] == 'grid') {
                    $cmsBlock['content'] = '{{block type="mpanel/products" products_count="' . $data['setting']['product_count'] . '" slider="' . $data['setting']['slider']['active'] . '" count_per_row="' . $data['setting']['count_per_row'] . '" title="' . $data['setting']['title'] . '"';
                    if ($data['setting']['slider']['active'] == 1) {
                        $cmsBlock['content'] .= ' auto_play="' . $data['setting']['slider']['auto'] . '" stop_hover="' . $data['setting']['slider']['stop_hover'] . '" navigation="' . $data['setting']['slider']['navigation'] . '" pagination="' . $data['setting']['slider']['pagination'] . '"';
                    } else {
                        $cmsBlock['content'] .= ' load_more="' . $data['setting']['load_more'] . '"';
                    }
                    $cmsBlock['content'] .= ' template="mgs/mpanel/products/hot_products.phtml"}}';
                } else {
                    $cmsBlock['content'] = '{{block type="mpanel/products" products_count="' . $data['setting']['product_count'] . '" title="' . $data['setting']['title'] . '" template="mgs/mpanel/products/list/hot_products.phtml"}}';
                }

                $data['setting'] = json_encode($data['setting']);
                break;

            // Featured Brands
            case 'brands':
                $cmsBlock['content'] = '{{block type="abrands/abrands" slider="' . $data['setting']['slider']['active'] . '" title="' . $data['setting']['title'] . '" brand_count="'.$data['setting']['brand_count'].'"';
                if ($data['setting']['slider']['active'] == 1) {
                    $cmsBlock['content'] .= ' auto_play="' . $data['setting']['slider']['auto'] . '" stop_hover="' . $data['setting']['slider']['stop_hover'] . '" navigation="' . $data['setting']['slider']['navigation'] . '" pagination="' . $data['setting']['slider']['pagination'] . '"';
                }
                $cmsBlock['content'] .= ' template="mgs/abrands/brands.phtml"}}';
                $data['setting'] = json_encode($data['setting']);
                break;

            // Promo Banners
            case 'promo_banner':
                if ((isset($data['chooser']) && $data['chooser'] == 'new') || (!isset($data['chooser']))) {
                    $data['status'] = 1;
                    $model = Mage::getModel('promobanners/promobanners');
                    $model->setData($data);
                    if (isset($data['banner_id'])) {
                        $model->setId($data['banner_id']);
                    }
                    $model->save();
                    $bannerId = $model->getId();
                } else {
                    if (isset($data['exist_id'])) {
                        $bannerId = $data['exist_id'];
                    }
                }
                $data['setting']['banner_id'] = $bannerId;
                $data['setting'] = json_encode($data['setting']);
                Mage::getSingleton('core/session')->setSaved(1);

                $cmsBlock['content'] = '{{block type="promobanners/promobanners" banner_id="' . $bannerId . '" template="mgs/promobanners/banner.phtml"}}';
                break;

            // Deals
            case 'deals':
                $deal = implode(',', $data['setting']['deal']);
                if ($data['setting']['slider']['active'] == 1) {
                    $cmsBlock['content'] = '{{widget type="deals/widget" template="mgs/deals/widget/slide.phtml" column="' . $data['setting']['count_per_row'] . '" real_deal="' . $deal . '" title="' . $data['setting']['title'] . '" auto_play="' . $data['setting']['slider']['auto'] . '" stop_hover="' . $data['setting']['slider']['stop_hover'] . '" navigation="' . $data['setting']['slider']['navigation'] . '" pagination="' . $data['setting']['slider']['pagination'] . '"}}';
                } else {
                    $cmsBlock['content'] = '{{widget type="deals/widget" template="mgs/deals/widget/grid.phtml" column="' . $data['setting']['count_per_row'] . '" real_deal="' . $deal . '" title="' . $data['setting']['title'] . '"}}';
                }
                $data['setting'] = json_encode($data['setting']);
                break;

            // Products Tab
            case 'tabs':
                $tabs = implode(',', $data['setting']['product_tab']);

                $cmsBlock['content'] = '{{block type="core/template" products_count="' . $data['setting']['product_count'] . '" slider="' . $data['setting']['slider']['active'] . '" count_per_row="' . $data['setting']['count_per_row'] . '" tabs="' . $tabs . '"';
                if ($data['setting']['slider']['active'] == 1) {
                    $cmsBlock['content'] .= ' auto_play="' . $data['setting']['slider']['auto'] . '" stop_hover="' . $data['setting']['slider']['stop_hover'] . '" navigation="' . $data['setting']['slider']['navigation'] . '" pagination="' . $data['setting']['slider']['pagination'] . '"';
                }
                $cmsBlock['content'] .= ' template="mgs/mpanel/tabs.phtml"}}';

                $data['setting'] = json_encode($data['setting']);
                break;

            // Latest Posts
            case 'latest_posts':
                $post = implode(',', $data['setting']['categories']);

                $cmsBlock['content'] = '{{widget type="blog/last" blog_count="' . $data['setting']['product_count'] . '" categories="' . $post . '" column="' . $data['setting']['count_per_row'] . '" view_mode="' . $data['setting']['view_mode'] . '" slider="' . $data['setting']['slider']['active'] . '" title="'.$data['setting']['title'].'"';

                if ($data['setting']['slider']['active'] == 1) {
                    $cmsBlock['content'] .= ' auto_play="' . $data['setting']['slider']['auto'] . '" stop_hover="' . $data['setting']['slider']['stop_hover'] . '" navigation="' . $data['setting']['slider']['navigation'] . '" pagination="' . $data['setting']['slider']['pagination'] . '"';
                }

                $cmsBlock['content'] .= '}}';

                $data['setting'] = json_encode($data['setting']);
                break;
        }

        $data['static_block'] = $cmsBlock;
		
		$data['block_content'] = $cmsBlock['content'];

        return $data;
    }

    // sort child block
    public function sortAction() {
        $this->_checkAccept();
        if (($data = $this->getRequest()->getPost()) && ($element = $this->getRequest()->getParam('el'))) {
            if (isset($data[$element]) && count($data[$element]) > 0) {
                foreach ($data[$element] as $position => $childId) {
                    $sortOrder = $position + 1;
                    try {
                        Mage::getModel('mpanel/childs')->setPosition($sortOrder)->setId($childId)->save();
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }
        }
    }

    // sort child block
    public function sortInCategoryAction() {
        $this->_checkAccept();
        if (($data = $this->getRequest()->getPost()) && ($element = $this->getRequest()->getParam('el'))) {
            $data1 = $this->getRequest()->getParams();
            if (isset($data[$element]) && count($data[$element]) > 0) {
                if (isset($data1['product_id']) && isset($data1['category_id'])) {
                    $collection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'product'))
                            ->addFieldToFilter('indentifier', array('eq' => $data1['product_id']));
                } else {
                    $collection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'category'))
                            ->addFieldToFilter('indentifier', array('eq' => $data1['category_id']));
                }
                if (count($collection)) {
                    $currentCategoryId = $this->getRequest()->getParam('category_id');
                    if (isset($data1['product_id']) && isset($data1['category_id'])) {
                        $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'product'))
                                ->addFieldToFilter('indentifier', array('eq' => $data1['product_id']));
                    } else {
                        $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'category'))
                                ->addFieldToFilter('indentifier', array('eq' => $currentCategoryId));
                    }
                    if (count($layoutCollection)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                        if ($this->getRequest()->getParam('position') == 'right') {
                            $right = unserialize($layout->getRight());
                            $finalRight = array();
                            foreach ($data[$element] as $e) {
                                foreach ($right as $key => $value) {
                                    if ($e == $value) {
                                        $finalRight[$key] = $e;
                                    }
                                }
                            }
                            $layout->setRight(serialize($finalRight));
                        } else {
                            $left = unserialize($layout->getLeft());
                            $finalLeft = array();
                            foreach ($data[$element] as $e) {
                                foreach ($left as $key => $value) {
                                    if ($e == $value) {
                                        $finalLeft[$key] = $e;
                                    }
                                }
                            }
                            $layout->setLeft(serialize($finalLeft));
                        }
                        $layout->save();
                    }
                } else {
                    $currentCategoryId = 0;
                    if (isset($data1['product_id']) && isset($data1['category_id'])) {
                        $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'product'))
                                ->addFieldToFilter('indentifier', array('eq' => $data1['product_id']));
                    } else {
                        $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'category'))
                                ->addFieldToFilter('indentifier', array('eq' => $currentCategoryId));
                    }
                    if (count($layoutCollection)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                        if ($this->getRequest()->getParam('position') == 'right') {
                            $right = unserialize($layout->getRight());
                            $finalRight = array();
                            foreach ($data[$element] as $e) {
                                foreach ($right as $key => $value) {
                                    if ($e == $value) {
                                        $finalRight[$key] = $e;
                                    }
                                }
                            }
                            $layout->setRight(serialize($finalRight));
                        } else {
                            $left = unserialize($layout->getLeft());
                            $finalLeft = array();
                            foreach ($data[$element] as $e) {
                                foreach ($left as $key => $value) {
                                    if ($e == $value) {
                                        $finalLeft[$key] = $e;
                                    }
                                }
                            }
                            $layout->setLeft(serialize($finalLeft));
                        }
                        $layout->save();
                    }
                }
            }
        }
    }

    // sort child block
    public function sortInCmsAction() {
        $this->_checkAccept();
        if (($data = $this->getRequest()->getPost()) && ($element = $this->getRequest()->getParam('el'))) {
            $data1 = $this->getRequest()->getParams();
            if (isset($data[$element]) && count($data[$element]) > 0) {
                $collection = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'cms'))
                        ->addFieldToFilter('indentifier', array('eq' => $data1['page_id']));
                if (count($collection)) {
                    $pageId = $this->getRequest()->getParam('page_id');
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'cms'))
                            ->addFieldToFilter('indentifier', array('eq' => $pageId));
                    if (count($layoutCollection)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                        if ($this->getRequest()->getParam('position') == 'right') {
                            $right = unserialize($layout->getRight());
                            $finalRight = array();
                            foreach ($data[$element] as $e) {
                                foreach ($right as $key => $value) {
                                    if ($e == $value) {
                                        $finalRight[$key] = $e;
                                    }
                                }
                            }
                            $layout->setRight(serialize($finalRight));
                        } else {
                            $left = unserialize($layout->getLeft());
                            $finalLeft = array();
                            foreach ($data[$element] as $e) {
                                foreach ($left as $key => $value) {
                                    if ($e == $value) {
                                        $finalLeft[$key] = $e;
                                    }
                                }
                            }
                            $layout->setLeft(serialize($finalLeft));
                        }
                        $layout->save();
                    }
                } else {
                    $pageId = 0;
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'cms'))
                            ->addFieldToFilter('indentifier', array('eq' => $pageId));
                    if (count($layoutCollection)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                        if ($this->getRequest()->getParam('position') == 'right') {
                            $right = unserialize($layout->getRight());
                            $finalRight = array();
                            foreach ($data[$element] as $e) {
                                foreach ($right as $key => $value) {
                                    if ($e == $value) {
                                        $finalRight[$key] = $e;
                                    }
                                }
                            }
                            $layout->setRight(serialize($finalRight));
                        } else {
                            $left = unserialize($layout->getLeft());
                            $finalLeft = array();
                            foreach ($data[$element] as $e) {
                                foreach ($left as $key => $value) {
                                    if ($e == $value) {
                                        $finalLeft[$key] = $e;
                                    }
                                }
                            }
                            $layout->setLeft(serialize($finalLeft));
                        }
                        $layout->save();
                    }
                }
            }
        }
    }

    // sort child block
    public function sortInCustomerAction() {
        $this->_checkAccept();
        if (($data = $this->getRequest()->getPost()) && ($element = $this->getRequest()->getParam('el'))) {
            if (isset($data[$element]) && count($data[$element]) > 0) {
                $collection = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'customer'))
                        ->addFieldToFilter('indentifier', array('eq' => 0));
                if (count($collection)) {
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'customer'))
                            ->addFieldToFilter('indentifier', array('eq' => 0));
                    if (count($layoutCollection)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                        if ($this->getRequest()->getParam('position') == 'right') {
                            $right = unserialize($layout->getRight());
                            $finalRight = array();
                            foreach ($data[$element] as $e) {
                                foreach ($right as $key => $value) {
                                    if ($e == $value) {
                                        $finalRight[$key] = $e;
                                    }
                                }
                            }
                            $layout->setRight(serialize($finalRight));
                        } else {
                            $left = unserialize($layout->getLeft());
                            $finalLeft = array();
                            foreach ($data[$element] as $e) {
                                foreach ($left as $key => $value) {
                                    if ($e == $value) {
                                        $finalLeft[$key] = $e;
                                    }
                                }
                            }
                            $layout->setLeft(serialize($finalLeft));
                        }
                        $layout->save();
                    }
                }
            }
        }
    }

    // delete child block
    public function deleteAction() {
        $this->_checkAccept();
        if ($id = $this->getRequest()->getParam('id')) {
            $child = Mage::getModel('mpanel/childs')->load($id);

            try {
                if ($child->getType() != 'separator') {
                    $staticBlockId = $child->getStaticBlockId();


                    if ($child->getType() != 'static') {
                        Mage::getModel('cms/block')->setId($staticBlockId)->delete();
                    } else {
                        $collection = Mage::getModel('mpanel/childs')
                                ->getCollection()
                                ->addFieldToFilter('static_block_id', $staticBlockId);
                        if (count($collection) == 1) {
                            Mage::getModel('cms/block')->setId($staticBlockId)->delete();
                        }
                    }
                }

                Mage::getModel('mpanel/childs')->setId($id)->delete();
                Mage::getSingleton('core/session')->addSuccess(Mage::helper('mpanel')->__('Block was successfully deleted.'));
            } catch (Exception $e) {
                
            }
        }

        $this->_redirectUrl(Mage::getBaseUrl());
        return;
    }

    // delete child block
    public function deleteInCategoryAction() {
        $this->_checkAccept();
        if ($data = $this->getRequest()->getParams()) {
            try {
                $cmsBlock = Mage::getModel('cms/block')->load($data['id']);
                $currentCategoryId = $data['category_id'];
                if (isset($data['product_id']) && isset($data['category_id'])) {
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'product'))
                            ->addFieldToFilter('indentifier', array('eq' => $data['product_id']));
                } else {
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'category'))
                            ->addFieldToFilter('indentifier', array('eq' => $currentCategoryId));
                }
                if (count($layoutCollection)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                    if ($layout->getRight() != null) {
                        $right = unserialize($layout->getRight());
                        unset($right[$cmsBlock->getData('identifier')]);
                        $layout->setRight(serialize($right));
                    }
                    if ($layout->getLeft() != null) {
                        $left = unserialize($layout->getLeft());
                        unset($left[$cmsBlock->getData('identifier')]);
                        $layout->setLeft(serialize($left));
                    }
                    $layout->save();
                }
                $cmsBlock->delete();
            } catch (Exception $e) {
                
            }
        }
        if ($this->getRequest()->getParam('type') == 'core') {
            $data = $this->getRequest()->getParams();
            try {
                if (isset($data['product_id']) && isset($data['category_id'])) {
                    $collection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'product'))
                            ->addFieldToFilter('indentifier', array('eq' => $data['product_id']));
                } else {
                    $collection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'category'))
                            ->addFieldToFilter('indentifier', array('eq' => $data['category_id']));
                }
                if (count($collection)) {
                    $currentCategoryId = $data['category_id'];
                    if (isset($data['product_id']) && isset($data['category_id'])) {
                        $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'product'))
                                ->addFieldToFilter('indentifier', array('eq' => $data['product_id']));
                    } else {
                        $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'category'))
                                ->addFieldToFilter('indentifier', array('eq' => $currentCategoryId));
                    }
                    if (count($layoutCollection)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                        if ($layout->getRight() != null) {
                            if ($this->getRequest()->getParam('template') == 'category_right') {
                                $right = unserialize($layout->getRight());
                                unset($right[$this->getRequest()->getParam('block')]);
                                $layout->setRight(serialize($right));
                            }
                        }
                        if ($layout->getLeft() != null) {
                            if ($this->getRequest()->getParam('template') == 'category_left') {
                                $left = unserialize($layout->getLeft());
                                unset($left[$this->getRequest()->getParam('block')]);
                                $layout->setLeft(serialize($left));
                            }
                        }
                        $layout->save();
                    }
                } else {
                    $currentCategoryId = 0;
                    $currentProductId = 0;
                    if (isset($data['product_id']) && isset($data['category_id'])) {
                        $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'product'))
                                ->addFieldToFilter('indentifier', array('eq' => $currentProductId));
                    } else {
                        $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'category'))
                                ->addFieldToFilter('indentifier', array('eq' => $currentCategoryId));
                    }
                    if (count($layoutCollection)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                        if ($layout->getRight() != null) {
                            if ($this->getRequest()->getParam('template') == 'category_right') {
                                $right = unserialize($layout->getRight());
                                unset($right[$this->getRequest()->getParam('block')]);
                                $layout->setRight(serialize($right));
                            }
                        }
                        if ($layout->getLeft() != null) {
                            if ($this->getRequest()->getParam('template') == 'category_left') {
                                $left = unserialize($layout->getLeft());
                                unset($left[$this->getRequest()->getParam('block')]);
                                $layout->setLeft(serialize($left));
                            }
                        }
                        $layout->save();
                    }
                }
            } catch (Exception $e) {
                
            }
        }
        $this->_redirectReferer();
        return;
    }

    // delete child block
    public function deleteInCmsAction() {
        $this->_checkAccept();
        if ($data = $this->getRequest()->getParams()) {
            try {
                $cmsBlock = Mage::getModel('cms/block')->load($data['id']);
                $pageId = $data['page_id'];
                $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'cms'))
                        ->addFieldToFilter('indentifier', array('eq' => $pageId));
                if (count($layoutCollection)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                    if ($layout->getRight() != null) {
                        $right = unserialize($layout->getRight());
                        unset($right[$cmsBlock->getData('identifier')]);
                        $layout->setRight(serialize($right));
                    }
                    if ($layout->getLeft() != null) {
                        $left = unserialize($layout->getLeft());
                        unset($left[$cmsBlock->getData('identifier')]);
                        $layout->setLeft(serialize($left));
                    }
                    $layout->save();
                }
                $cmsBlock->delete();
            } catch (Exception $e) {
                
            }
        }
        if ($this->getRequest()->getParam('type') == 'core') {
            $data = $this->getRequest()->getParams();
            try {
                $collection = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'cms'))
                        ->addFieldToFilter('indentifier', array('eq' => $data['page_id']));
                if (count($collection)) {
                    $pageId = $data['page_id'];
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'cms'))
                            ->addFieldToFilter('indentifier', array('eq' => $pageId));
                    if (count($layoutCollection)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                        if ($layout->getRight() != null) {
                            if ($this->getRequest()->getParam('template') == 'cms_right') {
                                $right = unserialize($layout->getRight());
                                unset($right[$this->getRequest()->getParam('block')]);
                                $layout->setRight(serialize($right));
                            }
                        }
                        if ($layout->getLeft() != null) {
                            if ($this->getRequest()->getParam('template') == 'cms_left') {
                                $left = unserialize($layout->getLeft());
                                unset($left[$this->getRequest()->getParam('block')]);
                                $layout->setLeft(serialize($left));
                            }
                        }
                        $layout->save();
                    }
                } else {
                    $pageId = 0;
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'cms'))
                            ->addFieldToFilter('indentifier', array('eq' => $pageId));
                    if (count($layoutCollection)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                        if ($layout->getRight() != null) {
                            if ($this->getRequest()->getParam('template') == 'cms_right') {
                                $right = unserialize($layout->getRight());
                                unset($right[$this->getRequest()->getParam('block')]);
                                $layout->setRight(serialize($right));
                            }
                        }
                        if ($layout->getLeft() != null) {
                            if ($this->getRequest()->getParam('template') == 'cms_left') {
                                $left = unserialize($layout->getLeft());
                                unset($left[$this->getRequest()->getParam('block')]);
                                $layout->setLeft(serialize($left));
                            }
                        }
                        $layout->save();
                    }
                }
            } catch (Exception $e) {
                
            }
        }
        $this->_redirectReferer();
        return;
    }

    // delete child block
    public function deleteInCustomerAction() {
        $this->_checkAccept();
        if ($data = $this->getRequest()->getParams()) {
            try {
                $cmsBlock = Mage::getModel('cms/block')->load($data['id']);
                $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'customer'))
                        ->addFieldToFilter('indentifier', array('eq' => 0));
                if (count($layoutCollection)) {
                    $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                    if ($layout->getRight() != null) {
                        $right = unserialize($layout->getRight());
                        unset($right[$cmsBlock->getData('identifier')]);
                        $layout->setRight(serialize($right));
                    }
                    if ($layout->getLeft() != null) {
                        $left = unserialize($layout->getLeft());
                        unset($left[$cmsBlock->getData('identifier')]);
                        $layout->setLeft(serialize($left));
                    }
                    $layout->save();
                }
                $cmsBlock->delete();
            } catch (Exception $e) {
                
            }
        }
        if ($this->getRequest()->getParam('type') == 'core') {
            $data = $this->getRequest()->getParams();
            try {
                $collection = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'customer'))
                        ->addFieldToFilter('indentifier', array('eq' => 0));
                if (count($collection)) {
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'customer'))
                            ->addFieldToFilter('indentifier', array('eq' => 0));
                    if (count($layoutCollection)) {
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                        if ($layout->getRight() != null) {
                            if ($this->getRequest()->getParam('template') == 'customer_right') {
                                $right = unserialize($layout->getRight());
                                unset($right[$this->getRequest()->getParam('block')]);
                                $layout->setRight(serialize($right));
                            }
                        }
                        if ($layout->getLeft() != null) {
                            if ($this->getRequest()->getParam('template') == 'customer_left') {
                                $left = unserialize($layout->getLeft());
                                unset($left[$this->getRequest()->getParam('block')]);
                                $layout->setLeft(serialize($left));
                            }
                        }
                        $layout->save();
                    }
                }
            } catch (Exception $e) {
                
            }
        }
        $this->_redirectReferer();
        return;
    }

    public function applyToAllAction() {
        $this->_checkAccept();
        if ($data = $this->getRequest()->getParams()) {
            try {
                $currentCategoryId = $data['category_id'];
                if (isset($data['product_id']) && isset($data['category_id'])) {
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'product'))
                            ->addFieldToFilter('indentifier', array('eq' => $data['product_id']));
                } else {
                    $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'category'))
                            ->addFieldToFilter('indentifier', array('eq' => $currentCategoryId));
                }
                if (count($layoutCollection)) {
                    if (isset($data['product_id']) && isset($data['category_id'])) {
                        $temp = $layoutCollection->getFirstItem();
                        $layoutCollectionDelete = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'product'))
                                ->addFieldToFilter('indentifier', array('eq' => 0));
                        foreach ($layoutCollectionDelete as $record) {
                            $l = Mage::getModel('mpanel/layout')->load($record->getId());
                            $l->delete();
                        }
                        $layoutCollectionDelete = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'product'))
                                ->addFieldToFilter('indentifier', array('eq' => $data['product_id']));
                        foreach ($layoutCollectionDelete as $record) {
                            $l = Mage::getModel('mpanel/layout')->load($record->getId());
                            $l->delete();
                        }
                        $layout = Mage::getModel('mpanel/layout');
                        $layout->setPageType('product');
                        $layout->setIndentifier(0);
                        $layout->setLeft($temp->getLeft());
                        $layout->setRight($temp->getRight());
                        $layout->save();
                    } else {
                        $layoutCollectionDelete = Mage::getModel('mpanel/layout')->getCollection()
                                ->addFieldToFilter('page_type', array('eq' => 'category'))
                                ->addFieldToFilter('indentifier', array('eq' => 0));
                        foreach ($layoutCollectionDelete as $record) {
                            $l = Mage::getModel('mpanel/layout')->load($record->getId());
                            $l->delete();
                        }
                        $layout = Mage::getModel('mpanel/layout')->load($layoutCollection->getFirstItem()->getId());
                        $layout->setIndentifier(0);
                        $layout->save();
                    }
                }
            } catch (Exception $ex) {
                
            }
        }
        $this->_redirectReferer();
        return;
    }

    public function applyToAllInCmsAction() {
        $this->_checkAccept();
        if ($data = $this->getRequest()->getParams()) {
            try {
                $pageId = $data['page_id'];
                $layoutCollection = Mage::getModel('mpanel/layout')->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'cms'))
                        ->addFieldToFilter('indentifier', array('eq' => $pageId));
                if (count($layoutCollection)) {
                    $temp = $layoutCollection->getFirstItem();
                    $layoutCollectionDelete = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'cms'))
                            ->addFieldToFilter('indentifier', array('eq' => 0));
                    foreach ($layoutCollectionDelete as $record) {
                        $l = Mage::getModel('mpanel/layout')->load($record->getId());
                        $l->delete();
                    }
                    $layoutCollectionDelete = Mage::getModel('mpanel/layout')->getCollection()
                            ->addFieldToFilter('page_type', array('eq' => 'cms'))
                            ->addFieldToFilter('indentifier', array('eq' => $pageId));
                    foreach ($layoutCollectionDelete as $record) {
                        $l = Mage::getModel('mpanel/layout')->load($record->getId());
                        $l->delete();
                    }
                    $layout = Mage::getModel('mpanel/layout');
                    $layout->setPageType('cms');
                    $layout->setIndentifier(0);
                    $layout->setLeft($temp->getLeft());
                    $layout->setRight($temp->getRight());
                    $layout->save();
                }
            } catch (Exception $ex) {
                
            }
        }
        $this->_redirectReferer();
        return;
    }

}
