<?php 

class Database {
    
    // Holds the single PDO instance (Singleton pattern) to prevent multiple connections
    private static ?PDO $connection = null;

    // Retrieves the database connection prioritizing system env vars, then fallback to .env file
    public static function getConnection(): PDO {
        // If the connection already exists, return it immediately
        if (self::$connection !== null) {
            return self::$connection;
        }

        // Try to read from system environment variables (Injected by Docker)
        $host = getenv('DB_HOST');
        $dbName = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');

        // If system vars are empty, read the .env file manually 
        if (!$host || !$dbName || !$user) {
            $envPath = __DIR__ . '/../../.env';
            
            if (file_exists($envPath)) {
                $envVars = parse_ini_file($envPath);
                
                if ($envVars) {
                    $host = $envVars['DB_HOST'] ?? '';
                    $dbName = $envVars['DB_NAME'] ?? '';
                    $user = $envVars['DB_USER'] ?? '';
                    $pass = $envVars['DB_PASS'] ?? '';
                } else {
                    die("System Error: Failed to parse .env file.");
                }
            } else {
                die("System Error: Database configuration missing (No ENV vars and no .env file found).");
            }
        }

        $charset = "utf8mb4";
        $dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";

        // Establish connection
        try { 
            self::$connection = new PDO($dsn, $user, $pass); 
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);  
        } catch (PDOException $e) {
            // Log the error securely to the server logs, never expose details
            error_log("Database Connection Error: " . $e->getMessage());
            
            // Generic error for the user in Spanish
            die("Error del sistema: No se pudo conectar a la base de datos en este momento.");
        }

        return self::$connection;
    }
}

?>