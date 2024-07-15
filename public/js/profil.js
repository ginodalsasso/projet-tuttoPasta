$(document).ready(function () {
    
    // Sélection des éléments de la page
    const modalEditData = $(".modal_edit_data");
    const modalEditPassword = $(".modal_edit_password");
    const showModalData = $(".show_modal_edit_data");
    const showModalPassword = $(".show_modal_edit_password");
    const closeModal = $(".closeModal");

    // Affiche la modale de modification des informations utilisateur
    showModalData.on("click", function () {
        modalEditData[0].showModal();  // [0] pour accéder à l'élément natif
    });

    // Affiche la modale de modification du mot de passe
    showModalPassword.on("click", function () {
        modalEditPassword[0].showModal();  // [0] pour accéder à l'élément natif
    });

    // Ferme les modales lors du clic sur le bouton de fermeture
    closeModal.on("click", function () {
        modalEditData[0].close();  // [0] pour accéder à l'élément natif
        modalEditPassword[0].close();
    });

    // Ferme la fenêtre modale si l'utilisateur clique en dehors de celle-ci
    $(window).on("click", function (event) {
        if (event.target === modalEditData[0]) {
            modalEditData[0].close();
        } else if (event.target === modalEditPassword[0]) {
            modalEditPassword[0].close();
        }
    });

    // Validation des informations utilisateur lors de la soumission du formulaire
    $(document).on('submit', '#save_edit_data', function(event) {
        $(".error_msg").text(""); // Réinitialise les messages d'erreur
        $(".data").removeClass("input_invalid"); // Retire les classes d'erreur des champs

        let isValid = true;

        // Vérifie que chaque champ de données n'est pas vide
        $(".data").each(function () {
            if ($(this).val().trim() === "") {
                $(this).addClass("input_invalid");
                isValid = false;
            }
        });

        const username = $("#user_form_username").val().trim();
        const email = $("#user_form_email").val().trim();
        
        // Validation du pseudo
        if (!validateUsername(username)) {
            $("#pseudo_error").text("Pseudo invalide : doit commencer par une lettre, contenir 3-50 caractères, et n'inclure que lettres, chiffres, tirets et underscores.");
            $("#user_form_username").addClass("input_invalid");
            isValid = false;
        }

        // Validation de l'email
        if (!validateEmail(email)) {
            $("#email_error").text("L'email est invalide !");
            $("#user_form_email").addClass("input_invalid");
            isValid = false;
        }

        // Empêche la soumission du formulaire si des erreurs sont présentes
        if (!isValid) {
            event.preventDefault();
        }
    });

    // Validation du mot de passe utilisateur lors de la soumission du formulaire
    $(document).on('click', '#save_edit_password', function(event) {
        $(".error_msg").text(""); // Réinitialise les messages d'erreur
        $(".input_invalid").removeClass("input_invalid"); // Retire les classes d'erreur des champs

        const oldPassword = $("#edit_password_oldPassword").val().trim();
        const password1 = $("#edit_password_plainPassword_first").val().trim();
        const password2 = $("#edit_password_plainPassword_second").val().trim();

        let isValid = true;

        // Vérifie que tous les champs de mot de passe ne sont pas vides
        if (password1 === "" || password2 === "" || oldPassword === "") {
            $("#password_error").text("Les trois champs de mot de passe doivent être remplis !");
            $("#edit_password_oldPassword").addClass("input_invalid");
            $("#edit_password_plainPassword_first").addClass("input_invalid");
            $("#edit_password_plainPassword_second").addClass("input_invalid");
            isValid = false;
        } else if (password1 !== password2) {
            // Vérifie que les nouveaux mots de passe correspondent
            $("#password_error").text("Les mots de passe ne correspondent pas !");
            $("#edit_password_plainPassword_first").addClass("input_invalid");
            $("#edit_password_plainPassword_second").addClass("input_invalid");
            isValid = false;
        } else if (!validatePassword(password1)) {
            // Vérifie que le nouveau mot de passe respecte les critères de sécurité
            let errorMessage = "Le mot de passe est invalide et doit contenir :";
            if (!/(?=.*?[A-Z])/.test(password1)) {
                errorMessage += "<br>- Au moins une lettre majuscule";
            }
            if (!/(?=.*?[a-z])/.test(password1)) {
                errorMessage += "<br>- Au moins une lettre minuscule";
            }
            if (!/(?=.*?[0-9])/.test(password1)) {
                errorMessage += "<br>- Au moins un chiffre";
            }
            if (!/(?=.*?[#?!@$%^&*-])/.test(password1)) {
                errorMessage += "<br>- Au moins un caractère spécial (#?!@$%^&*-)";
            }
            if (!/.{13,}/.test(password1)) {
                errorMessage += "<br>- Au moins 13 caractères";
            }
            $("#password_error").html(errorMessage);
            $("#edit_password_plainPassword_first").addClass("input_invalid");
            $("#edit_password_plainPassword_second").addClass("input_invalid");
            isValid = false;
        }

        // Empêche la soumission du formulaire si des erreurs sont présentes
        if (!isValid) {
            event.preventDefault();
        }
    });
});
