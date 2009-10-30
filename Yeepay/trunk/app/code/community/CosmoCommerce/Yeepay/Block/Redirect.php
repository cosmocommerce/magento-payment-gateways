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
class CosmoCommerce_Yeepay_Block_Redirect extends Mage_Core_Block_Abstract
{

	protected function _toHtml()
	{
		$standard = Mage::getModel('yeepay/payment');
        $form = new Varien_Data_Form();
        $form->setAction($standard->getYeepayUrl())
            ->setId('yeepay_payment_checkout')
            ->setName('yeepay_payment_checkout')
            ->setMethod('GET')
            ->setUseContainer(true);
        foreach ($standard->setOrder($this->getOrder())->getStandardCheckoutFormFields() as $field => $value) {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }

        $formHTML = $form->toHtml();

        $html = '<html><body>';
        $html.= $this->__('You will be redirected to Yeepay in a few seconds.');
        $html.= $formHTML;
        $html.= '<script type="text/javascript">document.getElementById("yeepay_payment_checkout").submit();</script>';
        $html.= '</body></html>';


        return $html;
    }
}