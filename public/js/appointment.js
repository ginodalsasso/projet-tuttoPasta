// Sélection des éléments et initialisation des variables
var csrfToken = $('meta[name="csrf-token"]').attr('content');

const $startDate = $("#appointment_startDate");
const $availableRdv = $("#available-rdv");
const $selectedSlot = $("#selectedSlot");
const $errorMsg = $("#date_error");

let dayoffDates = [];
let selectedLabel = null;

//___________________________________DOCUMENT READY_______________________________________
// Appeler la fonction pour initialiser Flatpickr dès que la page est prête
$(document).ready(function () {
    initFlatpickr();
    getSelectedDate();
    handleSlotSelection();
    handleServiceSelection();

    // Annulation d'un RDV sur la vue profil
    $(document).on('click', '.cancel_appointment', function(e) {
        e.preventDefault();
        var appointmentId = $(this).data('id');
        
        if (confirm("Êtes-vous sûr de vouloir annuler ce rendez-vous ?")) {
            cancelAppointment(appointmentId, csrfToken);
        }
    });

    // Messages d'erreurs UI
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

        const name = $("#appointment_name").val().trim();
        if (name === "" || name.length < 2 || name.length > 50) {
            $("#name_error").text("Le nom est invalide et doit contenir entre 2 et 50 caractères");
            $("#appointment_name").addClass("input_invalid");
            isValid = false;
        }

        const email = $("#appointment_email").val().trim();
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

//___________________________________FLATPICKR ET DAYOFFS_______________________________________
// Fonction pour récupérer les dayOffDates et initialiser Flatpickr
function initFlatpickr() {
    $.ajax({
        url: '/get_dayoff_dates',
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        },
        success: function(data) {
            $("#appointment_startDate").flatpickr({
                locale: "fr",
                dateFormat: "Y-m-d",
                minDate: "today",
                disable: [
                    function(date) {
                    // Désactiver les weekends
                        return (date.getDay() === 0 || date.getDay() === 6);
                    },
                    ...data.dayoffDates
                ]
            });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Erreur lors de la récupération des dates de congé :", textStatus, errorThrown);
        }
    });
}

//___________________________________INPUT HEURE RDV_______________________________________
// Détecte les changements de la date de début
function getSelectedDate() {

    $startDate.on("change", function () {
        const selectedDate = $startDate.val();
        $.ajax({
            url: available_rdv_ajax,
            contentType: "application/x-www-form-urlencoded",
            method: "POST",
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                startDate: selectedDate,
            },
            success: function (data) {
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
}
    
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
        } else {
            $label.removeClass("showRadioClass");
            $("#selectedService").val('');
        }
    });
}

// Fonction pour formater l'heure d'un créneau horaire en fonction de la locale 'fr-FR'
function formatTime(dateTimeString) {
    const dateTime = new Date(dateTimeString);
    return dateTime.toLocaleTimeString("fr-FR", {
        hour: "2-digit",
        minute: "2-digit",
    });
}


//___________________________________ANNULATION RDV_______________________________________
function cancelAppointment(appointmentId, csrfToken) {
    var url = `/profil/appointment/${appointmentId}/delete`;
    
    $.ajax({
        url: url,
        method: 'DELETE', 
        headers: {
            'X-CSRF-TOKEN': csrfToken  // Ajout du token CSRF dans les headers
        },
        success: function(data) {
            if (data.success) {
                // Supprimer le RDV de l'interface utilisateur
                $('#appointment-' + appointmentId).remove();
            } else {
                alert(data.message || "Erreur lors de la suppression du rendez-vous. Veuillez réessayer.");
                console.log(data);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Erreur lors de la suppression du rendez-vous :", textStatus, errorThrown);
            alert("Une erreur est survenue lors de la suppression du rendez-vous. Veuillez vérifier votre connexion et réessayer.");
        }
    });
}
