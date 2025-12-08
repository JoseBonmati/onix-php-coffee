<?php

    // Category entity class
    class Category {

        private $id;
        private $name;
    
        public function getId() {
            return $this->id;
        }

        public function getNombre() {
            return $this->name;
        }
        
    }

    // Retrieve categories with pagination and sorting
    function obtenerCategorias($con, $page, $resultsPP, $order, $orderType) {

        $start = ($page - 1) * $resultsPP;

        // Allowed columns for ordering
        $allowedColumns = ["id","nombre"];
        if (!in_array($order, $allowedColumns)) $order = "id";
        $orderType = strtoupper($orderType) === "DESC" ? "DESC" : "ASC";

        $query = $con->prepare("SELECT id, nombre AS name FROM categorias ORDER BY $order $orderType LIMIT :inicio, :resultados");
        $query->bindParam(":inicio", $start, PDO::PARAM_INT);
        $query->bindParam(":resultados", $resultsPP, PDO::PARAM_INT);
        $query->execute();

        // Map results to Category class
        $query->setFetchMode(PDO::FETCH_CLASS, "Category");
        return $query->fetchAll();
    }

?>
