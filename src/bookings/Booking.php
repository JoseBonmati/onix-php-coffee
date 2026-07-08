<?php

    require_once __DIR__ . '/../utils/Database.php';

    // Booking Entity Class
    class Booking {

        // Properties matching the translated database aliases with strict typing
        private ?int $id = null;
        private ?int $user_id = null;
        private ?string $date = null;
        private ?string $time = null;
        private ?int $people = null;
        private ?string $status = null;
        private ?string $user_name = null;

        //Getters with strict typing
        public function getId(): ?int {
            return $this->id;
        }

        public function getUserId(): ?int {
            return $this->user_id;
        }

        public function getDate(): ?string {
            return $this->date;
        }

        public function getTime(): ?string {
            return $this->time;
        }

        public function getPeople(): ?int {
            return $this->people;
        }

        public function getStatus(): ?string {
            return $this->status;
        }

        public function getUserName(): ?string {
            return $this->user_name;
        }


        //Fetches bookings with pagination, sorting, and automatic mapping to the Booking class.
        public static function getBookings(int $page, int $perPage, string $sortBy, string $sortDir, ?int $onlyUser = null): array {
            
            $db = Database::getConnection();
            $offset = ($page - 1) * $perPage;

            // White-list mapping parameter keys to avoid SQL Injection via ORDER BY
            $allowedColumns = [
                "id" => "r.id",
                "date" => "r.fecha",
                "time" => "r.hora",
                "people" => "r.num_personas",
                "status" => "r.estado",
                "user_name" => "u.nombre"
            ];
            
            // Fallback to safe defaults if parameters are invalid
            $orderField = $allowedColumns[$sortBy] ?? "r.id";
            $sortDir = strtoupper($sortDir) === "DESC" ? "DESC" : "ASC";

            $userFilter = "";
            if ($onlyUser !== null) {
                $userFilter = "WHERE r.id_usuario = :user_id";
            }

            // Clean SQL query with aliases to hydrate the Booking class seamlessly
            $sql = "SELECT r.id, r.id_usuario AS user_id, r.fecha AS date, r.hora AS time, r.num_personas AS people, r.estado AS status, u.nombre AS user_name 
                    FROM reservas r INNER JOIN usuarios u ON r.id_usuario = u.id $userFilter ORDER BY $orderField $sortDir LIMIT :offset, :limit";

            $stmt = $db->prepare($sql);

            if ($onlyUser !== null) {
                $stmt->bindValue(":user_id", $onlyUser, PDO::PARAM_INT);
            }

            // Bind variables to ensure correct integer hydration for limits
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":limit", $perPage, PDO::PARAM_INT);
            $stmt->execute();

            // Direct OOP Mapping
            $stmt->setFetchMode(PDO::FETCH_CLASS, "Booking");
            
            return $stmt->fetchAll();
        }
    }

?>