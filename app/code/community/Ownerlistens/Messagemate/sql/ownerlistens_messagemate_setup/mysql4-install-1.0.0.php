<?php
$installer = $this;

$installer->startSetup();

$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
$charactersLength = strlen($characters);
$randomString = '';
for ($i = 0; $i < 15; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
}

$setup = new Mage_Core_Model_Config();
Mage::getModel('core/config')->saveConfig('ownerlistens/config/mag_id', $randomString );

$installer->endSetup();