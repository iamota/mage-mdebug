<?php
class Iamota_Mdebug_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function log($msg,$file="mdebug.log"){
		Mage::log($msg,null,$file);
    }

    public function logBacktrace($msg,$file="mdebug_backtrace.log"){
    	Mage::log(Mage::app()->getRequest()->getPathInfo()." >> ".debug_backtrace()[3]['file']."::".debug_backtrace()[3]['function']." >> ".debug_backtrace()[2]['file']."::".debug_backtrace()[2]['function']." >> ".debug_backtrace()[1]['file']."::".debug_backtrace()[1]['function']." :: ".$msg."\n\n",null,$file);
    }

    public function logQuote($file="mdebug_quote.log",$verbose=false){
    	$quote = Mage::getModel('checkout/cart')->getQuote();
        $quote->collectTotals()->save();
        $totals = $quote->getTotals();
    	$billingAddress = $quote->getBillingAddress()->getData();
    	$shippingAddress = $quote->getShippingAddress()->getData();

    	$quoteData = array(
    			"subtotal"=>$quote->getSubtotal(),
    			"grand_total"=>$quote->getGrandTotal(),
    			"checkout_method"=>$quote->getCheckoutMethod(),
    			"customer_id"=>$quote->getCustomerId(),
                "shipping_amount"=>$quote->getShippingAddress()->getShippingAmount(),
                "shipping_addr_tax"=>$quote->getShippingAddress()->getData('tax_amount'),
                "billing_addr_tax"=>$quote->getBillingAddress()->getData('tax_amount'),
    			"is_multishipping"=>$quote->getIsMultiShipping()
    		);

        if($verbose){
            foreach($totals as $total_code => $total_val){
                $quoteData["totals_".$total_code] = $total_val->getValue();
            }
        }

    	$billingData = array();
    	$shippingData = array();

    	if($verbose){
    		$billingData = $billingAddress;
    		$shippingData = $shippingAddress;
    	}
    	else{
    		$billingData = array(
    				"city"=>$billingAddress["city"],
    			);

    		$shippingData = array(
    				"city"=>$shippingAddress["city"],
    			);
    	}

        $itemCounter = 0;

        foreach($quote->getAllItems() as $item) {
            $quoteData["cart_item_".$itemCounter."_sku"] = $item->getSku();
            $quoteData["cart_item_".$itemCounter."_qty"] = $item->getQty();
            $quoteData["cart_item_".$itemCounter."_price"] = $item->getPrice();
        }

    	$appliedRuleIds = $quote->getAppliedRuleIds();
        $appliedRuleIds = explode(',', $appliedRuleIds);
        $numSalesRules = sizeof($appliedRuleIds);
        $rules =  Mage::getModel('salesrule/rule')->getCollection()->addFieldToFilter('rule_id' , array('in' => $appliedRuleIds));

        $ruleCounter = 0;
        $totalDiscount = 0;
        foreach ($rules as $rule) {
            $ruleCounter++;

            $quoteData["price_rule_".$ruleCounter."_label"] = $rule->getName();
            $quoteData["price_rule_".$ruleCounter."_discount"] = $rule->getDiscountAmount();
            $quoteData["price_rule_".$ruleCounter."_action"] = $rule->getSimpleAction();
        }

    	$quoteData = array_merge($quoteData,$billingData);
    	$quoteData = array_merge($quoteData,$shippingData);

        Mage::log(" *** Mdebug Quote Debugger *** ",null,$file);

        foreach($quoteData as $key => $value){
            Mage::log(" *** ".strtoupper($key)." = ".$value,null,$file);
        }
    }

    public function logSalesRule($salesRule,$file="mdebug_salesrule.log"){
        Mage::log("Mdebug :: logSalesRule()",null,$file);
        Mage::log("Mdebug :: logSalesRule id=".$salesRule->getId(),null,$file);
        Mage::log("Mdebug :: logSalesRule name=".$salesRule->getName(),null,$file);
        Mage::log("Mdebug :: logSalesRule simple action=".$salesRule->getSimpleAction(),null,$file);
        Mage::log("Mdebug :: logSalesRule discount amount=".$salesRule->getDiscountAmount(),null,$file);
        Mage::log("Mdebug :: logSalesRule conditions serialized=".$salesRule->getConditionsSerialized(),null,$file);
        Mage::log("Mdebug :: logSalesRule actions serialized=".$salesRule->getActionsSerialized(),null,$file);
        Mage::log("Mdebug :: logSalesRule discount qty=".$salesRule->getDiscountQty(),null,$file);
        Mage::log("Mdebug :: logSalesRule discount step=".$salesRule->getDiscountStep(),null,$file);
        Mage::log("Mdebug :: logSalesRule apply to shipping=".$salesRule->getApplyToShipping(),null,$file);
    }

	public function logOrder($order,$file="mdebug_order.log",$verbose=false){
		ob_start();
		var_dump($order);
		$result = ob_get_clean();

		Mage::log("Mdebug :: logOrder()",null,$file);
		Mage::log($result,null,$file);
	}
}
