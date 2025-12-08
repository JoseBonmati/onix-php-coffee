<?php

    // User entity class
    class User {

        private $id;
        private $name;
        private $email;
        private $password;
        private $phone;
        private $role;
        private $status;
    
        public function getId() {
            return $this->id;
        }

        public function getNombre() {
            return $this->name;
        }

        public function getEmail() {
            return $this->email;
        }

        public function getContrasenya() {
            return $this->password;
        }

        public function getTelefono() {
            return $this->phone;
        }

        public function getRol() {
            return $this->role;
        }

        public function getEstado() {
            return $this->status;
        }
        
    }

    // Retrieve users with pagination and sorting
    function obtenerUsuarios($con, $page, $resultsPP, $order, $orderType) {

        $start = ($page - 1) * $resultsPP;

        $allowedColumns = ["id","nombre","email","contrasenya","telefono","rol","estado"];
        if (!in_array($order, $allowedColumns)) $order = "id";
        $orderType = strtoupper($orderType) === "DESC" ? "DESC" : "ASC";

        $query = $con->prepare("SELECT id, nombre AS name, email, contrasenya AS password, telefono AS phone, rol AS role, estado AS status FROM usuarios 
                                ORDER BY $order $orderType LIMIT :inicio, :resultados");
        $query->bindParam(":inicio", $start, PDO::PARAM_INT);
        $query->bindParam(":resultados", $resultsPP, PDO::PARAM_INT);
        $query->execute();

        // Map results to User class
        $query->setFetchMode(PDO::FETCH_CLASS, "User");
        return $query->fetchAll();
    }

?>