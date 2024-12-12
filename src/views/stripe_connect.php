<script src="https://js.stripe.com/v3/"></script>
<form class="my-form" method="post">
    <input type="hidden" name="token-account" id="token-account">
    <input type="hidden" name="token-person" id="token-person">
    <input type="hidden" name="token-bank_account" id="token-btok">
    <?php wp_nonce_field('rp_generate_stripe_link', 'rp_generate_stripe_link_nonce'); ?>

    <label>
        <span>Business Name</span>
        <input class="inp-company-name">
    </label>
    <fieldset>
        <legend>Business Address</legend>
        <label>
            <span>Street Address Line 1</span>
            <input class="inp-company-street-address1">
        </label>
        <label>
            <span>City</span>
            <input class="inp-company-city">
        </label>
        <label>
            <span>State</span>
            <input class="inp-company-state">
        </label>
        <label>
            <span>Postal Code</span>
            <input class="inp-company-zip">
        </label>
    </fieldset>
    <label>
        <span>Representative First Name</span>
        <input class="inp-person-first-name">
    </label>
    <label>
        <span>Representative Last Name</span>
        <input class="inp-person-last-name">
    </label>
    <fieldset>
        <legend>Representative Address</legend>
        <label>
            <span>Street Address Line 1</span>
            <input class="inp-person-street-address1">
        </label>
        <label>
            <span>City</span>
            <input class="inp-person-city">
        </label>
        <label>
            <span>State</span>
            <input class="inp-person-state">
        </label>
        <label>
            <span>Postal Code</span>
            <input class="inp-person-zip">
        </label>
    </fieldset>

    <label>
        <span>IBAN</span>
        <input class="inp-bank-iban">
    </label>

    <input type="hidden" name="generate_link">
    <button>Submit</button>
</form>


<script>
    // Assumes you've already included Stripe.js!
    const stripe = Stripe('pk_test_uiRSo292LL6MLyMpsd7pHjZo00PsJ3pq42');
    const myForm = document.querySelector('.my-form');
    myForm.addEventListener('submit', handleForm);

    async function handleForm(event) {
        event.preventDefault();

        // Création du token "account"
        const accountResult = await stripe.createToken('account', {
            business_type: 'company',
            company: {
                name: document.querySelector('.inp-company-name').value,
                address: {
                    line1: document.querySelector('.inp-company-street-address1').value,
                    city: document.querySelector('.inp-company-city').value,
                    state: document.querySelector('.inp-company-state').value,
                    postal_code: document.querySelector('.inp-company-zip').value,
                },
            },
            tos_shown_and_accepted: true,
        });

        // Création du token "person"
        const personResult = await stripe.createToken('person', {
            person: {
                first_name: document.querySelector('.inp-person-first-name').value,
                last_name: document.querySelector('.inp-person-last-name').value,
                address: {
                    line1: document.querySelector('.inp-person-street-address1').value,
                    city: document.querySelector('.inp-person-city').value,
                    state: document.querySelector('.inp-person-state').value,
                    postal_code: document.querySelector('.inp-person-zip').value,
                },
            },
        });

        // Création du token de compte bancaire (IBAN)
        const bankAccountResult = await stripe.createToken('bank_account', {
            bank_account: {
                country: 'FR', // Adapter en fonction du pays
                currency: 'eur',
                account_holder_name: document.querySelector('.inp-company-name').value,
                account_holder_type: 'company',
                account_number: document.querySelector('.inp-bank-iban').value,
                iban: document.querySelector('.inp-bank-iban').value,
            },
        });

        if (accountResult.token && personResult.token && bankAccountResult.token) {
            document.querySelector('#token-account').value = accountResult.token.id;
            document.querySelector('#token-person').value = personResult.token.id;
            document.querySelector('#token-btok').value = bankAccountResult.token.id;
            myForm.submit();
        }
    }
</script>
