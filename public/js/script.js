
function validateEmail(email) {
    const emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
    return emailReg.test(email);
}


function validatePassword(password) {
    const passwordReg = /^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{13,}$/;
    return passwordReg.test(password);
}

function validateUsername(username) {
    const usernameReg = /^[a-zA-Z][a-zA-Z0-9_-]{2,49}$/;
    return usernameReg.test(username);
}
