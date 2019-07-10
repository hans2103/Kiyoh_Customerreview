<?php
/**
 * My own options
 *
 */
class Kiyoh_Customerreview_Adminhtml_Model_System_Config_Source_Reviewservernew
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'klantenvertellen.nl', 'label'=>Mage::helper('adminhtml')->__('klantenvertellen.nl')),
            array('value' => 'newkiyoh.com', 'label'=>Mage::helper('adminhtml')->__('Kiyoh.com (International)')),

        );
    }

}
