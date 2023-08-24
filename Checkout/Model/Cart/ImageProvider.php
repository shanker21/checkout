<?php

namespace MDC\Checkout\Model\Cart;

/**
 * 
 */
class ImageProvider extends \Magento\Checkout\Model\Cart\ImageProvider
{
	
	public function getImages($cartId)
    {
        $itemData = [];

        /** @see code/Magento/Catalog/Helper/Product.php */
        $items = $this->itemRepository->getList($cartId);
        /** @var \Magento\Quote\Model\Quote\Item $cartItem */
        foreach ($items as $cartItem) {
            $allData = $this->customerDataItem->getItemData($cartItem);
            $itemData[$cartItem->getItemId()] = $allData['product_image'];
        }
        return $itemData;
    }
}