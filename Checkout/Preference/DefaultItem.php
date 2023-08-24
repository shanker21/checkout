<?php

namespace MDC\Checkout\Preference;

use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Model\StockRegistry;
use MDC\Catalog\Helper\Data as MDCdata;

class DefaultItem extends \Magento\Checkout\CustomerData\DefaultItem
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;
    /**
     * @var \Onedigital\Success\Block\Odfunc
     */
    protected $odfunc;
    /**
     * @var \Magento\Msrp\Helper\Data
     */
    protected $msrpHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    protected $configurationPool;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var ItemResolverInterface
     */
    private $itemResolver;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    private $wishlistHelper;

     /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockRegistry
     */
    private $stockRegistry;

    /**
     * @var MDCdata
     */
    private $helperForCatalog;

    /**
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Onedigital\Success\Block\Odfunc $odfunc
     * @param \Magento\Msrp\Helper\Data $msrpHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Framework\Escaper|null $escaper
     * @param ItemResolverInterface|null $itemResolver
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \MDC\Catalog\Helper\Data $helperForCatalog
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Onedigital\Success\Block\Odfunc $odfunc,
        \Magento\Msrp\Helper\Data $msrpHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Escaper $escaper = null,
        ItemResolverInterface $itemResolver = null,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        StoreManagerInterface $storeManager,
        StockRegistry $stockRegistry,
        MDCdata $helperForCatalog
    ) {
        $this->odfunc = $odfunc;
        $this->wishlistHelper = $wishlistHelper;
        $this->_session = $session;
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(\Magento\Framework\Escaper::class);
        $this->itemResolver = $itemResolver ?: ObjectManager::getInstance()->get(ItemResolverInterface::class);
        $this->redirect = $redirect;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->helperForCatalog = $helperForCatalog;
        parent::__construct($imageHelper, $msrpHelper, $urlBuilder, $configurationPool, $checkoutHelper);
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetItemData()
    {
        $imageHelper = $this->imageHelper->init($this->getProductForThumbnail(), 'mini_cart_product_thumbnail');
        $productName = $this->escaper->escapeHtml($this->item->getProduct()->getName());
        $odimage = $this->odfunc->getPrdampimg($this->item->getProduct()->getId());
        $sku = $this->item->getProduct()->getSku();
        $stockStatus = $this->stockRegistry->getStockStatusBySku($sku, $this->storeManager->getWebsite()->getId());
        $stockData = $stockStatus->getStockItem();
        $isInStock = $this->helperForCatalog->getProductVendorStock($this->item->getProduct()->getId());
        return [
            'options' => $this->getOptionList(),
            'qty' => $this->item->getQty() * 1,
            'item_id' => $this->item->getId(),
            'configure_url' => $this->getConfigureUrl(),
            'wishlist_data' => $this->getAddToWishlistParams(),
            'active_in_wishlist' => $this->getWishList(),
            'is_visible_in_site_visibility' => $this->item->getProduct()->isVisibleInSiteVisibility(),
            'is_in_stock' => $isInStock,
            'product_id' => $this->item->getProduct()->getId(),
            'product_name' => $productName,
            'product_sku' => $this->item->getProduct()->getSku(),
            'product_url' => $this->getProductUrl(),
            'product_has_url' => $this->hasProductUrl(),
            'product_price' => $this->checkoutHelper->formatPrice($this->item->getCalculationPrice()),
            'product_price_value' => $this->item->getCalculationPrice(),
            'product_image' => [
                'src' => $odimage,//$imageHelper->getUrl(),
                'alt' => $imageHelper->getLabel(),
                'width' => $imageHelper->getWidth(),
                'height' => $imageHelper->getHeight(),
            ],
            'canApplyMsrp' => $this->msrpHelper->isShowBeforeOrderConfirm($this->item->getProduct())
                && $this->msrpHelper->isMinimalPriceLessMsrp($this->item->getProduct()),
        ];
    }

    protected function getAddToWishlistParams()
    {
        $url = $this->redirect->getRefererUrl();
        $params = ['qty' => $this->item->getQty(),'currenturl' =>$url];
        return $this->wishlistHelper->getAddParams($this->item->getProduct(), $params);
    }

    public function getWishList()
    {
        $_product = $this->item->getProduct();
        $_in_wishlist = false;
        foreach ($this->wishlistHelper->getWishlistItemCollection() as $_wishlist_item){
            if($_product->getId() == $_wishlist_item->getProduct()->getId()){
                $_in_wishlist = true;
            }
        }
        if($this->_session->isLoggedIn()) 
        {
            if($_in_wishlist) { 
                return 'wishlist_active action towishlist amtheme-circle-icon'; 
            } else {
                return 'action towishlist amtheme-circle-icon';
            } 
        }
    }

}
