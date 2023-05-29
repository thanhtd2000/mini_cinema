<footer></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" type="module"></script>
<script>
    const FUNDING_SOURCES = [
        paypal.FUNDING.PAYPAL,
        paypal.FUNDING.CARD
    ];
    FUNDING_SOURCES.forEach(fundingSource => {
        paypal.Buttons({
                fundingSource,
                style: {
                    layout: 'vertical',
                    shape: 'rect',
                    color: (fundingSource == paypal.FUNDING.PAYLATER) ? 'gold' : '',
                },
                createOrder: async (data, actions) => {
                    const response = await fetch("/orders", {
                        method: "POST",
                    });
                    const details = await response.json();
                    return details.id;
                },

                onApprove: async (data, actions) => {
                    const response = await fetch(`/orders/${data.orderID}/capture`, {
                        method: "POST",
                    });
                    const details = await response.json();
                    // Three cases to handle:
                    //   (1) Recoverable INSTRUMENT_DECLINED -> call actions.restart()
                    //   (2) Other non-recoverable errors -> Show a failure message
                    //   (3) Successful transaction -> Show confirmation or thank you

                    // This example reads a v2/checkout/orders capture response, propagated from the server
                    // You could use a different API or structure for your 'orderData'

                    const errorDetail = Array.isArray(details.details) && details.details[0];
                    if (errorDetail && errorDetail.issue === 'INSTRUMENT_DECLINED') {
                        return actions.restart(); // Recoverable state, per:
                        // https://developer.paypal.com/docs/checkout/integration-features/funding-failure/
                    }

                    if (errorDetail) {
                        let msg = 'Sorry, your transaction could not be processed.';
                        if (errorDetail.description) msg += '\n\n' + errorDetail.description;
                        if (details.debug_id) msg += ' (' + details.debug_id + ')';
                        return alert(
                        msg); // Show a failure message (try to avoid alerts in production environments)
                    }

                    // Successful capture! For demo purposes:
                    console.log('Capture result', details, JSON.stringify(details, null, 2));
                    const transaction = details.purchase_units[0].payments.captures[0];
                    alert('Transaction ' + transaction.status + ': ' + transaction.id +
                        '\n\nSee console for all available details');
                },
            })
            .render("#paypal-button-container");
    })
</script>
