// Sélection des éléments et initialisation des variables
$(document).ready(function () {
    const $startDate = $("#appointment_startDate");
    const $availableRdv = $("#available-rdv");
    const $selectedSlot = $("#selectedSlot");

    const $errorMsg = $("#date_error");
    
    
    let dayoffDates = [];
    let selectedLabel = null;

    // Fonction pour gérer la sélection des créneaux horaires
    function handleSlotSelection() {
        $availableRdv.on("change", "input[type='radio']", function () {
            const $input = $(this);
            const $label = $input.closest('label');
            const slot = $input.val();

            if ($input.is(":checked")) {
                if (selectedLabel) {
                    $(selectedLabel).removeClass("showRadioClass");
                }
                $label.addClass("showRadioClass");
                selectedLabel = $label;
                $selectedSlot.val(slot);
            }
        });
    }

    // Fonction pour gérer la sélection des services
    function handleServiceSelection() {

        $("#appointment_services").on("change","input[type='checkbox']", function (e) {
            const $input = $(this);
            const $label = $input.next('label');
            const slot = $input.val();

            if ($input.is(":checked")) {
                $label.addClass("showRadioClass");
                $("#selectedService").val(slot);
            }
        });
    }

    // Initialiser les sélections de créneaux horaires et de services
    handleSlotSelection();
    handleServiceSelection();

    // Détecte les changements de la date de début
    $startDate.on("change", function () {
        const selectedDate = $startDate.val();
        const selectedDay = new Date(selectedDate).getUTCDay();

        if (selectedDay === 0 || selectedDay === 6 || dayoffDates.includes(selectedDate)) {
            if (dayoffDates.includes(selectedDate)) {
                $errorMsg.text("La date sélectionnée tombe sur un jour de congé. Veuillez choisir une autre date.");
            } else {
                $errorMsg.text("Veuillez sélectionner un jour de semaine.");
            }
            $startDate.val("");
            $availableRdv.empty();
            return;
        } else {
            $errorMsg.text("");
        }

        $.ajax({
            url: ajaxUrl,
            contentType: "application/x-www-form-urlencoded",
            method: "POST",
            data: {
                startDate: selectedDate,
            },
            success: function (data) {
                dayoffDates = data.dayoffDates;
                if (Array.isArray(data.availabilities)) {
                    $availableRdv.empty();
                    const allSlots = data.availabilities[0];
                    $.each(allSlots, function (index, slot) {
                        const $label = $("<label>");
                        const $input = $("<input>", {
                            type: "radio",
                            name: "selectedSlotRadio",
                            value: slot,
                            class: "radioSlots",
                        });
                        $label.append($input).append(document.createTextNode(formatTime(slot)));
                        $availableRdv.append($label);
                    });
                } else {
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
    function validateEmail(email) {
        const emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        return emailReg.test(email);
    }

    $("#appointment_save").on("click", function (event) {
        $(".error_msg").text("");
        $(".data").removeClass("input_invalid");

        let isValid = true;

        $(".data").each(function () {
            if ($(this).val() === "") {
                $(this).addClass("input_invalid");
                isValid = false;
            }
        });

        const name = $("#appointment_name").val();
        if (name === "" || name.length < 2 || name.length > 50) {
            $("#name_error").text("Le nom est invalide et doit contenir entre 2 et 50 caractères");
            $("#appointment_name").addClass("input_invalid");
            isValid = false;
        }

        const email = $("#appointment_email").val();
        if (email === "" || !validateEmail(email)) {
            $("#email_error").text("L'email est invalide !");
            $("#appointment_email").addClass("input_invalid");
            isValid = false;
        }

        const message = $("#appointment_message").val();
        if (message === "" || message.length < 5) {
            $("#message_error").text("Le message est invalide et doit contenir au minimum 5 caractères");
            $("#appointment_message").addClass("input_invalid");
            isValid = false;
        }

        if (isValid) {
            $("#appointment_form").submit();
        }
    });
});
