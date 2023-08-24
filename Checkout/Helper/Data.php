<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Theme
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace MDC\Checkout\Helper;

use Magento\Framework\App\ObjectManager;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var \Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage
     */
    private $minimumAmountErrorMessage;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param FilterManager $filter
     * @param Session $customer
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Checkout\Helper\Cart $cartHelper
    ) {
        $this->cartHelper = $cartHelper;
        parent::__construct($context);
    }

    public function getMessages()
    {
        if (!$this->cartHelper->getQuote()->validateMinimumAmount()) {
            return $this->getMinimumAmountErrorMessage()->getMessage();
        } else {
            return '';
        }

    }

    /**
     * @return \Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage
     * @deprecated 100.1.0
     */
    private function getMinimumAmountErrorMessage()
    {
        if ($this->minimumAmountErrorMessage === null) {
            $objectManager = ObjectManager::getInstance();
            $this->minimumAmountErrorMessage = $objectManager->get(
                \Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage::class
            );
        }
        return $this->minimumAmountErrorMessage;
    }
}
