<?php

    // Reserve entity class
    class Reserve {

        private $id;
        private $user_id;
        private $date;
        private $time;
        private $people;
        private $status;
        private $userName;

        public function getId() {
            return $this->id;
        }

        public function getIdUsuario() {
            return $this->user_id;
        }

        public function getFecha() {
            return $this->date;
        }

        public function getHora() {
            return $this->time;
        }

        public function getPersonas() {
            return $this->people;
        }

        public function getEstado() {
            return $this->status;
        }

        public function getUsuarioNombre() {
            return $this->userName;
        }
    }

    // Retrieve reservations with pagination and sorting
    function obtenerReservas($con, $page, $resultsPP, $order, $orderType, $onlyUser = null) {

        $start = ($page - 1) * $resultsPP;

        // Allowed columns for ordering
        $allowedColumns = ["r.id", "r.fecha", "r.hora", "r.num_personas", "r.estado", "u.nombre"];
        if (!in_array($order, $allowedColumns)) $order = "r.id";
        $orderType = strtoupper($orderType) === "DESC" ? "DESC" : "ASC";

        $userFilter = "";
        if ($onlyUser !== null) {
            $userFilter = "WHERE r.id_usuario = :idUsuario";
        }

        $sql = $con->prepare("SELECT r.id, r.id_usuario AS user_id, r.fecha AS date, r.hora AS time, r.num_personas AS people, r.estado AS status, u.nombre AS userName
                              FROM reservas r JOIN usuarios u ON r.id_usuario = u.id $userFilter ORDER BY $order $orderType LIMIT :inicio, :resultados");

        if ($onlyUser !== null) {
            $sql->bindValue(":idUsuario", $onlyUser, PDO::PARAM_INT);
        }

        $sql->bindValue(":inicio", $start, PDO::PARAM_INT);
        $sql->bindValue(":resultados", $resultsPP, PDO::PARAM_INT);
        $sql->execute();

        $sql->setFetchMode(PDO::FETCH_CLASS, "Reserve");
        return $sql->fetchAll();
    }

?>
