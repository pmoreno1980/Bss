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
    'jquery/ui',
    'Magento_Catalog/js/catalog-add-to-cart'
], function($, _) {
    'use strict';

    $.widget('Bss_GroupedProductOption.catalogAddToCart', $.mage.catalogAddToCart, {
        /**
         * Handler for the form 'submit' event
         *
         * @param {Object} form
         */
        submitForm: function (form) {
            if ($('[name=bss-gpo]').length) {
                var addToCartButton,
                    self = this,
                    fileOption = false;
                _.each($('#super-product-table tbody input.qty'), function (qty) {
                    if ($(qty).val() > 0) {
                        var optionEl = $(qty).closest('tbody').find('.bss-gpo-custom-option');
                        if (optionEl.has('input[type="file"]').length && optionEl.find('input[type="file"]').val() !== '') {
                            fileOption = true;
                            return false;
                        }
                    }
                });
                if (fileOption) {
                    self.element.off('submit');
                    // disable 'Add to Cart' button
                    addToCartButton = $(form).find(this.options.addToCartButtonSelector);
                    addToCartButton.prop('disabled', true);
                    addToCartButton.addClass(this.options.addToCartButtonDisabledClass);
                    form.submit();
                } else {
                    self.ajaxSubmit(form);
                }
            } else {
                this._super(form);
            }
        }
    });

    return $.Bss_GroupedProductOption.catalogAddToCart;
});
