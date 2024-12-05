document.addEventListener("DOMContentLoaded", function () {
    const v = new FastestValidator();

    const schema = {
        theme: {
            type: "enum",
            optionnal: false,
            values: ["1", "2", "3"],
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
        message: {
            type: "string",
            optionnal: true,
            empty: true,
            messages: {
                stringEmpty: "Veuillez renseigner un message",
                required: "Veuillez renseigner un message",
            },
        },
        email: {
            type: "email",
            optionnal: false,
            empty: false,
            messages: {
                email: "Veuillez renseigner une adresse email valide",
                emailEmpty: "Veuillez renseigner votre adresse email",
            },
        },
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
        from: {
            type: "string",
            optionnal: false,
            empty: false,
            messages: {
                required: "Veuillez renseigner le nom du destinataire",
                emailEmpty: "Veuillez renseigner le nom du destinataire",
            },
        },
        to: {
            type: "string",
            optionnal: false,
            empty: false,
            messages: {
                required: "Veuillez renseigner le nom du destinataire",
                emailEmpty: "Veuillez renseigner le nom du destinataire",
            },
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
    }
});
