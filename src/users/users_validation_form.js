document.addEventListener("DOMContentLoaded", () => {
    
    const form = document.querySelector("form"); // Works only if there is a single form
    const nameField = document.getElementById("name");
    const emailField = document.getElementById("email");
    const phoneField = document.getElementById("phone");
    const passwordField = document.getElementById("password");
    const errorsContainer = document.getElementById("errors");

    // Exit if there is no form on the current page to prevent console errors
    if (!form) return;

    function validate() {

        // Store error messages
        let errorMessages = [];

        /*
            General explanation of regular expressions:
            ^       → start of the string
            $       → end of the string
        */
        const whitespaceRegex = /^\s+$/;

        /* 
            Validates an email with basic format user@server.com
            .+   → One or more characters of any type (except line break) (user)
            \@   → The at symbol (@)
            .+   → Again, one or more characters of any type (server)
            \.   → Mandatory dot before extension
            .+   → Again, one or more characters of any type (com)
        */
        const emailRegex = /^(.+\@.+\..+)$/;

        /* 
            Validates a phone number with exactly 9 digits
            \d{9}   → exactly 9 digits (0–9)
        */
        const phoneRegex = /^\d{9}$/;

        /*
            Validates a password with at least 8 characters
            .{8,}   → any character repeated 8 or more times
        */
        const passwordRegex = /^.{8,}$/;

        // Generic field validation function
        function validateField(field, regex, mandatoryMessage, formatMessage) {
            if (field) {
                // Use trim() to clean up accidental leading/trailing spaces
                let value = field.value.trim(); 
                
                if (!value || value.length === 0 || whitespaceRegex.test(value)) {
                    errorMessages.push(mandatoryMessage);
                } else if (regex && !(regex.test(value))) {
                    errorMessages.push(formatMessage);
                }
            }
        }

        // Validate each field
        validateField(nameField, null, "El campo Nombre es obligatorio.");
        validateField(emailField, emailRegex, "El campo Email es obligatorio.", "El email no tiene un formato válido.");
        validateField(phoneField, phoneRegex, "El campo Teléfono es obligatorio.", "El teléfono debe contener exactamente 9 dígitos.");
        validateField(passwordField, passwordRegex, "El campo Contraseña es obligatorio.", "La contraseña debe tener al menos 8 caracteres.");

        // Show errors if any
        if (errorMessages.length > 0) {
            if (errorsContainer) {
                errorsContainer.innerHTML = errorMessages.join("<br>");
            }
            return false;
        }

        // Clear errors if validation passes
        if (errorsContainer) {
            errorsContainer.innerHTML = "";
        }
        
        return true;
    }

    // Prevent form submission if validation fails
    form.addEventListener("submit", function (event) {
        if (!validate()) {
            event.preventDefault();
        }
    });
});