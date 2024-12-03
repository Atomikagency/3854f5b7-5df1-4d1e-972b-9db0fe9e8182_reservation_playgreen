document.addEventListener("DOMContentLoaded", function () {
    const v = new FastestValidator();

    const schema = {
        theme: {
            type: "enum",
            optionnal: false,
            values: ["1", "2", "3", "4"],
            messages: {
                required: "Veuillez sélectionner un thème",
            },
        },
        montant: {
            type: "number",
            optionnal: false,
            convert: true,
            positive: true,
            messages: {
                numberPositive: "Veuillez sélectionner un montant",
            },
        },
        add_message: { type: "boolean", convert: true },
        message: {
            type: "string",
            optionnal: false,
            empty: false,
            messages: {
                stringEmpty: "Veuillez renseigner un message",
                required: "Veuillez renseigner un message",
            },
        },
        email: {
            type: "email",
            optionnal: false,
            messages: {
                email: "Veuillez renseigner une adresse email valide",
                emailEmpty: "Veuillez renseigner votre adresse email",
            },
        },
        send_direct: { type: "boolean", convert: true },
        emailSend: {
            type: "email",
            optionnal: false,
            empty: false,
            messages: {
                emailEmpty: "Veuillez renseigner l'adresse email du destinataire",
                email: "Veuillez renseigner une adresse email de destinataire valide",
            },
        },
        rgpd: {
            type: "boolean",
            convert: true,
            optionnal: false,
            messages: { required: "Veuillez accepter les conditions RGPD" },
        },
    };

    let isValid = false;
    const giftCardForm = document.querySelector("#gift_card");
    const errorContainer = document.querySelector("#gift-card-error");
    if (giftCardForm) {
        giftCardForm.addEventListener("submit", function (e) {
            if (isValid) {
                return;
            }
            e.preventDefault();
            const check = v.compile(schema);

            const data = new FormData(giftCardForm);
            const formData = { ...{ send_direct: false, add_message: false }, ...Object.fromEntries(data.entries()) };
            let validatorRes = check(formData);

            if (Array.isArray(validatorRes) && validatorRes.length > 0) {
                errorContainer.innerHTML = "";
                validatorRes.forEach((v) => {
                    const elem = document.createElement("p");
                    elem.style.paddingBottom = "5px";
                    elem.innerText = v.message;
                    errorContainer.appendChild(elem);
                });
                return;
            } else {
                isValidated = true;
                giftCardForm.submit();
            }
        });

        // Handle montant value
        const presetAmounts = giftCardForm.querySelectorAll('input[name="preset_amount"]');
        const customAmount = giftCardForm.querySelector('input[name="custom_amount"]');
        const montantHidden = giftCardForm.querySelector('input[name="montant"]');

        presetAmounts.forEach((radio) => {
            radio.addEventListener("change", () => {
                montantHidden.value = radio.value;
                customAmount.value = ""; // Clear custom amount
                customAmount.removeAttribute("data-filled");
            });
        });

        customAmount.addEventListener("focus", function () {
            montantHidden.value = "";
            presetAmounts.forEach((radio) => (radio.checked = false)); // Uncheck preset options
        });

        customAmount.addEventListener("input", function () {
            montantHidden.value = this.value;
            if (this.value) {
                customAmount.setAttribute("data-filled", null);
            } else {
                customAmount.removeAttribute("data-filled");
            }
        });

        // Handle message visibility
        const messageCheckbox = giftCardForm.querySelector('input[name="add_message"]');
        const messageTextarea = giftCardForm.querySelector('textarea[name="message"]');

        messageCheckbox.addEventListener("change", function () {
            schema.message.empty = !this.checked;
            schema.message.optionnal = !this.checked;
            messageTextarea.style.display = this.checked ? "block" : "none";
        });

        // Handle emailSend visibility
        const sendDirectCheckbox = giftCardForm.querySelector('input[name="send_direct"]');
        const emailSendField = giftCardForm.querySelector('input[name="emailSend"]');

        sendDirectCheckbox.addEventListener("change", function () {
            schema.emailSend.empty = !this.checked;
            schema.emailSend.optionnal = !this.checked;
            emailSendField.style.display = this.checked ? "block" : "none";
        });
    }
});
