const form = document.querySelector("form"); // Works only if there is a single form
const nameField = document.getElementById("nombre");
const email = document.getElementById("email");
const phone = document.getElementById("telefono");
const password = document.getElementById("contrasenya");
const errors = document.getElementById("errores");

function validacion() {
    // Remove error class before validation
    if (nameField) nameField.classList.remove("error");
    if (email) email.classList.remove("error");
    if (phone) phone.classList.remove("error");
    if (password) password.classList.remove("error");

    // Store error messages
    let errorMessages = [];

    /*
        General explanation of regular expressions:
        ^       → start of the string
        $       → end of the string
    */
    const regExp_whitespace = /^\s+$/;

    /* 
        Validates an email with basic format user@server.com
        .+   → One or more characters of any type (except line break) (user)
        \@   → The at symbol (@)
        .+   → Again, one or more characters of any type (server)
        \.   → Mandatory dot before extension
        .+   → Again, one or more characters of any type (com)
    */
    const regExp_email = /^(.+\@.+\..+)$/ ;

    /* 
        Validates a phone number with exactly 9 digits
        \d{9}   → exactly 9 digits (0–9)
    */
    const regExp_phone = /^\d{9}$/;

    /*
        Validates a password with at least 8 characters
        .{8,}   → any character repeated 8 or more times
    */
    const regExp_password = /^.{8,}$/;

    // Generic field validation function
    function validarCampo(field, regex, mandatoryMessage, formatMessage) {
        if (field) {
            let value = field.value;
            if (value == null || value.length === 0 || regExp_whitespace.test(value)) {
                errorMessages.push(mandatoryMessage);
                field.classList.add("error");
                if (errorMessages.length === 1) field.focus();
            } else if (regex && !(regex.test(value))) {
                errorMessages.push(formatMessage);
                field.classList.add("error");
                if (errorMessages.length === 1) field.focus();
            }
        }
    }

    // Validate each field
    validarCampo(nameField, null, "El campo Nombre es obligatorio.");
    validarCampo(email, regExp_email, "El campo Email es obligatorio.", "El email no tiene un formato válido.");
    validarCampo(phone, regExp_phone, "El campo Teléfono es obligatorio.", "El teléfono debe contener exactamente 9 dígitos.");
    validarCampo(password, regExp_password, "El campo Contraseña es obligatorio.", "La contraseña debe tener al menos 8 caracteres.");

    // Show errors if any
    if (errorMessages.length > 0) {
        errors.innerHTML = errorMessages.join("<br>");
        return false;
    }

    return true;
}

// Prevent form submission if validation fails
form.addEventListener("submit", function (event) {
    if (!validacion()) {
        event.preventDefault();
    }
});
