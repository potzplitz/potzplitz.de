<?php

if(!class_exists("Database_legacy")) {
    class Database_legacy {
        public $pdo;
        private $rows;

        public function __construct($dbname) {

            $host = "localhost";
            $user = MARIAUSER;
            $psswd = MARIAPASSWD;

            try {

                $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
                $this->pdo = new PDO($dsn, $user, (!isset($psswd)) ? "" : $psswd);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            } catch (PDOException $e) {

                echo "Verbindung fehlgeschlagen.";
            }
        }
        
        public function rows(): int { // returns count of rows of select in current instance
           return $this->rows;
        }

        public function queryData($query) { // deprecated
            $stmt = $this->pdo->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function queryData2($query, $binds) { // prepared
            $stmt = $this->pdo->prepare($query);

            foreach ($binds as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            $stmt->execute();
            $this->rows = $stmt->rowCount();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }    
    }  
}

?>