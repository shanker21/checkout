<?php
declare(strict_types=1);

namespace MDC\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;
Use Magento\Framework\Session\SessionManagerInterface;
 
class OrderPlaceAfter implements ObserverInterface
{
    
    private $resource;
    
    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     */

    /**
     * @var SessionManagerInterface
    */
    private $session;
    
    public function __construct(
            \Magento\Framework\App\ResourceConnection $resource,
            \Magento\Quote\Model\QuoteRepository $quoteRepository,
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
            SessionManagerInterface $session
    ) {
         $this->resource = $resource;
         $this->quoteRepository = $quoteRepository;
         $this->date =  $date;
         $this->session = $session;
      }
    /**
     * sales_order_place_after event handler.
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/orderaftersave.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('ordersaveobserver');
        $order = $observer->getEvent()->getOrder();
        $logger->info('orderid :'.$order->getIncrementId());
        $addresses = $order->getAddresses();
        foreach ($addresses as $address) {
            $addressId = "";
            if (isset ($address['customer_address_id'])) {
                $addressId = $address['customer_address_id'];
                if ($addressId) {
                    $deliverynote = $order->getSpecialInstructions();
                    try {
                        $connection  = $this->resource->getConnection();
                        $data = ["address_delivery_instructions" => $deliverynote]; 
                        $where = ['entity_id = ?' => (int) $addressId];
                        $tableName = $connection->getTableName("customer_address_entity");
                        $connection->update($tableName, $data, $where);
                    }
                    catch (\Exception $e) {
                        
                    }
                }
            }
        }

        //Saving next day as delivery date for gift vouchers
        $orderAllVisibleItems = $order->getAllVisibleItems();
        $giftType  = 0;
        foreach($orderAllVisibleItems as $item ) {
            if ($item->getProductType() == 'giftcard') {
                $giftType  = 1;
                break;
            }
        }
        if ($giftType  == 1) {
            $logger->info('Gift Voucher');
            $currentDate = $this->date->date()->format('Y-m-d 00:00:00');
            $nextDate = date('Y-m-d 00:00:00', strtotime('+1 day', strtotime($currentDate)));
            $order->setDeliveryDate($nextDate);
            $order->save();
        }
    }
}
