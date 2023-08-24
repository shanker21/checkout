<?php
namespace MDC\Checkout\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item;
use Onedigital\Success\Block\Odfunc;

class DefaultItem
{
	protected $productRepo;
    protected $odfunc;

    public function __construct(ProductRepositoryInterface $productRepository,
    Odfunc $odfunc)
    {
        $this->productRepo = $productRepository;
        $this->odfunc = $odfunc;
    }

    public function aroundGetItemData($subject, \Closure $proceed, Item $item)
    {
        $data = $proceed($item);
        $product = $item->getProduct();
        $productId = $item->getProduct()->getId();
        $odimage= $this->odfunc->getPrdampimg($productId);
        $product = $this->productRepo->getById($item->getProduct()->getId());
        $attributes = $product->getAttributes();

        $atts = [
            "product_weight" => $attributes['product_weight']->getFrontend()->getValue($product),
            "odimage" => $odimage
        ];

        $atts = [];
		$_weight = $product->getProductWeight(); 
        if ($_weight) {
        	$atts = [
	            "product_weight" => $_weight,
                "odimage"=>$odimage
	        ];
        }
        return array_merge($data, $atts);
    }
}
