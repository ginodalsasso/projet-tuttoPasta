
  /* document.addEventListener('DOMContentLoaded', function() {
                    var collectionHolder = document.querySelector('#services');
                        var prototype = collectionHolder.dataset.prototype;
                        var index = collectionHolder.dataset.index * 1 || collectionHolder.querySelectorAll('input').length;
                        var newForm = prototype.replace(/__name__/g, index);
                        collectionHolder.dataset.index = index + 1;
                        var newFormDiv = document.createElement('div');
                        newFormDiv.innerHTML = newForm;
                        collectionHolder.appendChild(newFormDiv);
                    })*/

    // Sélection des éléments et initialisation des variables
    $(document).ready(function () {
        const $startDate = $("#appointment_startDate");
        const $availableRdv = $("#available-rdv");
        const $selectedSlot = $("#selectedSlot");
        const $errorMsg = $("#date_error");
        var email = $("#email").val();

        let dayoffDates = [];
        let selectedLabel = null;
        // détecte les changements de la date de début
        $startDate.on("change", function () {
            // Récupère la date sélectionnée
            const selectedDate = $startDate.val();
            const selectedDay = new Date(selectedDate).getUTCDay();
            // Vérifie si le jour sélectionné est un jour de weekend
            if (selectedDay === 0 || selectedDay === 6 || dayoffDates.includes(selectedDate)) {
                if (dayoffDates.includes(selectedDate)) {
                    $errorMsg.text("La date sélectionnée tombe sur un jour de congé. Veuillez choisir une autre date.");
                    $(".data").addClass("input_invalid");
                } else {
                    $errorMsg.text("Veuillez sélectionner un jour de semaine.")
                    $(".data").addClass("input_invalid");
                }
                $startDate.val(""); // Réinitialise la date sélectionnée
                $availableRdv.empty(); // Vide les créneaux horaires disponibles
                return;
            } else {
                $errorMsg.text(""); // Vide le message d'erreur si une date valide est sélectionnée
            }
        // Envoie une requête POST pour obtenir les créneaux horaires disponibles pour la date sélectionnée
        $.ajax({
            url: ajaxUrl,
            contentType: "application/x-www-form-urlencoded", // Spécifie le type de contenu des données envoyées
            method: "POST",
            data: {
                startDate: selectedDate, // Envoie la date sélectionnée comme donnée du formulaire
            },
            success: function (data) {
                // Stocke les dates de congés
                dayoffDates = data.dayoffDates;
                // Vérifie si les créneaux horaires disponibles sont un tableau
                if (Array.isArray(data.availabilities)) {
                    // Réinitialise le conteneur des créneaux horaires disponibles
                    $availableRdv.empty();
                    // Récupère tous les créneaux horaires disponibles et réservés
                    const allSlots = data.availabilities[0];
                    // Parcourt tous les créneaux horaires disponibles
                    $.each(allSlots, function (index, slot) {
                        // Crée un élément label pour le créneau horaire
                        const $label = $("<label>");
                        // Crée un élément input pour le créneau horaire
                        const $input = $("<input>", {
                            type: "radio",
                            name: "selectedSlotRadio",
                            value: slot,
                            class: "radioSlots",
                        });
                        // Ajoute l'input radio et le texte du créneau horaire au label
                        $label.append($input).append(document.createTextNode(formatTime(slot)));
                        // Ajoute le label au conteneur des créneaux horaires disponibles
                        $availableRdv.append($label);
                        // Ajoute un écouteur d'événement pour mettre à jour la valeur du champ caché lorsque l'utilisateur sélectionne un créneau horaire
                        $input.on("change", function () {
                            // Vérifie si la case radio qui a déclenché l'événement est actuellement cochée/sélectionnée
                            if ($(this).is(":checked")) {
                                // Si un label a déjà été sélectionné précédemment
                                if (selectedLabel) {
                                    // Supprime la classe 'selected' du label précédemment sélectionné
                                    $(selectedLabel).removeClass("showRadioClass");
                                }
                                // Ajoute la classe 'selected' au label actuel
                                $label.addClass("showRadioClass");
                                // Met à jour le label sélectionné pour référence future
                                selectedLabel = $label;
                            }
                            $selectedSlot.val(slot);
                        });
                    });
                } else {
                    // Affiche une erreur dans la console si le format des données est invalide
                    console.error("Format de données invalide !");
                }
            },
        });
    });
    // Fonction pour formater l'heure d'un créneau horaire en fonction de la locale 'fr-FR'
    function formatTime(dateTimeString) {
        const dateTime = new Date(dateTimeString);
        return dateTime.toLocaleTimeString("fr-FR", {
            hour: "2-digit",
            minute: "2-digit",
        });
    }

    // Messages d'erreurs UI
    $("#appointment_save").on("click", function (event) {
        $(".error_msg").text("");
        var isValid = true;

        $(".data").removeClass("input_invalid");
        $( ".data" ).each(function( index ) {
            if ($(this).val() === ""){
                $(this).addClass("input_invalid");
                isValid = false;
            } 
        });

        if ($("#appointment_name").val() === "") {
            $("#name_error").text("Le nom est requis.");
            isValid = false;
        }

        if ($("#appointment_email").val() === "" || !validateEmail(email)) {
            $("#email_error").text("L'email est invalide !");
            isValid = false;
        }

        if ($("#appointment_message").val() === "") {
            $("#message_error").text("Le message est requis.");
            isValid = false;
        }

        function validateEmail(email) {
            var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
            return emailReg.test(email);
        }

        if (isValid) {
            $(this).closest('form').submit();
        } else {
            event.preventDefault();
        }
    })
})