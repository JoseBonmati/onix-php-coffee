<?php

    require_once "templates/header.php";
    require_once "utils/Database.php";

    $db = Database::getConnection();

    // Get active categories
    $categoryQuery = $db->prepare("SELECT id, nombre AS name FROM categorias WHERE estado = 'activo' ORDER BY nombre ASC");
    $categoryQuery->execute();
    $categories = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);

    $categoryIndex = 0;
?>

    <section class="container py-5">
        <h1 class="text-center mb-5 display-5 fw-bold text-light">Carta Cafetería Onix</h1>

        <?php foreach ($categories as $category): ?>
            <?php
                $categoryIndex++;
                $reverse = $categoryIndex % 2 === 0;

                // Get products
                $productStmt = $db->prepare("SELECT nombre AS name, descripcion AS description, precio AS price, imagen AS image FROM productos 
                                             WHERE id_categoria = :id AND estado = 'activo' ORDER BY nombre ASC");
                $productStmt->execute([":id" => $category["id"]]);
                $products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($products) === 0) continue;
            ?>

            <section class="container py-5">
                <h2 class="fw-bold mb-5 text-center"><?= htmlspecialchars($category["name"]) ?></h2>
                <div class="row align-items-start gx-5">
                    <div class="col-lg-6 text-center text-lg-start <?= $reverse ? 'order-lg-2' : 'order-lg-1' ?>">
                        <ul class="list-group list-group-flush fs-4 onix-list">
                            <?php foreach ($products as $product): ?>
                                <li class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= htmlspecialchars($product["name"]) ?></strong>
                                        <span><?= number_format($product["price"], 2) ?>€</span>
                                    </div>
                                    <p class="onix-desc text-start"><?= htmlspecialchars($product["description"]) ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="col-lg-6 <?= $reverse ? 'order-lg-1' : 'order-lg-2' ?>">
                        <div id="carousel<?= $category["id"] ?>" class="carousel slide">
                            <div class="carousel-inner rounded shadow">
                                <?php foreach ($products as $index => $product): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <img src="<?= htmlspecialchars($product["image"]) ?>" class="d-block menu-carousel-img" alt="<?= htmlspecialchars($product["name"]) ?>">
                                        <div class="carousel-caption d-block bg-dark bg-opacity-75 rounded py-2">
                                            <h5 class="mb-0"><?= htmlspecialchars($product["name"]) ?></h5>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <button class="btn carousel-btn" type="button" data-bs-target="#carousel<?= $category["id"] ?>" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </button>

                                <div class="carousel-indicators position-static m-0">
                                    <?php foreach ($products as $index => $product): ?>
                                        <button type="button"
                                                data-bs-target="#carousel<?= $category["id"] ?>"
                                                data-bs-slide-to="<?= $index ?>"
                                                class="<?= $index === 0 ? 'active' : '' ?>">
                                        </button>
                                    <?php endforeach; ?>
                                </div>

                                <button class="btn carousel-btn" type="button" data-bs-target="#carousel<?= $category["id"] ?>" data-bs-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    </section>
    
    <section class="container py-5">
        <h2 class="text-center fw-bold mb-5 display-6">Café e infusiones</h2>
        <div class="row row-cols-1 row-cols-lg-2 gx-5 gy-4 fs-5">
            <div class="col">
                <ul class="list-group list-group-flush onix-list">
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Café Solo</span><span>1,40€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Café Americano</span><span>1,80€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Bombón</span><span>2,00€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Carajillo</span><span>2,10€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Té Negro</span><span>2,30€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Té Verde</span><span>2,30€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Rooibos de Vainilla</span><span>2,30€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Poleo</span><span>2,30€</span>
                    </li>
                </ul>
            </div>
            <div class="col">
                <ul class="list-group list-group-flush onix-list">
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Café Cortado</span><span>1,60€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Café con Leche</span><span>1,90€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Capuccino</span><span>2,40€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Té Chai</span><span>2,30€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Té Rojo</span><span>2,30€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Té Matcha</span><span>2,30€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Pacífico</span><span>2,30€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Manzanilla</span><span>2,30€</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>
    
    <section class="container py-5">
        <h2 class="text-center fw-bold mb-5 display-6">Bebidas</h2>
        <div class="row row-cols-1 row-cols-lg-2 gx-5 gy-4 fs-5">
            <div class="col">
                <ul class="list-group list-group-flush onix-list">
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Agua mineral</span><span>1,20€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Coca-Cola</span><span>2,00€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Coca-Cola Zero</span><span>2,00€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Fanta Naranja</span><span>2,00€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Fanta Limón</span><span>2,00€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Sprite</span><span>2,00€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Nestea</span><span>2,20€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Zumo de naranja</span><span>2,50€</span>
                    </li>
                </ul>
            </div>
            <div class="col">
                <ul class="list-group list-group-flush onix-list">
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Zumo de piña</span><span>2,50€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Zumo multifrutas</span><span>2,50€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Cerveza</span><span>2,20€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Cerveza sin alcohol</span><span>2,20€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Tónica</span><span>2,00€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Agua con gas</span><span>1,50€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Batido de chocolate</span><span>2,80€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 border-0">
                        <span>Batido de vainilla</span><span>2,80€</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

<?php require_once "templates/footer.php"; ?>