<?php

class Kiyoh_Customerreview_Block_Customerreview extends Mage_Core_Block_Template
{
    protected $mcrdata;

    public function _prepareLayout()
    {
        $storeId = Mage::app()->getStore()->getId();
        $network = Mage::getStoreConfig('customconfig/review_group/network',$storeId);
        $key = 'kiyoh_customerreview_microdata-'.$storeId.'-'.$network;
        $cache = Mage::app()->getCache();
        $this->mcrdata = unserialize($cache->load($key));

        if (!$this->mcrdata){
            $this->mcrdata = $this->receiveData();
        }

        if(isset($this->mcrdata['company']['total_score'])){
            $cache->save(serialize($this->mcrdata),$key,array('block_html'),3600);
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
        $data = array();
        $network = Mage::getStoreConfig('customconfig/review_group/network');
        if ($network!='klantenvertellen') {
            $kiyoh_connector = Mage::getStoreConfig('customconfig/review_group/custom_connector');
            $company_id = Mage::getStoreConfig('customconfig/review_group/company_id');
            $kiyoh_server = Mage::getStoreConfig('customconfig/review_group/custom_server');

            $file = 'https://www.' . $kiyoh_server . '/xml/recent_company_reviews.xml?connectorcode=' . $kiyoh_connector . '&company_id=' . $company_id;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $file);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $output = curl_exec($ch);
            curl_close($ch);
            $doc = '';
            try {
                $doc = simplexml_load_string($output);
            } catch (Exception $e){
                $this->logErrors($e->getMessage() . ' Kiyoh response: '. $output);
            }
            if ($doc) {
                $data = Mage::helper('core/data')->jsonDecode(Mage::helper('core/data')->jsonEncode($doc));
                if (isset($data['error'])) {
                    $this->logErrors($data['error']);
                } else {
                    $this->logErrors(libxml_get_errors());
                }
            }
        } else {
            $hash = Mage::getStoreConfig('customconfig/review_group/hash');
            $location_id = Mage::getStoreConfig('customconfig/review_group/location_id');
            $custom_servernew = Mage::getStoreConfig('customconfig/review_group/custom_servernew');

            $server = 'klantenvertellen.nl';
            if($custom_servernew=='newkiyoh.com'){
                $server = 'kiyoh.com';
            }

            $url = "https://{$server}/v1/publication/review/external?locationId=" . $location_id;
            $ch = curl_init();

            // set url
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-Publication-Api-Token: ' . $hash
            ));
            //return the transfer as a string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            // $output contains the output string
            $output = curl_exec($ch);
            $rating = json_decode($output, true);
            if ($rating && isset($rating['numberReviews'])){
                $data['company'] = array();
                $data['review_list'] = array();
                $data['company']['total_reviews'] = $rating['numberReviews'];
                $data['company']['total_score'] = $rating['averageRating'];
                $data['company']['url'] = $rating['viewReviewUrl'];
            }
        }

        return $data;
    }

    public function logErrors($info)
    {
        if (Mage::getStoreConfig('customconfig/review_group/debug_enable')) {
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