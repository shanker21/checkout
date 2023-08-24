define(
    [
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-address/form-popup-state',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/checkout-data',
        'uiRegistry',
        'mage/translate',
        'Magento_Checkout/js/model/shipping-rate-service'
    ],
    function (
        ko,
        Component,
        _,
        stepNavigator,
        customer,
        addressList,
        addressConverter,
        quote,
        createShippingAddress,
        selectShippingAddress,
        shippingRatesValidator,
        formPopUpState,
        shippingService,
        selectShippingMethodAction,
        rateRegistry,
        setShippingInformationAction,
        modal,
        checkoutDataResolver,
        checkoutData,
        registry,
        $t
    ) {
        'use strict';
        /**
        * check-login - is the name of the component's .html template
        */
        return Component.extend({
           /* defaults: {
                template: 'MDC_Checkout/check-delivery'
            },*/

            isVisible: ko.observable(true),
            errorValidationMessage: ko.observable(false),
            stepTitle: 'Delivery Slot',

            /**
            *
            * @returns {*}
            */
            initialize: function () {
                this._super();
                /*stepNavigator.registerStep(
                    this.stepCode,

                    null,
                    this.stepTitle,

                    this.isVisible,

                    _.bind(this.navigate, this),


                    15
                );*/

                return this;
            },

            /**
            * The navigate() method is responsible for navigation between checkout step
            * during checkout. You can add custom logic, for example some conditions
            * for switching to your custom step
            */
            navigate: function () {
                var self = this;
                self.isVisible(true);
                
            },

            /**
            * @returns void
            */
            navigateToNextStep: function () {
                if (this.validateShippingInformation()) {
                    setShippingInformationAction().done(
                            function () {
                                stepNavigator.next();
                            }
                    );
                }
            },
            /**
            * @return {Boolean}
            */
            validateShippingInformation: function () {
                var shippingAddress,
                        addressData,
                        loginFormSelector = 'form[data-role=email-with-possible-login]',
                        emailValidationResult = customer.isLoggedIn(),
                        field;

                if (!quote.shippingMethod()) {
                    this.errorValidationMessage($t('Please specify a shipping method.'));

                    return false;
                }

                var deliverydate = jQuery('[name="delivery_date"]').val();

                if (isScheduleShippingEnable && isScheduleShippingEnableForCustomer) {
                    if (isDeliveryDateMandatory) {
                        if (!deliverydate) {
                            this.errorValidationMessage('Please specify a Delivery Date');
                            return false;
                        }
                    }
                    ;
                }
                ;

                /*if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }*/

                if (this.isFormInline) {
                    this.source.set('params.invalid', false);
                    this.triggerShippingDataValidateEvent();

                    if (emailValidationResult &&
                            this.source.get('params.invalid') ||
                            !quote.shippingMethod()['method_code'] ||
                            !quote.shippingMethod()['carrier_code']
                            ) {
                        this.focusInvalid();

                        return false;
                    }

                    shippingAddress = quote.shippingAddress();
                    addressData = addressConverter.formAddressDataToQuoteAddress(
                            this.source.get('shippingAddress')
                            );

                    for (field in addressData) {
                        if (addressData.hasOwnProperty(field) &&
                                shippingAddress.hasOwnProperty(field) &&
                                typeof addressData[field] != 'function' &&
                                _.isEqual(shippingAddress[field], addressData[field])
                                ) {
                            shippingAddress[field] = addressData[field];
                        } else if (typeof addressData[field] != 'function' &&
                                !_.isEqual(shippingAddress[field], addressData[field])) {
                            shippingAddress = addressData;
                            break;
                        }
                    }

                    if (customer.isLoggedIn()) {
                        shippingAddress['save_in_address_book'] = $('#newadd').val() == '1' ? 1 : 0;
                    }
                    selectShippingAddress(shippingAddress);
                }

                /*if (!emailValidationResult) {
                    $(loginFormSelector + ' input[name=username]').focus();

                    return false;
                }*/

                return true;
            }
        });
    }
);