<?php 

    define("HOST", "localhost"); 
    define("DB", "onix_db"); 
    define("USER", "root");       
    define("PASS", "");
    define("CHARSET", "utf8mb4");

    // Establish database connection using PDO
    function conectar() { 
        $dsn = "mysql:host=". HOST . ";dbname=" . DB . ";charset=" . CHARSET; 
        
        try { 
            $con = new PDO($dsn, USER, PASS); 
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);  
            return $con; 
        } catch (PDOException $e) { 
            die("Error en la conexión: " . $e->getMessage()); 
        } 
    }

?>