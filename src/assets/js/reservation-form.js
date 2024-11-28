document.addEventListener('DOMContentLoaded', function () {
    const flatpickrInput = document.querySelector('.flatpickr');
    const timeSlotContainer = document.querySelector('#time-slot-container');
    const timeSlotSelect = document.querySelector('#reservation-time');
    const nbAdultesInput = document.querySelector('#reservation-adultes');
    const nbEnfantsInput = document.querySelector('#reservation-enfants');
    const totalField = document.querySelector('#reservation-total');

    // Récupérer les données depuis PHP
    const prixAdulte = parseInt(rpReservationData.prixAdulte) || 0;
    const prixEnfant = parseInt(rpReservationData.prixEnfant) || 0;
    const availableDates = rpReservationData.availableDates;

    // Mapper les jours de la semaine en index (0 = Dimanche, 1 = Lundi, ...)
    const dayMapping = {
        "Dimanche": 0,
        "Lundi": 1,
        "Mardi": 2,
        "Mercredi": 3,
        "Jeudi": 4,
        "Vendredi": 5,
        "Samedi": 6,
    };

    // Filtrer les dates disponibles pour Flatpickr
    const enabledDates = [];
    const today = new Date();
    for (let i = 0; i < 30; i++) { // Vérifier les 30 prochains jours
        const currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate() + i);
        const dayOfWeek = currentDate.getDay(); // Obtenir l'index du jour
        const dayName = Object.keys(dayMapping).find(key => dayMapping[key] === dayOfWeek);

        if (availableDates[dayName]) {
            enabledDates.push(flatpickr.formatDate(currentDate, 'Y-m-d'));
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
                const dayName = Object.keys(dayMapping).find(key => dayMapping[key] === dayOfWeek);

                // Remplir les créneaux horaires pour le jour sélectionné
                const slots = availableDates[dayName];
                timeSlotSelect.innerHTML = '';

                for (const [time, status] of Object.entries(slots)) {
                    if (status === 'on') {
                        timeSlotSelect.innerHTML += `<option value="${time}">${time}</option>`;
                    }
                }

                // Afficher le sélecteur des créneaux horaires
                timeSlotContainer.style.display = 'block';
            }
        },
    });

    // Mise à jour du total
    function updateTotal() {
        const nbAdultes = parseInt(nbAdultesInput.value) || 0;
        const nbEnfants = parseInt(nbEnfantsInput.value) || 0;

        const total = (nbAdultes * prixAdulte) + (nbEnfants * prixEnfant);
        totalField.textContent = total;
    }

    nbAdultesInput.addEventListener('input', updateTotal);
    nbEnfantsInput.addEventListener('input', updateTotal);
});
