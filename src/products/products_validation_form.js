document.addEventListener("DOMContentLoaded", () => {
    
    const form = document.querySelector("form"); // Works only if there is a single form
    const nameField = document.getElementById("name");
    const descriptionField = document.getElementById("description");
    const priceField = document.getElementById("price");
    const categoryField = document.getElementById("category_id");
    const imageField = document.getElementById("image");
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
            Validates a positive integer or decimal number:
            ^          → start of the string
            [0-9]*     → zero or more digits (integer part, optional)
            \.?        → optional dot (decimal separator)
            [0-9]+     → one or more digits (mandatory if decimal point is present)
            $          → end of the string
        */
        const priceRegex = /^[0-9]*\.?[0-9]+$/;

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
        validateField(descriptionField, null, "El campo Descripción es obligatorio.");
        validateField(priceField, priceRegex, "El campo Precio es obligatorio.", "El precio debe ser un número válido.");

        // Validate category (select)
        if (categoryField && categoryField.value === "") {
            errorMessages.push("Debe seleccionar una categoría.");
        }

        // Validate image (file)
        if (imageField && imageField.value === "") {
            errorMessages.push("Debe subir una imagen.");
        }

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