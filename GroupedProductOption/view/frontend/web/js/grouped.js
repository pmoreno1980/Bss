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
    'Bss_GroupedProductOption/js/price-box',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($, priceBox) {
    'use strict';

    $.widget('bss.grouped', {
        options: {
            show_option: false,
            json: {}
        },

        _create: function () {
            this._initGrouped();
            this._initPriceBox();
        },

        _initGrouped: function () {
            var options = this.options;
            var optionCache = {},
                showOption = options.show_option;

            $.validator.addMethod(
                'required-option-field',
                function (value) {
                    return (value !== '');
                },
                $.mage.__('This is a required field.')
            );

            $('#product_addtocart_form').attr('enctype', 'multipart/form-data');

            $('#super-product-table input.qty').change(function () {
                var childId,
                    element,
                    qty = $(this).val();
                childId = $(this).attr('data-product-id');
                element = $('#super-product-table .bss-gpo-child-product-id-'+childId);
                if (!showOption) {
                    if (qty != 0) {
                        element.show();
                    } else {
                        $('#super-product-table .bss-gpo-child-product-id-'+childId).hide();
                    }
                } else {
                    element.find('.product-custom-option').each(function () {
                        var $this = $(this);
                        if (qty == 0) {
                            if ($this.hasClass('required')) {
                                $this.addClass('bss-required');
                                $this.removeClass('required');
                            }
                            if (typeof $this.attr('data-validate') !== typeof undefined && $this.attr('data-validate') !== false) {
                                var attrValue = $this.attr('data-validate');
                                $this.attr('data-validate-bss', attrValue);
                                $this.removeAttr('data-validate');
                                if ($this.hasClass('required-option-field')) {
                                    $this.removeClass('required-option-field');
                                    $this.attr('required-option-field', '1');
                                }
                            }
                            $this.removeClass('mage-error');
                        } else {
                            if ($this.hasClass('bss-required')) {
                                $this.addClass('required');
                                $this.removeClass('bss-required');
                            }
                            if (typeof $this.attr('data-validate-bss') !== typeof undefined && $this.attr('data-validate-bss') !== false) {
                                var attrValue2 = $this.attr('data-validate-bss');
                                $this.attr('data-validate', attrValue2);
                                $this.removeAttr('data-validate-bss');
                                if ($this.attr('required-option-field') === '1') {
                                    $this.addClass('required-option-field');
                                }
                            }
                        }
                    });
                }
                element.find('.super-attribute-select').each(function () {
                    var $this = $(this);
                    if (qty == 0) {
                        if ($this.hasClass('required-option-field')) {
                            $this.removeClass('required-option-field');
                        }
                    } else {
                        if (!$this.hasClass('required-option-field')) {
                            $this.addClass('required-option-field');
                        }
                    }
                });

                element.find('.bss-file').each(function () {
                    var $this = $(this);
                    if (qty == 0) {
                        if ($this.hasClass('validate-fileextensions')) {
                            $this.removeClass('validate-fileextensions');
                        }
                    } else {
                        if (!$this.hasClass('validate-fileextensions')) {
                            $this.addClass('validate-fileextensions');
                        }
                    }
                });

                element.find('.bss-file').each(function () {
                    var $this = $(this);
                    if (qty == 0) {
                        if ($this.hasClass('validate-image-height-width')) {
                            $this.removeClass('validate-image-height-width');
                        }
                    } else {
                        if (!$this.hasClass('validate-image-height-width')) {
                            $this.addClass('validate-image-height-width');
                        }
                    }
                });
            });

            $('#super-product-table input.qty').change();

            $('.action.tocart').mousedown(function(){
                $('#super-product-table input.qty').change();
            })
        },

        _initPriceBox: function () {
            var options = this.options;
            var priceBoxes = $('[data-role=priceBox]');

            priceBoxes = priceBoxes.filter(function (index, elem) {
                return !$(elem).find('.price-from').length;
            });

            priceBoxes.priceBox({'priceConfig': options.json});
        }
    });

    return $.bss.grouped;
});