<?php

    require_once __DIR__ . '/../utils/Database.php';

    // Category Entity Class
    class Category {

        // Properties matching the translated database aliases with strict typing
        private ?int $id = null;
        private ?string $name = null;
        private ?string $status = null;

        //Getters with strict typing
        public function getId(): ?int {
            return $this->id;
        }

        public function getName(): ?string {
            return $this->name;
        }

        public function getStatus(): ?string {
            return $this->status;
        }


        //Fetches categories with pagination, sorting, and automatic mapping to the Category class.
        public static function getCategories(int $page, int $perPage, string $orderBy, string $sortDirection): array {
            
            $db = Database::getConnection();
            $offset = ($page - 1) * $perPage;

            // White-list mapping parameter keys to avoid SQL Injection and map EN to ES for sorting
            $allowedColumns = [
                "id" => "id",
                "name" => "nombre",
                "status" => "estado"
            ];

            // Fallback to safe defaults if parameters are invalid
            $orderField = $allowedColumns[$orderBy] ?? "id";
            $sortDirection = strtoupper($sortDirection) === "DESC" ? "DESC" : "ASC";

            // Clean SQL query with aliases to hydrate the Category class seamlessly
            $sql = "SELECT id, nombre AS name, estado AS status FROM categorias ORDER BY $orderField $sortDirection LIMIT :offset, :limit";

            $stmt = $db->prepare($sql);
            
            // Bind variables to ensure correct integer hydration
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":limit", $perPage, PDO::PARAM_INT);
            $stmt->execute();

            // Direct OOP Mapping
            $stmt->setFetchMode(PDO::FETCH_CLASS, "Category");

            return $stmt->fetchAll();
        }
    }

?>