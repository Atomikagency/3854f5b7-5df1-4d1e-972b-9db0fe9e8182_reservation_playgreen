document.addEventListener('DOMContentLoaded', function () {
    const giftCardForm = document.querySelector('#gift_card');
    if (giftCardForm) {
        // Handle montant value
        const presetAmounts = giftCardForm.querySelectorAll('input[name="preset_amount"]');
        const customAmount = giftCardForm.querySelector('input[name="custom_amount"]');
        const montantHidden = giftCardForm.querySelector('input[name="montant"]');

        presetAmounts.forEach(radio => {
            radio.addEventListener('change', () => {
                montantHidden.value = radio.value;
                customAmount.value = ''; // Clear custom amount
            });
        });

        customAmount.addEventListener('input', function () {
            montantHidden.value = this.value;
            presetAmounts.forEach(radio => radio.checked = false); // Uncheck preset options
        });

        // Handle message visibility
        const messageCheckbox = giftCardForm.querySelector('input[name="add_message"]');
        const messageTextarea = giftCardForm.querySelector('textarea[name="message"]');

        messageCheckbox.addEventListener('change', function () {
            messageTextarea.style.display = this.checked ? 'block' : 'none';
        });

        // Handle emailSend visibility
        const sendDirectCheckbox = giftCardForm.querySelector('input[name="send_direct"]');
        const emailSendField = giftCardForm.querySelector('input[name="emailSend"]');

        sendDirectCheckbox.addEventListener('change', function () {
            emailSendField.style.display = this.checked ? 'block' : 'none';
        });
    }
});
