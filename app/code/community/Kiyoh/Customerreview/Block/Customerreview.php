<?php

class Kiyoh_Customerreview_Block_Customerreview extends Mage_Core_Block_Template
{
    protected $mcrdata;

    public function _prepareLayout()
    {
        $cache = Mage::app()->getCache();
        $this->mcrdata = unserialize($cache->load('kiyoh_customerreview_microdata'));

        if(!$this->mcrdata){
            $this->mcrdata = $this->receiveData();
        }

        if(isset($this->mcrdata['company']['total_score'])){
            $cache->save(serialize($this->mcrdata),'kiyoh_customerreview_microdata',array(),3600);
            $this->setCorrectData(1);
        }

        return parent::_prepareLayout();
    }

    public function getCustomerreview()
    {
        if (!$this->hasData('customerreview')) {
            $this->setData('customerreview', Mage::registry('customerreview'));
        }
        return $this->getData('customerreview');

    }
    public function receiveData()
    {
        libxml_use_internal_errors(true);
        $kiyoh_connector = Mage::getStoreConfig('customconfig/review_group/custom_connector');
        $company_id = Mage::getStoreConfig('customconfig/review_group/company_id');
        $kiyoh_server = Mage::getStoreConfig('customconfig/review_group/custom_server');

        $file = 'https://www.'.$kiyoh_server.'/xml/recent_company_reviews.xml?connectorcode=' . $kiyoh_connector . '&company_id=' . $company_id;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        curl_close($ch);
        $doc = simplexml_load_string($output);
        $data = array();

        if ($doc) {
            $data = Mage::helper('core/data')->jsonDecode(Mage::helper('core/data')->jsonEncode($doc));

            if (isset($data['error']))
            {
                $this->logErrors($data['error']);
            }

        } else {
            $this->logErrors(libxml_get_errors(), null, 'kiyoh.log');
        }

        return $data;
    }

    public function logErrors($info) {
        if(Mage::getStoreConfig('customconfig/review_group/debug_enable')){
            Mage::log($info, null, 'kiyoh.log');
        }
    }

    public function getRatingPercentage(){

        if(isset($this->mcrdata['company']['total_score'])){
            return $this->mcrdata['company']['total_score']*10;
        }
        return 100;
    }
    public function getMaxrating(){
        return 10;
    }
    public function getMicrodataUrl(){
        if(isset($this->mcrdata['company']['url'])){
            return $this->mcrdata['company']['url'];
        }
        return '#';
    }
    public function getRating(){
        if(isset($this->mcrdata['company']['total_score'])){
            return $this->mcrdata['company']['total_score'];
        }
        return '10';
    }
    public function getReviews(){
        if(isset($this->mcrdata['company']['total_reviews'])){
            return $this->mcrdata['company']['total_reviews'];
        }
        return '0';
    }
    public function getShowRating(){
        return (Mage::getStoreConfig('customconfig/review_group/show_rating')=='1');
    }
}