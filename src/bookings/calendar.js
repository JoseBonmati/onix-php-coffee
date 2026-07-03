// Month names used for the calendar header
const monthNames = [
    "Enero","Febrero","Marzo","Abril","Mayo","Junio",
    "Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"
];

// Track today's date and the currently displayed month/year
let today = new Date();
let currentYear = today.getFullYear();
let currentMonth = today.getMonth();

// DOM references
const calMonthYear = document.getElementById("cal-month-year");
const calGrid = document.getElementById("cal-grid");
const btnPrev = document.getElementById("cal-prev");
const btnNext = document.getElementById("cal-next");
const inputDate = document.getElementById("fecha");
const selectHour = document.getElementById("hora");

// Initial render
renderizarCalendario();

// Navigation buttons
btnPrev.addEventListener("click", () => cambiarMes(-1));
btnNext.addEventListener("click", () => cambiarMes(1));

// Move to previous or next month and handle year change when crossing boundaries
function cambiarMes(direction) {
    currentMonth += direction;

    if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    }
    if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    }

    renderizarCalendario();
}

// Update calendar header (Month + Year) and build the calendar grid
function renderizarCalendario() {
    calMonthYear.textContent = `${monthNames[currentMonth]} ${currentYear}`;
    crearTablaCalendario();
}

function crearTablaCalendario() {
    // Clear previous calendar
    calGrid.innerHTML = "";

    // Create table structure
    const table = document.createElement("table");
    table.classList.add("table", "table-dark", "table-bordered", "onix-calendar-table");

    // Create header row with weekday names
    const headerRow = document.createElement("tr");
    const days = ["Lu","Ma","Mi","Ju","Vi","Sa","Do"];

    days.forEach(d => {
        const th = document.createElement("th");
        th.textContent = d;
        th.classList.add("text-center");
        headerRow.appendChild(th);
    });

    table.appendChild(headerRow);

    // Determine the first weekday of the month
    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const adjustedFirstDay = (firstDay === 0) ? 6 : firstDay - 1;

    // Number of days in the current month
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

    let row = document.createElement("tr");

    // Add empty cells before day 1
    for (let i = 0; i < adjustedFirstDay; i++) {
        row.appendChild(document.createElement("td"));
    }

    // Generate each day cell
    for (let day = 1; day <= daysInMonth; day++) {
        const cell = document.createElement("td");
        cell.textContent = day;
        cell.classList.add("text-center", "cal-day");

        const cellDate = new Date(currentYear, currentMonth, day);
        const yyyy = cellDate.getFullYear();
        const mm = String(cellDate.getMonth() + 1).padStart(2, "0");
        const dd = String(cellDate.getDate()).padStart(2, "0");
        const dateStr = `${yyyy}-${mm}-${dd}`;

        // Disable past days
        const todayMid = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        if (cellDate < todayMid) {
            cell.classList.add("disabled-day");
        }

        // Disable Sundays (closed day)
        if (cellDate.getDay() === 0) {
            cell.classList.add("disabled-day");
        }

        // Handle day selection
        cell.addEventListener("click", () => seleccionarDia(cell, dateStr));

        row.appendChild(cell);

        // Start a new row every 7 days
        if ((adjustedFirstDay + day) % 7 === 0) {
            table.appendChild(row);
            row = document.createElement("tr");
        }
    }

    // Append last row
    table.appendChild(row);
    calGrid.appendChild(table);
}

// Ignoring disabled days, highlights the selected day removing previous selection
// And sets the selected date in the input load available hours for this date
function seleccionarDia(cell, dateStr) {
    if (cell.classList.contains("disabled-day")) return;

    document.querySelectorAll(".selected-day").forEach(c => c.classList.remove("selected-day"));
    cell.classList.add("selected-day");

    inputDate.value = dateStr;

    cargarHorasDisponibles();
}

// Fetch available hours for the selected date
function cargarHorasDisponibles() {
    selectHour.innerHTML = "";

    const placeholder = document.createElement("option");
    placeholder.disabled = true;
    placeholder.selected = true;
    placeholder.textContent = "Seleccione hora";
    selectHour.appendChild(placeholder);

    const hours = [
        "07:00","08:00","09:00","10:00","11:00","12:00","13:00","14:00",
        "16:00","17:00","18:00","19:00","20:00","21:00","22:00"
    ];

    hours.forEach(h => {
        const opt = document.createElement("option");
        opt.value = h;
        opt.textContent = h;
        selectHour.appendChild(opt);
    });
}

