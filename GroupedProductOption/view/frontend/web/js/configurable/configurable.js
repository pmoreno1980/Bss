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

define([
    'jquery',
    'underscore',
    'mage/template',
    'mage/translate',
    'priceUtils',
    'jquery/ui',
    'Magento_ConfigurableProduct/js/configurable'
], function ($, _, mageTemplate, $t, priceUtils) {

    $.widget('bss.groupedProductOptionConfigurable', $.mage.configurable, {
        _init: function () {
            var productId = this.options.jsonGroupedConfigurable.productId,
                spConfigKey = 'spConfig-' + productId;
            this.options.spConfig = this.options[spConfigKey];
        },

        _configure: function (event) {
            $(event.currentTarget).trigger('gpo-update-attribute');
            this._super(event);
        },
        
        _initializeOptions: function () {
            var options = this.options,
                gallery = $(options.mediaGallerySelector),
                elem = $(this.options.priceHolderSelector + '[data-product-id=' + productId + ']'),
                priceBoxOptions = elem.priceBox('option').priceConfig || null,
                productId = this.options.jsonGroupedConfigurable.productId,
                spConfigKey = 'spConfig-' + productId,
                superSelector = '.super-attribute-select-' + productId;

            if (priceBoxOptions && priceBoxOptions.optionTemplate) {
                options.optionTemplate = priceBoxOptions.optionTemplate;
            }

            if (priceBoxOptions && priceBoxOptions.priceFormat) {
                options.priceFormat = priceBoxOptions.priceFormat;
            }
            options.optionTemplate = mageTemplate(options.optionTemplate);
            options.tierPriceTemplate = $(this.options.tierPriceTemplateSelector).html();

            options.settings = options[spConfigKey].containerId ?
                $(options[spConfigKey].containerId).find(superSelector) :
                $(superSelector);

            options.values = options[spConfigKey].defaultValues || {};
            options.parentImage = $('[data-role=base-image-container] img').attr('src');

            this.inputSimpleProduct = this.element.find(options.selectSimpleProduct);

            gallery.data('gallery') ?
                this._onGalleryLoaded(gallery) :
                gallery.on('gallery:loaded', this._onGalleryLoaded.bind(this, gallery));

        },
        _fillSelect: function (element) {
            var attributeId = $(element).attr('data-product').replace(/[a-z]*/, ''),
                options = this._getAttributeOptions(attributeId),
                prevConfig,
                index = 1,
                allowedProducts,
                i,
                j,
                productId = this.options.jsonGroupedConfigurable.productId,
                spConfigKey = 'spConfig-' + productId;

            this._clearSelect(element);
            element.options[0] = new Option('', '');
            element.options[0].innerHTML = this.options[spConfigKey].chooseText;
            prevConfig = false;

            if (element.prevSetting) {
                prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
            }

            if (options) {
                for (i = 0; i < options.length; i++) {
                    allowedProducts = [];

                    /* eslint-disable max-depth */
                    if (prevConfig) {
                        for (j = 0; j < options[i].products.length; j++) {
                            // prevConfig.config can be undefined
                            if (prevConfig.config &&
                                prevConfig.config.allowedProducts &&
                                prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1) {
                                allowedProducts.push(options[i].products[j]);
                            }
                        }
                    } else {
                        allowedProducts = options[i].products.slice(0);
                    }

                    if (allowedProducts.length > 0) {
                        options[i].allowedProducts = allowedProducts;
                        element.options[index] = new Option(this._getOptionLabel(options[i]), options[i].id);

                        if (typeof options[i].price !== 'undefined') {
                            element.options[index].setAttribute('price', options[i].prices);
                        }

                        element.options[index].config = options[i];
                        index++;
                    }

                    /* eslint-enable max-depth */
                }
            }
        },

        _fillState: function () {
            var productId = this.options.jsonGroupedConfigurable.productId,
                spConfigKey = 'spConfig-' + productId;
            $.each(this.options.settings, $.proxy(function (index, element) {
                var attributeId = $(element).attr('data-product').replace(/[a-z]*/, '');

                if (attributeId && this.options[spConfigKey].attributes[attributeId]) {
                    element.config = this.options[spConfigKey].attributes[attributeId];
                    element.attributeId = attributeId;
                    this.options.state[attributeId] = false;
                }
            }, this));
        },

        _configureForValues: function () {
            if (this.options.values) {
                this.options.settings.each($.proxy(function (index, element) {
                    var attributeId = $(element).attr('data-product');

                    element.value = this.options.values[attributeId] || '';
                    this._configureElement(element);
                }, this));
            }
        },

        _getAttributeOptions: function (attributeId) {
            var productId = this.options.jsonGroupedConfigurable.productId,
                spConfigKey = 'spConfig-' + productId;
            if (this.options[spConfigKey].attributes[attributeId]) {
                return this.options[spConfigKey].attributes[attributeId].options;
            }
        },

        _displayRegularPriceBlock: function (optionId) {
            var productId = this.options.jsonGroupedConfigurable.productId,
                spConfigKey = 'spConfig-' + productId;
            if (typeof optionId != 'undefined' &&
                this.options[spConfigKey].optionPrices[optionId].oldPrice.amount != //eslint-disable-line eqeqeq
                this.options[spConfigKey].optionPrices[optionId].finalPrice.amount
            ) {
                $(this.options.slyOldPriceSelector).show();
            } else {
                $(this.options.slyOldPriceSelector).hide();
            }
        },
        _changeProductImage: function () {
            return false;
        },

        _displayTierPriceBlock: function (optionId) {
            var options,
                tierPriceHtml,
                productId = this.options.jsonGroupedConfigurable.productId,
                spConfigKey = 'spConfig-' + productId;

            if (typeof optionId != 'undefined' &&
                this.options[spConfigKey].optionPrices[optionId].tierPrices != [] // eslint-disable-line eqeqeq
            ) {
                options = this.options[spConfigKey].optionPrices[optionId];

                if (this.options.tierPriceTemplate) {
                    tierPriceHtml = mageTemplate(this.options.tierPriceTemplate, {
                        'tierPrices': options.tierPrices,
                        '$t': $t,
                        'currencyFormat': this.options[spConfigKey].currencyFormat,
                        'priceUtils': priceUtils
                    });
                    $(this.options.tierPriceBlockSelector).html(tierPriceHtml).show();
                }
            } else {
                $(this.options.tierPriceBlockSelector).hide();
            }
        },

        _calculatePrice: function (config) {
            var productId = this.element.find('.bss-gpo-custom-option').attr('data-product-id'),
                elem = $(this.options.priceHolderSelector + '[data-product-id=' + productId + ']'),
                displayPrices = elem.priceBox('option').priceConfig[productId].prices,
                newPrices = this.options.spConfig.optionPrices[_.first(config.allowedProducts)];

            _.each(displayPrices, function (price, code) {
                if (newPrices[code]) {
                    displayPrices[code].amount = newPrices[code].amount - displayPrices[code].amount;
                }
            });

            return displayPrices;
        }
    });
    return $.bss.groupedProductOptionConfigurable;
});
