<?php

class Kiyoh_Customerreview_Model_Observer
{
    public function salesOrderShipmentSaveAfter(Varien_Event_Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        $storeId = $order->getStoreId();

        $kiyoh_status = Mage::getStoreConfig('customconfig/review_group/custom_enable',$storeId);
        $kiyoh_eventval = Mage::getStoreConfig('customconfig/review_group/custom_event',$storeId);

        if ($kiyoh_eventval === 'Shipping' &&  $kiyoh_status =='1'){
//				Mage::log('salesOrderShipmentSaveAfter', null, 'kiyoh.log');
                $this->sendRequest($order);
        }
    }
    public function salesOrderPaymentAfter(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        $storeId = $order->getStoreId();

        $kiyoh_status = Mage::getStoreConfig('customconfig/review_group/custom_enable',$storeId);
        $kiyoh_eventval = Mage::getStoreConfig('customconfig/review_group/custom_event',$storeId);

        if ($kiyoh_eventval === 'Purchase' &&  $kiyoh_status =='1'){
//				Mage::log('salesOrderPaymentAfter', null, 'kiyoh.log');
                $this->sendRequest($order);
        }
    }

    public function salesOrderSaveAfter($observer){
        $order = $observer->getOrder();
        $storeId = $order->getStoreId();
        $kiyoh_status = Mage::getStoreConfig('customconfig/review_group/custom_enable',$storeId);
        $kiyoh_eventval = Mage::getStoreConfig('customconfig/review_group/custom_event',$storeId);
        $kiyoh_orderstatus = explode(',',Mage::getStoreConfig('customconfig/review_group/custom_event_order_status',$storeId));

        if ($kiyoh_eventval === 'Orderstatus' &&  $kiyoh_status =='1' && in_array($observer->getOrder()->getStatus(), $kiyoh_orderstatus)){
//                Mage::log('salesOrderSaveAfter', null, 'kiyoh.log');                
                $this->sendRequest($observer->getOrder());
        }
    }
    protected function sendRequest($order){
        /** @var Mage_Sales_Model_Order $order */
        $storeId = $order->getStoreId();
        $groups_str = Mage::getStoreConfig('customconfig/review_group/exclude_customer_groups',$storeId);
        $exclude_customer_groups = array();
        if($groups_str != ''){
            $exclude_customer_groups = explode(',',$groups_str);
        }
        if(in_array($order->getCustomerGroupId(),$exclude_customer_groups)){
            return;
        }
        $email = $order->getCustomerEmail();
        $network = Mage::getStoreConfig('customconfig/review_group/network',$storeId);
        $kiyoh_server = Mage::getStoreConfig('customconfig/review_group/custom_server',$storeId);
        $kiyoh_user = Mage::getStoreConfig('customconfig/review_group/custom_user',$storeId);
        $kiyoh_connector = Mage::getStoreConfig('customconfig/review_group/custom_connector',$storeId);
        $kiyoh_action = Mage::getStoreConfig('customconfig/review_group/custom_action',$storeId);
        $kiyoh_delay = Mage::getStoreConfig('customconfig/review_group/custom_delay',$storeId);
        $kiyoh_lang = Mage::getStoreConfig('customconfig/review_group/language',$storeId);
        if ($network!='klantenvertellen'){
            $url = 'https://www.'.$kiyoh_server.'/set.php?user='.$kiyoh_user.'&connector='.$kiyoh_connector.'&action='.$kiyoh_action.'&targetMail='.$email.'&delay='.$kiyoh_delay.'&language='.$kiyoh_lang;
        } else {
            $invite_email = $email;
            $first_name = $order->getCustomerFirstname();
            $last_name = $order->getCustomerLastname();
            if (!$first_name){
                $first_name = $order->getShippingAddress()->getFirstname();
            }
            if (!$last_name){
                $last_name = $order->getShippingAddress()->getLastname();
            }
            $hash = Mage::getStoreConfig('customconfig/review_group/hash',$storeId);
            $location_id = Mage::getStoreConfig('customconfig/review_group/location_id',$storeId);
            $custom_delay_1 = Mage::getStoreConfig('customconfig/review_group/custom_delay_1',$storeId);
            $language_1 = Mage::getStoreConfig('customconfig/review_group/language_1',$storeId);
            $custom_servernew = Mage::getStoreConfig('customconfig/review_group/custom_servernew',$storeId);

            $server = 'klantenvertellen.nl';
            if($custom_servernew=='newkiyoh.com'){
                $server = 'kiyoh.com';
            }
            $url = "https://{$server}/v1/invite/external?" .
                "hash={$hash}" .
                "&location_id={$location_id}" .
                "&invite_email={$invite_email}" .
                "&delay={$custom_delay_1}" .
                "&first_name={$first_name}" .
                "&last_name={$last_name}" .
                "&language={$language_1}";
        }

        try{
            // create a new cURL resource
            $curl = curl_init();

            // set URL and other appropriate options
            curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_SSLVERSION,1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            // grab URL and pass it to the browser
            $response = curl_exec($curl);
            if (curl_errno($curl)){
                throw new Exception(curl_error($curl).'---Url---'.$url);
            }
            if(Mage::getStoreConfig('customconfig/review_group/debug_enable',$storeId)){
                Mage::log($response.'---Url---'.$url, null, 'kiyoh.log');
            }
            
        } catch (Exception $e){
            Mage::log($e->getMessage(), null, 'kiyoh.log');
        }
        curl_close($curl);
    }
}
