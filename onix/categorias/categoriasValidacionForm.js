const form = document.querySelector("form"); // Works only if there is a single form
const nameField = document.getElementById("nombre");
const errors = document.getElementById("errores");

function validacion() {
    // Remove error class before validation
    if (nameField) nameField.classList.remove("error");

    // Store error messages
    let errorMessages = [];

    /*
        General explanation of regular expressions:
        ^       → start of the string
        $       → end of the string
    */
    const regExp_whitespace = /^\s+$/;

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
