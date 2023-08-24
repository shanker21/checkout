<?php
namespace MDC\Checkout\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Class PmIdSave
 */
class PmIdSave
{
    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    public function __construct(
        CookieManagerInterface $cookieManager
    )
    {
        $this->_cookieManager = $cookieManager;
    }

    /**
     * @param OrderManagementInterface $subject
     * @param OrderInterface           $order
     *
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPlace(
        OrderManagementInterface $subject,
        OrderInterface $result
    ) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/placeorderpluginafter.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Getting value from cookie');
        $orderId = $result->getIncrementId();
        if ($orderId) {
            $pmId = $this->_cookieManager->getCookie('paymentId');
            $logger->info("orderid : ".$orderId);
            $logger->info("pmid :" .$pmId);
            if ($pmId) {
                $result->setData('pm_id',$pmId);
                $logger->info("saved in order");
            }
        }
        return $result;
    }
}
