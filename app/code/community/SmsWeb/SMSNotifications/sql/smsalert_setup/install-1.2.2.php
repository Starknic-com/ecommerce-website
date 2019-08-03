<?php
/** @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('sales/order_status_history');
if ($installer->getConnection()->isTableExists($tableName) == true) {
    $installer->getConnection()
        ->addColumn(
            $tableName,
            'is_customer_sms_notified',
            array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => 0,
                'comment'   => 'Is Customer SMS Notified'
            )
        );
}

$installer->endSetup();