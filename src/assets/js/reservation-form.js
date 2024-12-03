document.addEventListener("DOMContentLoaded", function () {
    const flatpickrInput = document.querySelector(".flatpickr");
    const timeSlotContainer = document.querySelector("#time-slot-container");
    const timeSlotSelect = document.querySelector("#reservation-time");
    const nbAdultesInput = document.querySelector("#reservation-adultes");
    const nbEnfantsInput = document.querySelector("#reservation-enfants");
    const totalField = document.querySelector("#reservation-total");
    const form = document.querySelector("#reservation-form");
    const errorContainer = document.querySelector("#reservation-error");
    let isValidated = false;

    const v = new FastestValidator();

    const schema = {
        reservation_date: {
            type: "string",
            optionnal: false,
            empty: false,
            messages: {
                stringEmpty: "Veuillez sélectionner une date de réservation",
                required: "Veuillez sélectionner une date de réservation",
            },
        },
        reservation_time: {
            type: "string",
            optionnal: false,
            empty: false,
            messages: {
                stringEmpty: "Veuillez sélectionner un créneau horaire",
                required: "Veuillez sélectionner un créneau horaire",
            },
        },
        reservation_nom: { type: "string", optionnal: false },
        reservation_prenom: { type: "string", optionnal: false },
        reservation_email: {
            type: "email",
            optionnal: false,
            messages: { email: "Veuillez renseigner une adresse email valide" },
        },
        reservation_langue: { type: "string", optionnal: false },
        reservation_adultes: { type: "number", optionnal: true, convert: true },
        reservation_enfants: { type: "number", optionnal: true, convert: true },
        reservation_code_promo: { type: "string", optionnal: true },
        reservation_cgv: { type: "boolean", convert: true },
    };

    const check = v.compile(schema);

    // Récupérer les données depuis PHP
    const prixAdulte = parseInt(rpReservationData.prixAdulte) || 0;
    const prixEnfant = parseInt(rpReservationData.prixEnfant) || 0;
    const availableDates = rpReservationData.availableDates;
    const unavailabilityDates = rpReservationData.unavailabilityDates || [];
    console.log(unavailabilityDates)
    // Mapper les jours de la semaine en index (0 = Dimanche, 1 = Lundi, ...)
    const dayMapping = {
        Dimanche: 0,
        Lundi: 1,
        Mardi: 2,
        Mercredi: 3,
        Jeudi: 4,
        Vendredi: 5,
        Samedi: 6,
    };

    // Filtrer les dates disponibles pour Flatpickr
    const enabledDates = [];
    const today = new Date();
    const daysToCheck = 365; // Vérifier les 365 prochains jours
    for (let i = 0; i < daysToCheck; i++) {
        const currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate() + i);
        const dayOfWeek = currentDate.getDay(); // Obtenir l'index du jour
        const dayName = Object.keys(dayMapping).find((key) => dayMapping[key] === dayOfWeek);
        const currentDateStr = flatpickr.formatDate(currentDate, "Y-m-d");

        // Vérifier si la date est dans les dates d'indisponibilité
        const isUnavailable = unavailabilityDates.some(dateRange => {
            const startDate = new Date(dateRange.start);
            const endDate = dateRange.end && dateRange.end !== '' ? new Date(dateRange.end) : startDate;
            return currentDateStr === flatpickr.formatDate(startDate, "Y-m-d") || (currentDate >= startDate && currentDate <= endDate);
        });

        console.log(isUnavailable)
        if (availableDates[dayName] && !isUnavailable) {
            enabledDates.push(currentDateStr);
        }
    }

    // Initialisation de Flatpickr
    flatpickr(flatpickrInput, {
        enable: enabledDates,
        dateFormat: "Y-m-d",
        onChange: function (selectedDates, dateStr) {
            // Afficher le sélecteur des créneaux horaires après la sélection d'une date
            if (dateStr) {
                const selectedDate = new Date(dateStr);
                const dayOfWeek = selectedDate.getDay();
                const dayName = Object.keys(dayMapping).find((key) => dayMapping[key] === dayOfWeek);

                // Remplir les créneaux horaires pour le jour sélectionné
                const slots = availableDates[dayName];
                timeSlotSelect.innerHTML = "";

                for (const [time, status] of Object.entries(slots)) {
                    if (status === "on") {
                        timeSlotSelect.innerHTML += `<option value="${time}">${time}</option>`;
                    }
                }

                // Afficher le sélecteur des créneaux horaires
                timeSlotSelect.removeAttribute("disabled");
            }
        },
    });

    // Mise à jour du total
    function updateTotal() {
        const nbAdultes = parseInt(nbAdultesInput.value) || 0;
        const nbEnfants = parseInt(nbEnfantsInput.value) || 0;

        const total = nbAdultes * prixAdulte + nbEnfants * prixEnfant;
        totalField.textContent = total;
    }

    function validateForm(e) {
        if (isValidated) {
            return;
        }

        e.preventDefault();

        const data = new FormData(form);
        // data to object
        const formData = Object.fromEntries(data.entries());

        let validatorRes = check(formData);

        const hasPlayer = formData.reservation_adultes + formData.reservation_enfants > 0;
        if (!hasPlayer) {
            if (Array.isArray(validatorRes)) {
                validatorRes.push({
                    type: "min",
                    message: "Veuillez sélectionner au moins un joueur enfant ou adulte",
                });
            } else {
                validatorRes = [
                    {
                        type: "min",
                        message: "Veuillez sélectionner au moins un joueur enfant ou adulte",
                    },
                ];
            }
        }

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
            form.submit();
        }
    }

    form.addEventListener("submit", validateForm);

    nbAdultesInput.addEventListener("input", updateTotal);
    nbEnfantsInput.addEventListener("input", updateTotal);
});
