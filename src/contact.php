<?php
    require_once "templates/header.php";
?>

    <section class="py-5">
        <div class="container">
            <h1 class="text-center text-light fw-bold mb-5 display-5">Contacto</h1>
            <div class="row justify-content-center text-light fs-4 my-5 mt-3">
                <div class="col-lg-6 text-center">
                    <div class="mb-3">
                        <i class="bi bi-geo-alt-fill display-4 text-onix"></i>
                    </div>
                    <h2 class="fw-bold text-onix my-4">Dirección</h2>
                    <p class="mb-0">Carrer Poeta Miguel Hernandez, 36, 03201 Elche, Alicante</p>
                </div>

                <div class="col-lg-6 text-center mt-5 mt-lg-0">
                    <div class="mb-3">
                        <i class="bi bi-telephone-fill display-4 text-onix"></i>
                    </div>
                    <h2 class="fw-bold text-onix my-4">Teléfono</h2>
                    <p class="mb-0">123 456 789</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 my-5 calendar-section">
        <div class="container">
            
            <!-- Messages -->
            <div class="mb-3">
                <?php
                    if (isset($_GET["error"])) {
                        $error = htmlspecialchars($_GET["error"]);

                        if ($error === "invalid_data") {
                            echo "<p class='alert alert-danger text-center mb-4'>Debes completar todos los campos correctamente.</p>";
                        }
                        if ($error === "invalid_date") {
                            echo "<p class='alert alert-danger text-center mb-4'>La fecha seleccionada no es válida.</p>";
                        }
                        if ($error === "past_date") {
                            echo "<p class='alert alert-danger text-center mb-4'>No puedes reservar en una fecha pasada.</p>";
                        }
                        if ($error === "sunday") {
                            echo "<p class='alert alert-danger text-center mb-4'>La cafetería permanece cerrada los domingos. Selecciona otro día.</p>";
                        }
                        if ($error === "invalid_time") {
                            echo "<p class='alert alert-danger text-center mb-4'>La hora seleccionada no es válida.</p>";
                        }
                        if ($error === "fully_booked") {
                            echo "<p class='alert alert-danger text-center mb-4'>No quedan plazas disponibles a esa hora. Prueba otra hora o día.</p>";
                        }
                        if ($error === "booking_not_found") {
                            echo "<p class='alert alert-danger text-center mb-4'>No se ha encontrado la reserva.</p>";
                        }
                    }
                ?>
            </div>

            <!-- Client-side errors -->
            <div id="errors" class="mb-5 text-center text-danger fw-semibold"></div>

            <div class="row justify-content-center">
                <div class="col-lg-5 mb-4">
                    <div class="onix-card p-4 text-light">
                        <h2 class="text-onix fw-bold mb-4 text-center">Selecciona una fecha</h2>

                        <div id="calendar-container" class="bg-dark p-3 rounded text-center">

                            <!-- Calendar header -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <button id="cal_prev" type="button" class="btn btn-sm btn-outline-onix px-3">
                                    <i class="bi bi-chevron-left"></i>
                                </button>

                                <h3 id="cal_month_year" class="mb-0 text-onix fw-bold"></h3>

                                <button id="cal_next" type="button" class="btn btn-sm btn-outline-onix px-3">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>

                            <div id="cal_grid" class="table-responsive"></div>

                        </div>
                    </div>
                </div>

                <div class="col-lg-5 mb-4">
                    <div class="onix-card p-4 text-light">
                        <h2 class="text-onix fw-bold mb-4 text-center">Reserva</h2>

                        <form action="/bookings/booking_process.php" method="post">
                            <div class="mb-3">
                                <label for="date" class="form-label">Fecha</label>
                                <input type="text" id="date" name="date" class="form-control" placeholder="Selecciona una fecha" autocomplete="off" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="people" class="form-label">Personas</label>
                                <select id="people" name="people" class="form-select">
                                    <option selected disabled value="">Seleccione</option>
                                    <?php for ($i = 1; $i <= 30; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?> persona<?= $i > 1 ? 's' : '' ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="time" class="form-label">Hora</label>
                                <select id="time" name="time" class="form-select">
                                    <option selected disabled value="">Seleccione una fecha primero</option>
                                </select>
                            </div>
                            
                            <div class="text-center mt-4">
                                <?php if (isset($_SESSION["id"])): ?>
                                    <button type="submit" class="btn btn-onix w-100">Reservar</button>
                                <?php else: ?>
                                    <a href="/users/login.php" class="btn btn-outline-onix w-100">Iniciar sesión para reservar</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container py-5 mb-5">
        <div class="ratio ratio-16x9">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d6265.294229335919!2d-0.7074452235700485!3d38.26448968379929!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd63b701541d1b2f%3A0xa9734591f8df6b3f!2sCafeteria%20Onix!5e0!3m2!1ses!2ses!4v1764693711563!5m2!1ses!2ses"
                    width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </section>

    <script src="/bookings/calendar.js"></script>
    <script src="/bookings/bookings_validation_form.js"></script>

<?php require_once "templates/footer.php"; ?>