/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_GroupedProductOption
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

define(
    [
        'jquery',
        'Magento_Catalog/js/price-utils',
        'underscore',
        'mage/template',
        'Magento_Catalog/js/price-box',
        'jquery/ui'
    ],
    function ($, utils, _, mageTemplate) {
        'use strict';
        var bssItemId = '';
        $.widget('bss_groupedproductoption.priceBox', $.mage.priceBox, {
            groupItemPrices: [],
            originGroupItemPrices: [],
            groupElement: [],
            additionalPriceObjectCustom: {},

            _init: function initPriceBox() {
                var box = this.element,
                    self = this;

                box.trigger('updatePrice');
                _.each(self.options.priceConfig, function(index, el) {
                    self.cache.displayPrices[el] = utils.deepClone(index.prices);
                });
            },

            _create: function createPriceBox()
            {
                var box = this.element,
                    self = this;

                self.cache = {};
                self.cache.displayPrices = {};
                self._setDefaultsFromPriceConfig();
                self._setDefaultsFromDataSet();
                $('.bss-gpo-child-product-info .super-attribute-select').change(function () {
                    var id = $(this).closest('.product-options-wrapper').find('.bss-gpo-custom-option');
                    if (id && $(id).length) {
                        bssItemId = $(id).attr('data-product-id');
                    } else {
                        bssItemId = '';
                    }
                });
                $('.bss-gpo-child-product-info .super-attribute-select').on('gpo-update-attribute', function () {
                    var id = $(this).closest('.product-options-wrapper').find('.bss-gpo-custom-option');
                    if (id && $(id).length) {
                        bssItemId = $(id).attr('data-product-id');
                    } else {
                        bssItemId = '';
                    }
                });
                box.on('reloadPrice', self.reloadPrice.bind(self));
                box.on('updatePrice', self.onUpdatePrice.bind(self));
                _.each(this.options.priceConfig, function(index, productId) {
                    self.groupItemPrices[productId] = utils.deepClone(index.prices);
                    self.originGroupItemPrices[productId] = utils.deepClone(index.prices);
                    self.groupElement[productId] = $('#super-product-table [data-role="priceBox"][data-product-id="'+productId+'"]');
                });
            },

            updatePrice: function updatePrice(newPrices)
            {
                var prices = this.cache.displayPrices,
                    additionalPrice = {},
                    pricesCode = [],
                    priceValue, origin, finalPrice;
                if (newPrices && typeof (newPrices.productId) != 'undefined') {
                    bssItemId = newPrices.productId;
                    delete newPrices.productId;
                }

                if (bssItemId) {
                    prices = this.cache.displayPrices[bssItemId];
                    this.additionalPriceObjectCustom[bssItemId] = this.additionalPriceObjectCustom[bssItemId] || {};
                }
                this.cache.additionalPriceObject = this.cache.additionalPriceObject || {};

                if (newPrices) {
                    if (bssItemId) {
                        $.extend(this.additionalPriceObjectCustom[bssItemId], newPrices);
                        this.cache.additionalPriceObject = this.additionalPriceObjectCustom[bssItemId];
                    } else {
                        $.extend(this.cache.additionalPriceObject, newPrices);
                    }
                }

                if (!_.isEmpty(additionalPrice)) {
                    pricesCode = _.keys(additionalPrice);
                } else if (!_.isEmpty(prices)) {
                    pricesCode = _.keys(prices);
                }

                _.each(this.cache.additionalPriceObject, function (additional, key) {
                    if (additional && !_.isEmpty(additional)) {
                        pricesCode = _.keys(additional);
                    }
                    _.each(pricesCode, function (priceCode) {
                        var priceValue = additional[priceCode] || {};
                        priceValue.amount = +priceValue.amount || 0;
                        priceValue.adjustments = priceValue.adjustments || {};

                        additionalPrice[priceCode] = additionalPrice[priceCode] || {
                                'amount': 0,
                                'adjustments': {}
                            };
                        additionalPrice[priceCode].amount =  0 + (additionalPrice[priceCode].amount || 0)
                            + priceValue.amount;
                        _.each(priceValue.adjustments, function (adValue, adCode) {
                            additionalPrice[priceCode].adjustments[adCode] = 0
                                + (additionalPrice[priceCode].adjustments[adCode] || 0) + adValue;
                        });
                    });
                });

                if (_.isEmpty(additionalPrice) && bssItemId) {
                    this.cache.displayPrices[bssItemId] = utils.deepClone(this.options.priceConfig[bssItemId].prices);
                } else {
                    _.each(additionalPrice, function (option, priceCode) {
                        if (bssItemId != '') {
                            origin = this.originGroupItemPrices[bssItemId][priceCode] || {};
                            finalPrice = prices[priceCode] || {};
                            option.amount = option.amount || 0;
                            origin.amount = origin.amount || 0;
                            origin.adjustments = origin.adjustments || {};
                            finalPrice.adjustments = finalPrice.adjustments || {};

                            finalPrice.amount = 0 + origin.amount + option.amount;
                            _.each(option.adjustments, function (pa, paCode) {
                                finalPrice.adjustments[paCode] = 0 + (origin.adjustments[paCode] || 0) + pa;
                            });
                        }
                    }, this);
                }

                this.element.trigger('reloadPrice');
            },

            reloadPrice: function reDrawPrices()
            {
                var priceFormat = (this.options.priceConfig[bssItemId] && this.options.priceConfig[bssItemId].priceFormat) || {},
                    priceTemplate = mageTemplate(this.options.priceTemplate);

                if (bssItemId != '') {
                    var prices = this.cache.displayPrices[bssItemId];
                } else {
                    var prices = this.cache.displayPrices;
                }

                _.each(prices, function (price, priceCode) {
                    if (price.adjustments) {
                        price.final = _.reduce(price.adjustments, function (memo, amount) {
                            return memo + amount;
                        }, price.amount);

                        price.formatted = utils.formatPrice(price.final, priceFormat);
                        if (this.cache && bssItemId) {
                            if (this.groupElement[bssItemId].attr('data-product-id') != bssItemId) {
                                return;
                            }
                        }

                        if (bssItemId != '') {
                            var element = this.groupElement[bssItemId];
                        } else {
                            var element = this.element;
                        }
                        var elem = '#product-price-'+bssItemId+'[data-price-type="' + priceCode + '"]';
                        $(elem).html(priceTemplate({data: price}));
                    }
                }, this);
            }
        });

        return $.bss_groupedproductoption.priceBox;
    }
);
