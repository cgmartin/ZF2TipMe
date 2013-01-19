;(function(tipMe, undefined) {

    // Default options
    var defaults = {
        'stripePubKey'  : 'STRIPE_PUBLISHED_KEY',
        'recipientName' : 'RECIPIENT_NAME',
        'formSelector'  : '#tipForm',
        'confirmMessage': 'Send a gift of ${{amount}} from your {{cardType}} card to {{recipient}}?\nPressing "OK" will process the charge.',
        'spinner': {
            lines: 9, // The number of lines to draw
            length: 2, // The length of each line
            width: 2, // The line thickness
            radius: 3, // The radius of the inner circle
            corners: 1, // Corner roundness (0..1)
            rotate: 0, // The rotation offset
            color: '#fff', // #rgb or #rrggbb
            speed: 1, // Rounds per second
            trail: 60, // Afterglow percentage
            shadow: false, // Whether to render a shadow
            hwaccel: false, // Whether to use hardware acceleration
            className: 'spinner', // The CSS class to assign to the spinner
            zIndex: 2e9, // The z-index (defaults to 2000000000)
            top: 'auto', // Top position relative to parent in px
            left: 'auto' // Left position relative to parent in px
        }
    };
    
    var $tipForm, $submitBtn, spinner;

    tipMe.disableSubmitBtn = function() {
        $submitBtn.attr("disabled", "disabled");
        if (spinner) {
            spinner.spin(
                $('.spin', $submitBtn).css('display', 'inline-block')[0]
            );
        }
    };

    tipMe.enableSubmitBtn = function() {
        $submitBtn.removeAttr("disabled");
        if (spinner) {
            spinner.stop();
        }
        $('.spin', $submitBtn).hide();
    };

    tipMe.showError = function(group, message) {
        $('.' + group + '-group .errors').html(message).show();
        $('.' + group + '-group', $tipForm).addClass('error');
    };

    tipMe.hideError = function(group) {
        $('.' + group + '-group .errors').hide();
        $('.' + group + '-group', $tipForm).removeClass('error');
    };

    tipMe.validateForm = function() {
        var isValid = true;
        var focus   = null;

        tipMe.hideError('gift');
        if ($('input[name=tipOption]:checked', $tipForm).length == 0) {
            tipMe.showError('gift', 'Please select a gift');
            isValid = false; focus = '#tipOption';
        }
        tipMe.hideError('cardNumber');
        if (!Stripe.validateCardNumber($('#cardNumber').val())) {
            tipMe.showError('cardNumber', 'Invalid credit card number');
            isValid = false; focus = focus || '#cardNumber';
        }
        tipMe.hideError('expiration');
        if (!Stripe.validateExpiry($('#cardExpiryMonth').val(), $('#cardExpiryYear').val())) {
            tipMe.showError('expiration', 'Invalid credit card expiration');
            isValid = false; focus = focus || '#cardExpiryMonth';
        }
        tipMe.hideError('cardCvc');
        if (!Stripe.validateCVC($('#cardCvc').val())) {
            tipMe.showError('cardCvc', 'Invalid verification code');
            isValid = false; focus = focus || '#cardCvc';
        }

        if (focus) {
            $(focus).focus();
        }
        return isValid;
    };

    var paramTypeToGroupMap = {
        'number'    : 'cardNumber',
        'exp_month' : 'expiration',
        'exp_year'  : 'expiration',
        'cvc'       : 'cardCvc'
    };

    tipMe.stripeResponseHandler = function(status, response) {
        if (response.error) {
            tipMe.enableSubmitBtn();
            // show the errors on the form
            var errParam = response.error.param || 'number';
            tipMe.showError(paramTypeToGroupMap[errParam], response.error.message);
        } else {
            // token contains id, last4, and card type
            var token = response['id'];
            var amount   = $('input[name=tipOption]:checked', $tipForm).data('tip-amount');
            var cardType = Stripe.cardType($('#cardNumber').val());
            if (window.confirm(
                tipMe.options.confirmMessage
                    .replace( /\{\{amount\}\}/, amount)
                    .replace( /\{\{cardType\}\}/, cardType)
                    .replace( /\{\{recipient\}\}/, tipMe.options.recipientName)
            )) {
                // insert the token into the form so it gets submitted to the server
                $('#stripeToken').val(token);
                // and submit
                $tipForm.get(0).submit();
            } else {
                tipMe.enableSubmitBtn();
            }
        }
    };

    tipMe.changeGiftImage = function() {
        var imgSrc = $('input[name=tipOption]:checked', $tipForm).data('tip-image');
        $('.gift-image', $tipForm).html('<img src="' + imgSrc + '"/>');
        tipMe.hideError('gift');
    };

    tipMe.init = function(options) {
        options = options || {};
        tipMe.options = $.extend({}, defaults, options);

        // this identifies your website in the createToken call below
        Stripe.setPublishableKey(tipMe.options.stripePubKey);

        // Initialize the spinner animation (if exists)
        spinner = (Spinner) ? new Spinner(tipMe.options.spinner) : null;

        // Initialize the form
        $tipForm = $(tipMe.options.formSelector);
        $tipForm.show();

        $submitBtn = $('.btn-submit', $tipForm);
        $tipForm.submit(function(event) {
            event.preventDefault(); // submit from callback

            // disable the submit button to prevent repeated clicks
            tipMe.disableSubmitBtn();

            if (!tipMe.validateForm()) {
                tipMe.enableSubmitBtn();
                return;
            }

            // createToken returns immediately - the supplied callback submits the form if there are no errors
            Stripe.createToken({
                number:      $('#cardNumber').val(),
                cvc:         $('#cardCvc').val(),
                exp_month:   $('#cardExpiryMonth').val(),
                exp_year:    $('#cardExpiryYear').val()
            }, tipMe.stripeResponseHandler);
        });

        // Change images based on radio selection
        $('input[name=tipOption]').change(tipMe.changeGiftImage);
        if ($('input[name=tipOption]:checked', $tipForm).length != 0) {
            tipMe.changeGiftImage();
        }

        // Fill in with test data
        $(".test-data-btn", $tipForm).click(function() {
            $('#cardNumber').val($(this).data('tip-cnum'));
            $('#cardCvc').val('123');
            $('#cardExpiryMonth').val('10');
            $('#cardExpiryYear').val('2043');
        });

        // Preload images
        $('input[name=tipOption]', $tipForm).each(function(i, val) {
            var img = new Image();
            img.src = $(val).data('tip-image');
        });
    };

})(window.tipMe = window.tipMe || {});
