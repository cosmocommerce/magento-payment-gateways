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
 * @category	CosmoCommerce
 * @package 	CosmoCommerce_99Bill
 * @copyright	Copyright (c) 2009 CosmoCommerce,LLC. (http://www.cosmocommerce.com)
 * @contact :
 * T: +86-021-66346672
 * L: Shanghai,China
 * M:sales@cosmocommerce.com
 */
class CosmoCommerce_Cosmo99Bill_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'cosmo99bill_payment';
    protected $_formBlockType = 'cosmo99bill/form';

    // Cosmo99Bill return codes of payment
    const RETURN_CODE_ACCEPTED      = 'Success';
    const RETURN_CODE_TEST_ACCEPTED = 'Success';
    const RETURN_CODE_ERROR         = 'Fail';

    // Payment configuration
    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    // Order instance
    protected $_order = null;

    /**
     *  Returns Target URL
     *
     *  @return	  string Target URL
     */
    public function getCosmo99BillUrl()
    {
        $url = $this->getConfigData('transport').'://'.$this->getConfigData('gateway');
        return $url;
    }

    /**
     *  Return back URL
     *
     *  @return	  string URL
     */
	protected function getReturnURL()
	{
		return Mage::getUrl('checkout/onepage/success', array('_secure' => true));
	}

	/**
	 *  Return URL for Cosmo99Bill success response
	 *
	 *  @return	  string URL
	 */
	protected function getSuccessURL()
	{
		return Mage::getUrl('checkout/onepage/success', array('_secure' => true));
	}

    /**
     *  Return URL for Cosmo99Bill failure response
     *
     *  @return	  string URL
     */
    protected function getErrorURL()
    {
        return Mage::getUrl('cosmo99bill/payment/error', array('_secure' => true));
    }

	/**
	 *  Return URL for Cosmo99Bill notify response
	 *
	 *  @return	  string URL
	 */
	protected function getNotifyURL()
	{
		return Mage::getUrl('checkout/onepage/success', array('_secure' => true));
	}

    /**
     * Capture payment
     *
     * @param   Varien_Object $orderPayment
     * @return  Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $payment->setStatus(self::STATUS_APPROVED)
            ->setLastTransId($this->getTransactionId());

        return $this;
    }

    /**
     *  Form block description
     *
     *  @return	 object
     */
    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('cosmo99bill/form_payment', $name);
        $block->setMethod($this->_code);
        $block->setPayment($this->getPayment());

        return $block;
    }

    /**
     *  Return Order Place Redirect URL
     *
     *  @return	  string Order Redirect URL
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('cosmo99bill/payment/redirect');
    }

    /**
     *  Return Standard Checkout Form Fields for request to Cosmo99Bill
     *
     *  @return	  array Array of hidden form fields
     */
    public function getStandardCheckoutFormFields()
    {
        $session = Mage::getSingleton('checkout/session');
        
        $order = $this->getOrder();
        if (!($order instanceof Mage_Sales_Model_Order)) {
            Mage::throwException($this->_getHelper()->__('Cannot retrieve order object'));
        }
		
		
		$_merchantAcctId=$this->getConfigData('partner_id');
		$_version="v2.0";
		$_key=$this->getConfigData('security_code');
		$_signType="1";
		$_payerContactType="1";
		$_orderId=$order->getRealOrderId();
		$_orderAmount=sprintf('%.2f', $order->getBaseGrandTotal())*100;
		$_orderTime=date('YmdHis');
		$_productName=$order->getRealOrderId();
		$_productNum="1";//???
		$_productId=$order->getRealOrderId();
		$_productDesc=$order->getRealOrderId();
		$_ext1=$this->getConfigData('ext1');
		$_ext2=$this->getConfigData('ext2');
		$_bankId=$this->getConfigData('bank_id');
		
		$_redoFlag=$this->getConfigData('redo_flag');//1代表同一订单号只允许提交1次；0表示同一订单号在没有支付成功的前提下可重复提交多次。默认为0建议实物购物车结算类商户采用0；虚拟产品类商户采用1
		
		$_pid=$this->getConfigData('pid'); ///合作伙伴在快钱的用户编号
		
		$_payType=$this->getConfigData('pay_type');
		
		$_payerName=$order->getCustomerName();
		$_payerContact=$order->getCustomerEmail() ;

		
		//todo : url
		$_pageUrl=$this->getConfigData('page_url');
		$_bgUrl=$this->getConfigData('bg_url');
		$_inputCharset=$this->getConfigData('input_charset');//1代表UTF-8; 2代表GBK; 3代表gb2312
		$_language=$this->getConfigData('display_language');//1代表中文；2代表英文
		
		
		
		
		
		//生成加密签名串
///请务必按照如下顺序和规则组成加密串！
	$signMsgVal="";
	$signMsgVal=$this->appendParam($signMsgVal,"inputCharset",$_inputCharset);
	$signMsgVal=$this->appendParam($signMsgVal,"pageUrl",$_pageUrl);
	$signMsgVal=$this->appendParam($signMsgVal,"bgUrl",$_bgUrl);
	$signMsgVal=$this->appendParam($signMsgVal,"version",$_version);
	$signMsgVal=$this->appendParam($signMsgVal,"language",$_language);
	$signMsgVal=$this->appendParam($signMsgVal,"signType",$_signType);
	$signMsgVal=$this->appendParam($signMsgVal,"merchantAcctId",$_merchantAcctId);
	$signMsgVal=$this->appendParam($signMsgVal,"payerName",$_payerName);
	$signMsgVal=$this->appendParam($signMsgVal,"payerContactType",$_payerContactType);
	$signMsgVal=$this->appendParam($signMsgVal,"payerContact",$_payerContact);
	$signMsgVal=$this->appendParam($signMsgVal,"orderId",$_orderId);
	$signMsgVal=$this->appendParam($signMsgVal,"orderAmount",$_orderAmount);
	$signMsgVal=$this->appendParam($signMsgVal,"orderTime",$_orderTime);
	$signMsgVal=$this->appendParam($signMsgVal,"productName",$_productName);
	$signMsgVal=$this->appendParam($signMsgVal,"productNum",$_productNum);
	$signMsgVal=$this->appendParam($signMsgVal,"productId",$_productId);
	$signMsgVal=$this->appendParam($signMsgVal,"productDesc",$_productDesc);
	$signMsgVal=$this->appendParam($signMsgVal,"ext1",$_ext1);
	$signMsgVal=$this->appendParam($signMsgVal,"ext2",$_ext2);
	$signMsgVal=$this->appendParam($signMsgVal,"payType",$_payType);	
	$signMsgVal=$this->appendParam($signMsgVal,"bankId",$_bankId);
	$signMsgVal=$this->appendParam($signMsgVal,"redoFlag",$_redoFlag);
	$signMsgVal=$this->appendParam($signMsgVal,"pid",$_pid);
	$signMsgVal=$this->appendParam($signMsgVal,"key",$_key);
	$signMsg= strtoupper(md5($signMsgVal));


		
		$parameter = array(
			'inputCharset'=>$_inputCharset,
			'pageUrl'=>$_pageUrl,
			'bgUrl'=>$_bgUrl,
			'version'=>$_version,
			'language'=>$_language,
			'signType'=>$_signType,
			'merchantAcctId'=>$_merchantAcctId,
			'payerName'=>$_payerName,
			'payerContactType'=>$_payerContactType,
			'payerContact'=>$_payerContact,
			'orderId'=>$_orderId,
			'orderAmount'=>$_orderAmount,
			'orderTime'=>$_orderTime,
			'productName'=>$_productName,
			'productNum'=>$_productNum,
			'productId'=>$_productId,
			'productDesc'=>$_productDesc,
			'ext1'=>$_ext1,
			'ext2'=>$_ext2,
			'payType'=>$_payType,
			'bankId'=>$_bankId,
			'redoFlag'=>$_redoFlag,
			'pid'=>$_pid,
			'signMsg'=>$signMsg
		);
						
		
        return $parameter;
    }

	
	//功能函数。将变量值不为空的参数组成字符串
	public function appendParam($returnStr,$paramId,$paramValue){

		if($returnStr!=""){
			
				if($paramValue!=""){
					
					$returnStr.="&".$paramId."=".$paramValue;
				}
			
		}else{
		
			If($paramValue!=""){
				$returnStr=$paramId."=".$paramValue;
			}
		}
		
		return $returnStr;
	}
	//功能函数。将变量值不为空的参数组成字符串。结束	
	
	/**
	 * Return authorized languages by Cosmo99Bill
	 *
	 * @param	none
	 * @return	array
	 */
	protected function _getAuthorizedLanguages()
	{
		$languages = array();
		
        foreach (Mage::getConfig()->getNode('global/payment/cosmo99bill_payment/languages')->asArray() as $data) 
		{
			$languages[$data['code']] = $data['name'];
		}
		
		return $languages;
	}
	
	/**
	 * Return language code to send to Cosmo99Bill
	 *
	 * @param	none
	 * @return	String
	 */
	protected function _getLanguageCode()
	{
		// Store language
		$language = strtoupper(substr(Mage::getStoreConfig('general/locale/code'), 0, 2));

		// Authorized Languages
		$authorized_languages = $this->_getAuthorizedLanguages();

		if (count($authorized_languages) === 1) 
		{
			$codes = array_keys($authorized_languages);
			return $codes[0];
		}
		
		if (array_key_exists($language, $authorized_languages)) 
		{
			return $language;
		}
		
		// By default we use language selected in store admin
		return $this->getConfigData('language');
	}



    /**
     *  Output failure response and stop the script
     *
     *  @param    none
     *  @return	  void
     */
    public function generateErrorResponse()
    {
        die($this->getErrorResponse());
    }

    /**
     *  Return response for Cosmo99Bill success payment
     *
     *  @param    none
     *  @return	  string Success response string
     */
    public function getSuccessResponse()
    {
        $response = array(
            'Pragma: no-cache',
            'Content-type : text/plain',
            'Version: 1',
            'OK'
        );
        return implode("\n", $response) . "\n";
    }

    /**
     *  Return response for Cosmo99Bill failure payment
     *
     *  @param    none
     *  @return	  string Failure response string
     */
    public function getErrorResponse()
    {
        $response = array(
            'Pragma: no-cache',
            'Content-type : text/plain',
            'Version: 1',
            'Document falsifie'
        );
        return implode("\n", $response) . "\n";
    }

}