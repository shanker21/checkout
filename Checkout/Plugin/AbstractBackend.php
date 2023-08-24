<?php
namespace MDC\Checkout\Plugin;

class AbstractBackend
{
    public function beforeValidate(
        \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend $subject,
        $object
    ) {
            $attribute = $subject->getAttribute();
            $attrCode = $attribute->getAttributeCode();

            if ($attrCode == 'lastname') {
                $attribute->setIsRequired(0);
            }
            if ($attrCode == 'firstname') {
                $attribute->setIsRequired(0);
            }
    }
}

