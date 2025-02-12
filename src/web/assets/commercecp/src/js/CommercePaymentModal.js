if (typeof Craft.Commerce === typeof undefined) {
    Craft.Commerce = {};
}

/**
 * Class Craft.Commerce.PaymentModal
 */
Craft.Commerce.PaymentModal = Garnish.Modal.extend(
    {
        $container: null,
        $body: null,

        init: function(settings) {
            this.$container = $('<div id="paymentmodal" class="modal fitted loading"/>').appendTo(Garnish.$bod);

            this.base(this.$container, $.extend({
                resizable: false
            }, settings));

            var data = {
                orderId: settings.orderId,
                paymentForm: settings.paymentForm,
                paymentAmount: settings.paymentAmount,
                paymentCurrency: settings.paymentCurrency
            };

            Craft.postActionRequest('commerce/orders/get-payment-modal', data, $.proxy(function(response, textStatus) {
                this.$container.removeClass('loading');

                if (textStatus === 'success') {
                    if (response.success) {
                        var $this = this;
                        this.$container.append(response.modalHtml);
                        Craft.appendHeadHtml(response.headHtml);
                        Craft.appendFootHtml(response.footHtml);

                        var $buttons = $('.buttons', this.$container),
                            $cancelBtn = $('<div class="btn">' + Craft.t('commerce', 'Cancel') + '</div>').prependTo($buttons);

                        this.addListener($cancelBtn, 'click', 'cancelPayment');

                        $('select#payment-form-select').change($.proxy(function(ev) {
                            var id = $(ev.currentTarget).val();
                            $('.gateway-form').addClass('hidden');
                            $('#gateway-' + id + '-form').removeClass('hidden');

                            setTimeout(function() {
                                Craft.initUiElements(this.$container);
                                $this.updateSizeAndPosition();
                            }, 200);
                        }, this)).trigger('change');

                        setTimeout(function() {
                            Craft.initUiElements(this.$container);
                            $this.updateSizeAndPosition();
                        }, 200);
                    }
                    else {
                        var error = Craft.t('commerce', 'An unknown error occurred.');

                        if (response.error) {
                            error = response.error;
                        }

                        this.$container.append('<div class="body">' + error + '</div>');
                    }
                }
            }, this));

        },

        cancelPayment: function() {
            this.hide();
        }
    },
    {});
