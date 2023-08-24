<?php

namespace MDC\Checkout\Model;

/**
 * 
 */
class Quote extends \Magento\Quote\Model\Quote
{
	
	/**
     * Merge quotes
     *
     * @param Quote $quote
     * @return $this
     */
    public function merge(\Magento\Quote\Model\Quote $quote)
    {
        $this->_eventManager->dispatch(
            $this->_eventPrefix . '_merge_before',
            [$this->_eventObject => $this, 'source' => $quote]
        );

        foreach ($quote->getAllVisibleItems() as $item) {
            $found = false;
            foreach ($this->getAllItems() as $quoteItem) {

                /* 
                * this will merge the products if product id is same 
                * please note : this will not check for custom options now 
                */
                $sameProductId = false;
                if ($quoteItem->getProductId() == $item->getProductId()) {
                    $sameProductId = true;
                }
    
                if ($quoteItem->compare($item) || $sameProductId) {
                    $quoteItem->setQty($quoteItem->getQty() + $item->getQty());
                    $this->itemProcessor->merge($item, $quoteItem);
                    $found = true;
                    break;
                }
            }
    
            if (!$found) {
                $newItem = clone $item;
                $this->addItem($newItem);
                if ($item->getHasChildren()) {
                    foreach ($item->getChildren() as $child) {
                        $newChild = clone $child;
                        $newChild->setParentItem($newItem);
                        $this->addItem($newChild);
                    }
                }
            }
        }

        /**
         * Init shipping and billing address if quote is new
         */
        if (!$this->getId()) {
            $this->getShippingAddress();
            $this->getBillingAddress();
        }

        if ($quote->getCouponCode()) {
            $this->setCouponCode($quote->getCouponCode());
        }

        $this->_eventManager->dispatch(
            $this->_eventPrefix . '_merge_after',
            [$this->_eventObject => $this, 'source' => $quote]
        );

        return $this;
    }
}