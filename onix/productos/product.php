<?php

    // Product entity class
    class Product {

        private $id;
        private $name;
        private $description;
        private $price;
        private $categoryName;
        private $image;

        public function getId() {
            return $this->id;
        }

        public function getNombre() {
            return $this->name;
        }

        public function getDescripcion() {
            return $this->description;
        }

        public function getPrecio() {
            return $this->price;
        }

        public function getCategoriaNombre() {
            return $this->categoryName;
        }

        public function getImagen() {
            return $this->image;
        }
    }

    // Retrieve products with pagination and sorting
    function obtenerProductos($con, $page, $resultsPP, $order, $orderType) {

        $start = ($page - 1) * $resultsPP;

        $allowedColumns = ["p.id","p.nombre","p.descripcion","p.precio","c.nombre"];
        if (!in_array($order, $allowedColumns)) $order = "p.id";
        $orderType = strtoupper($orderType) === "DESC" ? "DESC" : "ASC";

        $query = $con->prepare("SELECT p.id, p.nombre AS name, p.descripcion AS description, p.precio AS price, c.nombre AS categoryName, p.imagen AS image 
                                FROM productos p INNER JOIN categorias c ON p.id_categoria = c.id ORDER BY $order $orderType LIMIT :inicio, :resultados");
        $query->bindParam(":inicio", $start, PDO::PARAM_INT);
        $query->bindParam(":resultados", $resultsPP, PDO::PARAM_INT);
        $query->execute();

        // Map results to Product class
        $query->setFetchMode(PDO::FETCH_CLASS, "Product");
        return $query->fetchAll();
    }

?>
