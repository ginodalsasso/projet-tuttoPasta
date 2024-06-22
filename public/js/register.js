    
    $(document).ready(function () {
        // Messages d'erreurs UI
        function validateEmail(email) {
            const emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
            return emailReg.test(email);
        }
    
        $("#register_save").on("click", function (event) {
            $(".error_msg").text("");
            $(".data").removeClass("input_invalid");
    
            let isValid = true;
    
            $(".data").each(function () {
                if ($(this).val() === "") {
                    $(this).addClass("input_invalid");
                    isValid = false;
                }
            });
    
            const name = $(".register_pseudo").val();
            if (name === "" || name.length < 2 || name.length > 50) {
                $("#name_error").text("Le nom est invalide et doit contenir entre 2 et 50 caractères");
                $("#appointment_name").addClass("input_invalid");
                isValid = false;
            }
    
            const email = $(".register_email").val();
            if (email === "" || !validateEmail(email)) {
                $("#email_error").text("L'email est invalide !");
                $("#appointment_email").addClass("input_invalid");
                isValid = false;
            }

            if (isValid) {
                $("#appointment_form").submit();
            }
        });
    })