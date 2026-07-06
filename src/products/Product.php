<?php

    require_once __DIR__ . '/../utils/Database.php';

    // Product Entity Class
    class Product {

        // Properties matching the translated database aliases with strict typing
        private ?int $id = null;
        private ?int $category_id = null;
        private ?string $name = null;
        private ?string $description = null;
        private ?float $price = null;
        private ?string $image = null;
        private ?string $status = null;
        private ?string $category_name = null;

        // Getters with strict typing
        public function getId(): ?int {
            return $this->id;
        }

        public function getCategoryId(): ?int {
            return $this->category_id;
        }

        public function getName(): ?string {
            return $this->name;
        }

        public function getDescription(): ?string {
            return $this->description;
        }

        public function getPrice(): ?float {
            return $this->price;
        }

        public function getImage(): ?string {
            return $this->image;
        }

        public function getStatus(): ?string {
            return $this->status;
        }

        public function getCategoryName(): ?string {
            return $this->category_name;
        }


        //Fetches products with pagination, sorting, and automatic mapping to the Product class.
        public static function getProducts(int $page, int $perPage, string $sortBy, string $sortDir): array {
            
            $db = Database::getConnection();
            $offset = ($page - 1) * $perPage;

            // White-list mapping parameter keys to avoid SQL Injection via ORDER BY
            $allowedColumns = [
                "id" => "p.id",
                "name" => "p.nombre",
                "description" => "p.descripcion",
                "price" => "p.precio",
                "category_name" => "c.nombre"
            ];
            
            // Fallback to safe defaults if parameters are invalid
            $orderField = $allowedColumns[$sortBy] ?? "p.id";
            $sortDir = strtoupper($sortDir) === "DESC" ? "DESC" : "ASC";

            // Clean SQL query with aliases to hydrate the Product class seamlessly
            $sql = "SELECT p.id, p.id_categoria AS category_id, p.nombre AS name, p.descripcion AS description, p.precio AS price, p.imagen AS image, p.estado AS status, 
                    c.nombre AS category_name FROM productos p INNER JOIN categorias c ON p.id_categoria = c.id ORDER BY $orderField $sortDir LIMIT :offset, :limit";

            $stmt = $db->prepare($sql);

            // Bind variables to ensure correct integer hydration for limits
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":limit", $perPage, PDO::PARAM_INT);
            $stmt->execute();

            // Direct OOP Mapping
            $stmt->setFetchMode(PDO::FETCH_CLASS, "Product");
            
            return $stmt->fetchAll();
        }
    }

?>