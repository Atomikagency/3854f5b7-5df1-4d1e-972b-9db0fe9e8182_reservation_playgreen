<h1>Stripe Connect</h1>
<script src="https://js.stripe.com/v3/"></script>
<form class="my-form" method="post">
    <input type="hidden" name="token-account" id="token-account">
    <input type="hidden" name="token-person" id="token-person">
    <input type="hidden" name="token-bank_account" id="token-btok">
    <?php wp_nonce_field('rp_generate_stripe_link', 'rp_generate_stripe_link_nonce'); ?>
    <style>
        h3{
            margin:15px 0 0 0;
        }
        tr td, tr th {
            padding: 5px !important;
        }
    </style>
    <table class="form-table">
        <tr>
            <th><h3>Compagnie</h3></th>
        </tr>
        <tr>
            <th scope="row">
                <label for="">Nom de compagnie</label>
            </th>
            <td>
                <input class="inp-company-name" name="company_name">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="">Téléphone de compagnie</label>
            </th>
            <td>
                <input class="inp-company-tel" name="company_tel">
                <small>format : +33606060606</small>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="">Adresse de compagnie</label>
            </th>
            <td>
                <input class="inp-company-street-address1" name="company_address">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="">Ville de compagnie</label>
            </th>
            <td>
                <input class="inp-company-city" name="company_city">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="">Region de compagnie</label>
            </th>
            <td>
                <input class="inp-company-state" name="company_state">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="">Code postal de compagnie</label>
            </th>
            <td>
                <input class="inp-company-zip" name="company_zip">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="">Site internet de compagnie</label>
            </th>
            <td>
                <input class="inp-company-website" name="company_website">
                <small>format attendu: www.website.com</small>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="">SIREN</label>
            </th>
            <td>
                <input class="inp-company-tax_id" name="company_tax_id">
            </td>
        </tr>
    </table>
    <table class="form-table">
        <tr>
            <th><h3>Réprésentant</h3></th>
        </tr>
        <tr>
            <th scope="row">
                <label for="">Prénom</label>
            </th>
            <td>
                <input class="inp-person-first-name" name="representant_firstname">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="">Nom</label>
            </th>
            <td>
                <input class="inp-person-last-name" name="representant_lastname">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="">Date de naissance</label></th>
            <td>
                <input class="inp-person-dob-day" name="representant_dob_day" placeholder="Jour" min="1" max="31" required>
                <input class="inp-person-dob-month" name="representant_dob_month" placeholder="Mois" min="1" max="12" required>
                <input class="inp-person-dob-year" name="representant_dob_year" placeholder="Année" min="1900" required>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="">Téléphone</label></th>
            <td>
                <input class="inp-person-phone" name="representant_phone" required>
                <small>format : +33606060606</small>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="">Email</label></th>
            <td><input class="inp-person-email" name="representant_email" required></td>
        </tr>
        <tr>
            <th scope="row">
                <label for="">Adresse</label>
            </th>
            <td>
                <input class="inp-person-street-address1" name="representant_address">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="">Ville</label>
            </th>
            <td>
                <input class="inp-person-city" name="representant_city">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="">Region</label>
            </th>
            <td>
                <input class="inp-person-state" name="representant_state">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="">Code postal</label>
            </th>
            <td>
                <input class="inp-person-zip"  name="representant_zip">
            </td>
        </tr>
    </table>
    <table class="form-table">
        <tr>
            <th><h3>Compte bancaire</h3></th>
        </tr>
        <tr>
            <th scope="row">
                <label for="">IBAN</label>
            </th>
            <td>
                <input class="inp-bank-iban" name="iban">
            </td>
        </tr>
    </table>
    <input type="hidden" name="generate_link" >
    <button class="button-primary" style="margin-top:10px">Créer un compte stripe connect</button>
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
                phone: document.querySelector('.inp-company-tel').value,
                tax_id: document.querySelector('.inp-company-tax_id').value
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
                dob: { // Date de naissance (nécessaire pour certains rôles)
                    day: parseInt(document.querySelector('.inp-person-dob-day').value),
                    month: parseInt(document.querySelector('.inp-person-dob-month').value),
                    year: parseInt(document.querySelector('.inp-person-dob-year').value),
                },
                relationship: {
                    director: true,
                    // executive: true,
                    owner: true,
                    percent_ownership:100.00,
                    representative: true,
                    title:'PDG'
                },
                phone: document.querySelector('.inp-person-phone').value,
                email: document.querySelector('.inp-person-email').value
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