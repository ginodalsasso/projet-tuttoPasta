$(document).ready(function () {
    // Messages d'erreurs UI
    $("#login_save").on("click", function (event) {
        $(".error_msg").text("");
        $(".data").removeClass("input_invalid");
        let isValid = true;

        $(".data").each(function () {
            if ($(this).val().trim() === "") {
                $(this).addClass("input_invalid");
                isValid = false;
            }
        });


        const email = $("#username").val().trim();
        if (email === "" || !validateEmail(email)) {
            $("#email_error").text("L'email est invalide !");
            $("#username").addClass("input_invalid");
            isValid = false;
        }

        const password = $("#password").val().trim();

        if (password === "") {
            $("#password_error").text("Le mot de passe est invalide !");
            $("#password").addClass("input_invalid");
            isValid = false;
        } else if (!validatePassword(password)) {
            let errorMessage = "Le mot de passe est invalide et doit contenir :";
            if (!/(?=.*?[A-Z])/.test(password)) {
                errorMessage += "<br>- Au moins une lettre majuscule";
            }
            if (!/(?=.*?[a-z])/.test(password)) {
                errorMessage += "<br>- Au moins une lettre minuscule";
            }
            if (!/(?=.*?[0-9])/.test(password)) {
                errorMessage += "<br>- Au moins un chiffre";
            }
            if (!/(?=.*?[#?!@$%^&*-])/.test(password)) {
                errorMessage += "<br>- Au moins un caractère spécial (#?!@$%^&*-)";
            }
            if (!/.{13,}/.test(password)) {
                errorMessage += "<br>- Au moins 13 caractères";
            }
            $("#password_error").html(errorMessage);
            $("#password").addClass("input_invalid");
            isValid = false;
        }
        if (isValid) {
            $("#login_save").submit();
        } else {
            event.preventDefault();
        }
    });
});
