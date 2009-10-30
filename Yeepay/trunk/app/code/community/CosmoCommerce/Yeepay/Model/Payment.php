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
 * @package 	CosmoCommerce_Yeepay
 * @copyright	Copyright (c) 2009 CosmoCommerce,LLC. (http://www.cosmocommerce.com)
 * @contact :
 * T: +86-021-66346672
 * L: Shanghai,China
 * M:sales@cosmocommerce.com
 */
class CosmoCommerce_Yeepay_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'yeepay_payment';
    protected $_formBlockType = 'yeepay/form';

    // Yeepay return codes of payment
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
    public function getYeepayUrl()
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
		return Mage::getUrl('yeepay/payment/normal', array('_secure' => true));
	}

	/**
	 *  Return URL for Yeepay success response
	 *
	 *  @return	  string URL
	 */
	protected function getSuccessURL()
	{
		return Mage::getUrl('yeepay/payment/success', array('_secure' => true));
	}

    /**
     *  Return URL for Yeepay failure response
     *
     *  @return	  string URL
     */
    protected function getErrorURL()
    {
        return Mage::getUrl('yeepay/payment/error', array('_secure' => true));
    }

	/**
	 *  Return URL for Yeepay notify response
	 *
	 *  @return	  string URL
	 */
	protected function getNotifyURL()
	{
		return Mage::getUrl('yeepay/payment/notify', array('_secure' => true));
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
        $block = $this->getLayout()->createBlock('yeepay/form_payment', $name);
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
        return Mage::getUrl('yeepay/payment/redirect');
    }

	

	
    /**
     *  Return Standard Checkout Form Fields for request to Yeepay
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
				
		$p0_Cmd='Buy';//业务类型,支付请求，固定值"Buy" .
		$p1_MerId=$this->getConfigData('partner_id');//商户编号p1_MerId,以及密钥merchantKey 需要从易宝支付平台获得
		$p2_Order=$order->getRealOrderId();//商户订单号,选填
		$p3_Amt=sprintf('%.2f', $order->getBaseGrandTotal());//支付金额,必填.单位:元，精确到分.
		$p4_Cur='CNY';//交易币种,固定值"CNY".
		$p5_Pid=$order->getRealOrderId();//商品名称,用于支付时显示在易宝支付网关左侧的订单产品信息.
		$p6_Pcat=$order->getRealOrderId();//商品种类
		$p7_Pdesc=$order->getRealOrderId();//商品描述	
		$p8_Url='http://localhost/yee/callback.php';//商户接收支付成功数据的地址,支付成功后易宝支付会向该地址发送两次成功通知.
		$p9_SAF='0';//送货地址标识
		$pa_MP='EXPRESS';//商户扩展信息,商户可以任意填写1K 的字符串,支付成功时将原样返回.
		$pd_FrpId='';//银行编码,默认为""，到易宝支付网关.若不需显示易宝支付的页面，直接跳转到各银行、神州行支付、骏网一卡通等支付页面，该字段可依照附录:银行列表设置参数值.
		$pr_NeedResponse='0';//应答机制,为"1": 需要应答机制;为"0": 不需要应答机制.
				
		$parameter=array('p0_Cmd'    		=> $p0_Cmd,	
                         'p1_MerId'    		=> $p1_MerId,
                         'p2_Order'    		=> $p2_Order,
                         'p3_Amt'           => $p3_Amt,
                         'p4_Cur'			=> $p4_Cur,
                         'p5_Pid'           => $p5_Pid,
                         'p6_Pcat'          => $p6_Pcat,
                         'p7_Pdesc'     	=> $p7_Pdesc,					   
                         'p8_Url' 			=> $p8_Url,
				 		 'p9_SAF'			=> $p9_SAF,
                         'pa_MP'    		=> $pa_MP,
                         'pd_FrpId'         => $pd_FrpId,
                         'pr_NeedResponse'  => $pr_NeedResponse 
                         );

	
		$merchantKey= $this->getConfigData('security_code');
		//调用签名函数生成签名串
		$hmac = $this->getReqHmacString($merchantKey,$p0_Cmd,$p1_MerId,$p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$p9_SAF,$pa_MP,$pd_FrpId,$pr_NeedResponse);
		
		$parameter['hmac'] = $hmac;
        return $parameter;		
    }
    
   
	
	#签名函数生成签名串
	public function getReqHmacString($merchantKey,$p0_Cmd,$p1_MerId,$p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$p9_SAF,$pa_MP,$pd_FrpId,$pr_NeedResponse)
	{
			
	  #进行签名处理，一定按照文档中标明的签名顺序进行
	  $sbOld = "";
	  #加入业务类型
	  $sbOld = $sbOld.$p0_Cmd;
	  #加入商户编号
	  $sbOld = $sbOld.$p1_MerId;
	  #加入商户订单号
	  $sbOld = $sbOld.$p2_Order;     
	  #加入支付金额
	  $sbOld = $sbOld.$p3_Amt;
	  #加入交易币种
	  $sbOld = $sbOld.$p4_Cur;
	  #加入商品名称
	  $sbOld = $sbOld.$p5_Pid;
	  #加入商品分类
	  $sbOld = $sbOld.$p6_Pcat;
	  #加入商品描述
	  $sbOld = $sbOld.$p7_Pdesc;
	  #加入商户接收支付成功数据的地址
	  $sbOld = $sbOld.$p8_Url;
	  #加入送货地址标识
	  $sbOld = $sbOld.$p9_SAF;
	  #加入商户扩展信息
	  $sbOld = $sbOld.$pa_MP;
	  #加入银行编码
	  $sbOld = $sbOld.$pd_FrpId;
	  #加入是否需要应答机制
	  $sbOld = $sbOld.$pr_NeedResponse;
	  
	  return $this->HmacMd5($sbOld,$merchantKey);
	  
	} 

	public function getCallbackHmacString($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType)
	{
		#取得加密前的字符串
		$sbOld = "";
		#加入商家ID
		$sbOld = $sbOld.$p1_MerId;
		#加入消息类型
		$sbOld = $sbOld.$r0_Cmd;
		#加入业务返回码
		$sbOld = $sbOld.$r1_Code;
		#加入交易ID
		$sbOld = $sbOld.$r2_TrxId;
		#加入交易金额
		$sbOld = $sbOld.$r3_Amt;
		#加入货币单位
		$sbOld = $sbOld.$r4_Cur;
		#加入产品Id
		$sbOld = $sbOld.$r5_Pid;
		#加入订单ID
		$sbOld = $sbOld.$r6_Order;
		#加入用户ID
		$sbOld = $sbOld.$r7_Uid;
		#加入商家扩展信息
		$sbOld = $sbOld.$r8_MP;
		#加入交易结果返回类型
		$sbOld = $sbOld.$r9_BType;

		return $this->HmacMd5($sbOld,$merchantKey);

	}


	#	取得返回串中的所有参数
	public function getCallBackValue(&$r0_Cmd,&$r1_Code,&$r2_TrxId,&$r3_Amt,&$r4_Cur,&$r5_Pid,&$r6_Order,&$r7_Uid,&$r8_MP,&$r9_BType,&$hmac)
	{  
		$r0_Cmd		= $_REQUEST['r0_Cmd'];
		$r1_Code	= $_REQUEST['r1_Code'];
		$r2_TrxId	= $_REQUEST['r2_TrxId'];
		$r3_Amt		= $_REQUEST['r3_Amt'];
		$r4_Cur		= $_REQUEST['r4_Cur'];
		$r5_Pid		= $_REQUEST['r5_Pid'];
		$r6_Order	= $_REQUEST['r6_Order'];
		$r7_Uid		= $_REQUEST['r7_Uid'];
		$r8_MP		= $_REQUEST['r8_MP'];
		$r9_BType	= $_REQUEST['r9_BType']; 
		$hmac			= $_REQUEST['hmac'];
		
		return null;
	}

	public function CheckHmac($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac)
	{
		if($hmac==$this->getCallbackHmacString($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType))
			return true;
		else
			return false;
	}
			
	  
	public function HmacMd5($data,$key)
	{
	// RFC 2104 HMAC implementation for php.
	// Creates an md5 HMAC.
	// Eliminates the need to install mhash to compute a HMAC
	// Hacked by Lance Rushing(NOTE: Hacked means written)

	//需要配置环境支持iconv，否则中文参数不能正常处理

	$b = 64; // byte length for md5
	if (strlen($key) > $b) {
	$key = pack("H*",md5($key));
	}
	$key = str_pad($key, $b, chr(0x00));
	$ipad = str_pad('', $b, chr(0x36));
	$opad = str_pad('', $b, chr(0x5c));
	$k_ipad = $key ^ $ipad ;
	$k_opad = $key ^ $opad;

	return md5($k_opad . pack("H*",md5($k_ipad . $data)));
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
     *  Return response for Yeepay success payment
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
     *  Return response for Yeepay failure payment
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