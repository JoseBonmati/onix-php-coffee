document.addEventListener("DOMContentLoaded", () => {
    
    const form = document.querySelector("form"); // Works only if there is a single form
    const dateField = document.getElementById("date");
    const timeField = document.getElementById("time");
    const peopleField = document.getElementById("people");
    const statusField = document.getElementById("status");
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

        /* Validates a date in the format YYYY-MM-DD
            \d{4}   → exactly 4 digits for the year (YYYY)
            -       → mandatory hyphen separator
            \d{2}   → exactly 2 digits for the month (MM)
            -       → mandatory hyphen separator
            \d{2}   → exactly 2 digits for the day (DD)
        */
        const dateRegex = /^\d{4}-\d{2}-\d{2}$/;

        // Allowed hours
        const allowedHours = [
            "07:00","08:00","09:00","10:00","11:00","12:00","13:00","14:00",
            "16:00","17:00","18:00","19:00","20:00","21:00","22:00"
        ];

        // Validate date
        const dateValue = dateField ? dateField.value.trim() : "";
        const today = new Date();

        if (!dateField || dateValue === "" || whitespaceRegex.test(dateValue)) {
            errorMessages.push("La fecha es obligatoria.");
        } else {
            if (!dateRegex.test(dateValue)) {
                errorMessages.push("La fecha no tiene un formato válido.");
            } else {
                today.setHours(0,0,0,0);
                const selectedDate = new Date(dateValue);

                if (selectedDate < today) {
                    errorMessages.push("No puedes seleccionar una fecha pasada.");
                }
            }
        }

        // Validate hour
        const timeValue = timeField ? timeField.value.trim() : "";

        if (!timeField || timeValue === "" || whitespaceRegex.test(timeValue)) {
            errorMessages.push("La hora es obligatoria.");
        } else if (!allowedHours.includes(timeValue)) {
            errorMessages.push("La hora seleccionada no es válida.");
        }
        
        if (dateRegex.test(dateValue)) {
            const selectedDate = new Date(dateValue);
            const todayDate = new Date();
            todayDate.setHours(0,0,0,0);

            if (selectedDate.getTime() === todayDate.getTime()) {
                const nowHour = new Date().toTimeString().slice(0,5);
                if (timeValue <= nowHour) {
                    errorMessages.push("La hora seleccionada ya ha pasado.");
                }
            }
        }

        // Validate number of people
        const peopleValue = peopleField ? peopleField.value.trim() : "";
        const num = parseInt(peopleValue);

        if (!peopleField || peopleValue === "") {
            errorMessages.push("Debes seleccionar el número de personas.");
        } else {
            if (isNaN(num) || num < 1) {
                errorMessages.push("El número de personas no es válido.");
            }
            if (num > 30) {
                errorMessages.push("El máximo permitido es 30 personas.");
            }
        }

        // Validate status
        const statusValue = statusField ? statusField.value.trim() : "";
        const validStates = ["pendiente", "confirmada", "cancelada"];

        if (statusField) {
            if (!validStates.includes(statusValue)) {
                errorMessages.push("El estado seleccionado no es válido.");
            }
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