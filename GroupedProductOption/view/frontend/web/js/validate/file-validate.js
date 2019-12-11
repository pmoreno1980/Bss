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
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($) {

    $.validator.addMethod(
        'validate-fileextensions', function (v, elm) {
            var extensionsList = window.Extension;
            var extensions = extensionsList.split(',');
            if (!v) {
                return true;
            }
            with (elm) {
                var ext = value.substring(value.lastIndexOf('.') + 1);
                for (var i = 0; i < extensions.length; i++) {
                    if (ext === extensions[i]) {
                        return true;
                    }
                }
            }
            return false;
        }, $.mage.__('Disallowed file type.')
    );

    $.validator.addMethod(
        'validate-image-height-width', function (v, elm) {

            if (!v) {
                return true;
            }
            with (elm) {
                if ((elm.files[0].name)) {
                    return !(window.imgHeight > window.imgMaxHeight || window.imgWidth > window.imgMaxWidth);
                }
            }
            return false;
        }, $.mage.__('Height and Width must not exceed '+ window.imgMaxHeight + 'px.'));
});