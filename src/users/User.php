<?php

    require_once __DIR__ . '/../utils/Database.php';

    // User Entity Class
    class User {
        
        // Properties matching the translated database aliases with strict typing
        private ?int $id = null;
        private ?string $name = null;
        private ?string $email = null;
        private ?string $password = null;
        private ?string $phone = null;
        private ?string $role = null;
        private ?string $status = null;

        //Getters with strict typing
        public function getId(): ?int {
            return $this->id;
        }

        public function getName(): ?string {
            return $this->name;
        }

        public function getEmail(): ?string {
            return $this->email;
        }

        public function getPassword(): ?string {
            return $this->password;
        }

        public function getPhone(): ?string {
            return $this->phone;
        }

        public function getRole(): ?string {
            return $this->role;
        }

        public function getStatus(): ?string {
            return $this->status;
        }


        //Fetches users with pagination, sorting, and automatic mapping to the User class.
        public static function getUsers(int $page, int $perPage, string $orderBy, string $sortDirection): array {
            
            $db = Database::getConnection();
            $offset = ($page - 1) * $perPage;

            // White-list mapping parameter keys to avoid SQL Injection and map EN to ES for sorting
            $allowedColumns = [
                "id" => "id",
                "name" => "nombre",
                "email" => "email",
                "phone" => "telefono",
                "role" => "rol",
                "status" => "estado"
            ];

            // Fallback to safe defaults if parameters are invalid
            $orderField = $allowedColumns[$orderBy] ?? "id";
            $sortDirection = strtoupper($sortDirection) === "DESC" ? "DESC" : "ASC";

            // Clean SQL query with aliases to hydrate the User class seamlessly
            $sql = "SELECT id, nombre AS name, email, contrasenya AS password, telefono AS phone, rol AS role, estado AS status 
                    FROM usuarios ORDER BY $orderField $sortDirection LIMIT :offset, :limit";

            $stmt = $db->prepare($sql);
            
            // Bind variables to ensure correct integer hydration
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":limit", $perPage, PDO::PARAM_INT);
            $stmt->execute();

            // Direct OOP Mapping
            $stmt->setFetchMode(PDO::FETCH_CLASS, "User");

            return $stmt->fetchAll();
        }
    }

?>