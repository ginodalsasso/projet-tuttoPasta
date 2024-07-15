
$(document).ready(function () {
    
    const modalEditData = $(".modal_edit_data");
    const modalEditPassword = $(".modal_edit_password");
    const showModalData = $(".show_modal_edit_data");
    const showModalPassword = $(".show_modal_edit_password");
    const closeModal = $(".closeModal");

    showModalData.on("click", function () {
        modalEditData[0].showModal();  // [0] pour accéder à l'élément natif
    });

    showModalPassword.on("click", function () {
        modalEditPassword[0].showModal();  // [0] pour accéder à l'élément natif
    });

    closeModal.on("click", function () {
        modalEditData[0].close();  // [0] pour accéder à l'élément natif
        modalEditPassword[0].close();
    });

    // Ferme la fenêtre au click hors de la modal
    $(window).on("click", function (event) {
        if (event.target === modalEditData[0]) {
            modalEditData[0].close();
        } else if (event.target === modalEditPassword[0]) {
            modalEditPassword[0].close();
        }
    });


    // Validation des informations utilisateur
    $("#save_edit_data").on("click", function (event) {
        $(".error_msg").text("");
        $(".data").removeClass("input_invalid");
        let isValid = true;

        $(".data").each(function () {
            if ($(this).val().trim() === "") {
                $(this).addClass("input_invalid");
                isValid = false;
            }
        });

        const username = $("#user_form_username").val().trim();
        if (!validateUsername(username)) {
            $("#pseudo_error").text("Pseudo invalide : doit commencer par une lettre, contenir 3-50 caractères, et n'inclure que lettres, chiffres, tirets et underscores.");
            $("#user_form_username").addClass("input_invalid");  // Correction du sélecteur
            isValid = false;
        }

        const email = $("#user_form_email").val().trim();
        if (email === "" || !validateEmail(email)) {
            $("#email_error").text("L'email est invalide !");
            $("#user_form_email").addClass("input_invalid");  // Correction du sélecteur
            isValid = false;
        }
        if (!isValid) {
            event.preventDefault();
        }
    });

    // Validation du password utilisateur
    $("#save_edit_password").on("click", function (event) {
        $(".error_msg").text(""); // Reset any existing error messages
        $(".input_invalid").removeClass("input_invalid"); // Reset invalid input classes

        const password1 = $("#change_password_form_plainPassword_first").val().trim();
        const password2 = $("#change_password_form_plainPassword_second").val().trim();

        let isValid = true;

        if (password1 === "" || password2 === "") {
            $("#password_error").text("Les deux champs de mot de passe doivent être remplis !");
            $("#change_password_form_plainPassword_first").addClass("input_invalid");
            $("#change_password_form_plainPassword_second").addClass("input_invalid");
            isValid = false;
        } else if (password1 !== password2) {
            $("#password_error").text("Les mots de passe ne correspondent pas !");
            $("#change_password_form_plainPassword_first").addClass("input_invalid");
            $("#change_password_form_plainPassword_second").addClass("input_invalid");
            isValid = false;
        } else if (!validatePassword(password1)) {
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
            $("#change_password_form_plainPassword_first").addClass("input_invalid");
            $("#change_password_form_plainPassword_second").addClass("input_invalid");
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
        }
    });
});