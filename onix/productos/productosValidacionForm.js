const form = document.querySelector("form"); // Works only if there is a single form
const nameField = document.getElementById("nombre");
const description = document.getElementById("descripcion");
const price = document.getElementById("precio");
const category = document.getElementById("categoriaId");
const image = document.getElementById("imagen");
const errors = document.getElementById("errores");

function validacion() {

    // Store error messages
    let errorMessages = [];

    /*
        General explanation of regular expressions:
        ^       → start of the string
        $       → end of the string
    */
    const regExp_whitespace = /^\s+$/;

    /* 
        Validates a positive integer or decimal number:
        ^          → start of the string
        [0-9]*     → zero or more digits (integer part, optional)
        \.?        → optional dot (decimal separator)
        [0-9]+     → one or more digits (mandatory if decimal point is present)
        $          → end of the string
    */
    const regExp_price = /^[0-9]*\.?[0-9]+$/;

    // Generic field validation function
    function validateField(field, regex, mandatoryMessage, formatMessage) {
        if (field) {
            let value = field.value;
            if (value == null || value.length === 0 || regExp_whitespace.test(value)) {
                errorMessages.push(mandatoryMessage);
            } else if (regex && !(regex.test(value))) {
                errorMessages.push(formatMessage);
            }
        }
    }

    // Validate each field
    validateField(nameField, null, "El campo Nombre es obligatorio.");
    validateField(description, null, "El campo Descripción es obligatorio.");
    validateField(price, regExp_price, "El campo Precio es obligatorio.", "El precio debe ser un número válido.");

    // Validate category (select)
    if (category && category.value === "") {
        errorMessages.push("Debe seleccionar una categoría.");
    }

    // Validate image (file)
    if (image && image.value === "") {
        errorMessages.push("Debe subir una imagen.");
    }

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
