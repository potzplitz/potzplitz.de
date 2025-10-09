<?php
class Database {
    private $conn;
    public $RSArray = [];
    public $rows = 0;
    function __construct() {

        $connection_string = "91.99.142.119:1521/FREEPDB1";

        $this->conn = oci_connect(ORAUSER, ORAPASSWRD, $connection_string, "AL32UTF8");
        oci_execute(oci_parse($this->conn, "ALTER SESSION SET NLS_DATE_FORMAT = 'DD.MM.YYYY HH24:MI'"));

        if(!$this->conn) {
            $e = oci_error($this->conn);
            die("Error!!!");
        }
    }
    
    function query($query, $binds) {
        $stid = oci_parse($this->conn, $query);
        
        if(!$stid) {
            $e = oci_error($stid);
            die("SQL Parse Error: " . $e['message']);
        }

        foreach($binds as $key => $val) {
            $param = ':' . $key;
            oci_bind_by_name($stid, $param, $binds[$key]);
        }

        $r = oci_execute($stid, OCI_COMMIT_ON_SUCCESS);
        
        if(!$r) {
            $e = oci_error($stid);
            die("SQL Execute Error: " . $e['message']);
        }

        $rows = [];
        if (stripos(trim($query), 'SELECT') === 0) {
            while($row = oci_fetch_assoc($stid)) {
                $rows[] = array_change_key_case($row, CASE_LOWER);
            }
        }

        oci_free_statement($stid);

        $this->RSArray = $rows;
        $this->rows = count($rows);
    }


    public function callFunctionToRS(string $plsql, array $inBinds, string $outName = 'result', int $outType = SQL_INTEGER, int $outSize = 4000) {
        $stmt = oci_parse($this->conn, $plsql);
        if (!$stmt) {
            $e = oci_error($this->conn);
            throw new RuntimeException($e['message']);
        }

        // Eingaben binden
        foreach ($inBinds as $k => $v) {
            oci_bind_by_name($stmt, ":$k", $inBinds[$k]);
        }

        // Ausgabe binden
        $outVal = null;
        oci_bind_by_name($stmt, ":$outName", $outVal, $outSize, $outType);

        if (!oci_execute($stmt)) {
            $e = oci_error($stmt);
            throw new RuntimeException($e['message']);
        }

        $this->RSArray = [$outName => $outVal];
        $this->rows = 1;

        oci_free_statement($stmt);
    }
}

?>