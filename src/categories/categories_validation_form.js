document.addEventListener("DOMContentLoaded", () => {
    
    const form = document.querySelector("form"); // Works only if there is a single form
    const nameField = document.getElementById("name");
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

        // Generic field validation function
        function validateField(field, regex, mandatoryMessage, formatMessage) {
            if (field) {
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