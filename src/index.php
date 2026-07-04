<?php

    require_once "templates/header.php";

?>

    <section class="d-flex align-items-center justify-content-center text-center cta-section">
        <div class="slideshow"> 
            <div class="slide"></div> 
            <div class="slide"></div> 
            <div class="slide"></div> 
            <div class="slide"></div> 
        </div>
        <div class="container">
            <img src="assets/brand/logo-grande.png" alt="Logo grande" class="img-fluid mb-4" style="max-width: 350px;">
            <div class="d-flex justify-content-around">
                <a href="contact.php" class="btn btn-lg px-5 py-3 fw-semibold btn-onix">Reservar</a>
                <a href="menu.php" class="btn btn-lg px-5 py-3 fw-semibold btn-onix">Ver carta</a>
            </div>
        </div>
    </section>
    
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 d-flex flex-column justify-content-start text-center">
                    <div class="mb-4">
                        <img src="assets/brand/logo-pequenyo.png" alt="Logo" width="auto" height="auto" style="max-height: 80px;">
                        <h1 class="fw-bold my-3">Cafetería en Elche</h1>
                        <p class="fs-5">
                            Cafetería Onix es un espacio donde el café, la creatividad y los buenos momentos se encuentran.
                            Cada rincón está pensado para inspirarte, relajarte y disfrutar de productos de calidad en un
                            ambiente acogedor.
                        </p>
                    </div>
                    <div class="my-3">
                        <img src="assets/images/cafeteria-interior.jpg" class="img-fluid rounded shadow" alt="Interior Onix">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div>
                        <img src="assets/images/cafeteria-exterior.jpg" class="img-fluid rounded shadow" alt="Exterior Onix">
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="py-5 my-5 reviews-section">
        <div class="container my-5">
            <h2 class="h2 fw-semibold mb-5 text-center">Lo que ven nuestros clientes</h2>
            <div class="row g-4 fs-5">
                <div class="col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <p class="card-text">“Café excelente y personal amable. El desayuno del domingo fue top.”</p>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-warning">★★★★★</span>
                                <small class="text-muted">Alicia • Google</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <p class="card-text">“Local tranquilo para trabajar. Repetiré.”</p>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-warning">★★★★☆</span>
                                <small class="text-muted">Jose • Maps</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <p class="card-text">“Los postres caseros están de vicio. Recomendado.”</p>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-warning">★★★★★</span>
                                <small class="text-muted">Juan • Trip</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="py-5 my-5 schedule-section">
        <div class="container">
            <div class="row g-4 fs-5">
                <div class="col-lg-5">
                    <h2 class="h2 fw-semibold mb-3 text-center">Horario</h2>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fs-mobile">Lunes - Sábado</span>
                            <span class="fw-semibold fs-mobile">7:00–15:00, 16:00–23:00</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fs-mobile">Domingo</span>
                            <span class="fw-semibold fs-mobile">Cerrado</span>
                        </li>
                    </ul>
                    <div class="d-flex justify-content-center">
                        <a href="contact.php" class="btn mt-3 w-100 py-2 fs-5 fw-semibold btn-onix">Reservar mesa</a>
                    </div>
                </div>
                <div class="col-lg-7">
                    <h2 class="h3 fw-semibold mb-3">Encuéntranos</h2>
                    <div class="ratio ratio-16x9 rounded overflow-hidden">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d6265.294229335919!2d-0.7074452235700485!3d38.26448968379929!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd63b701541d1b2f%3A0xa9734591f8df6b3f!2sCafeteria%20Onix!5e0!3m2!1ses!2ses!4v1764614579572!5m2!1ses!2ses" 
                        width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
<?php require_once "templates/footer.php"; ?>