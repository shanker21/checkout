<?php
namespace MDC\Checkout\Plugin;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

class Delete 
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
    )
    {
        $this->_objectManager = $context->getObjectManager();
        $this->_redirect = $context->getRedirect();
        $this->resultRedirectFactory = $resultRedirectFactory;
    }
   
    public function afterExecute(\Magento\Checkout\Controller\Cart\Delete $subject, $result)
    {
        $defaultUrl = $this->_objectManager->create(\Magento\Framework\UrlInterface::class)->getUrl('checkout/cart');
        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl($defaultUrl));
    }
}
