<?php
declare(strict_types=1);

namespace MDC\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session;
 
class DisableCOD implements ObserverInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;
    
    /**
     * @param Session $checkoutSession
     */
    
    public function __construct(
            Session $checkoutSession
    ) {
         $this->checkoutSession = $checkoutSession;
      }
    /**
     * payment_method_is_active event handler.
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote  */
        $quote = $this->checkoutSession->getQuote();
        $cartItems = $quote->getAllVisibleItems();
        foreach ($cartItems as $item) {
            $name = $item->getName();
            $type = $item->getProductType();
            if ($type == "giftcard") {

                if ($observer->getMethodInstance()->getCode() == "checkmo") {
                    $checkResult = $observer->getEvent()->getResult();
                    $checkResult->setData('is_available', false); 
                    return;
                }
            }
        }
         
    }
}
