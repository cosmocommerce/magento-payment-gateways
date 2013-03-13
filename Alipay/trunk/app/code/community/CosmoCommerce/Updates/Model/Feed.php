<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    CosmoCommerce
 * @package     CosmoCommerce_Alipay
 * @copyright   Copyright (c) 2009-2013 CosmoCommerce,LLC. (http://www.cosmocommerce.com)
 * @contact :
 * T: +86-021-66346672
 * L: Shanghai,China
 * M:sales@cosmocommerce.com
 */
	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 
class CosmoCommerce_Updates_Model_Feed extends Mage_AdminNotification_Model_Feed
{
    const XML_USE_HTTPS_PATH    = 'cosmocommerce/feed/use_https';
    const XML_FEED_URL_PATH     = 'cosmocommerce/feed/url';
    const XML_FREQUENCY_PATH    = 'cosmocommerce/feed/frequency';
	const XML_FREQUENCY_ENABLE    = 'cosmocommerce/feed/enable';
    const XML_LAST_UPDATE_PATH  = 'cosmocommerce/feed/last_update';

	
	public static function check(){
//		if(!Mage::getStoreConfig(self::XML_FREQUENCY_ENABLE)){		
//			return;
//		}
		return Mage::getModel('cosmocommerce/feed')->checkUpdate();
	}
	
    public function getFrequency()
    {
        return Mage::getStoreConfig(self::XML_FREQUENCY_PATH) * 1;
    }

    public function getLastUpdate()
    {		
		//return 100;
        return Mage::app()->loadCache('cosmocommerce_notifications_lastcheck');
    }

    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), 'cosmocommerce_notifications_lastcheck');
        return $this;
    }
    public function getFeedUrl()
    {
		
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = (Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://')
                . Mage::getStoreConfig(self::XML_FEED_URL_PATH);
        }
        return $this->_feedUrl;
    }

 		public function checkUpdate()
    {
        if (($this->getFrequency() + $this->getLastUpdate()) > time()) {
            return $this;
        }

        $feedData = array();

        $feedXml = $this->getFeedData();

        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            foreach ($feedXml->channel->item as $item) {
                $feedData[] = array(
                    'severity'      => (int)$item->severity ? (int)$item->severity : 3,
                    'date_added'    => $this->getDate((string)$item->pubDate),
                    'title'         => (string)$item->title,
                    'description'   => (string)$item->description,
                    'url'           => (string)$item->link,
                );
            }
            if ($feedData) {
                Mage::getModel('adminnotification/inbox')->parse(array_reverse($feedData));
            }

        }
        $this->setLastUpdate();

        return $this;
    }

}
