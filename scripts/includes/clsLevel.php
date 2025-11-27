<?php

enum ListArt {
    case AREDL;
    case NCL;
}

class Level {
    private ListArt $listArt;
    private array $levelInfo = [];
    private int $results = 0;
    public function __construct(string $levelid, ListArt $listArt) {
        $DB = new Database();

        $this->listArt = $listArt;

        $art = match($this->listArt) {
            ListArt::AREDL => 'AREDL',
            ListArt::NCL => 'NCL',
        };

        $query = "SELECT * from t_" . $art . " where id = :id";
        $binds = [
            "id" => $levelid
        ];

        $DB->query($query, $binds);

        $this->levelInfo = $DB->RSArray ?? [];
        $this->results = $DB->rows;
    }

    public function exists() {
        return $this->results > 0;
    }

    public function placement() {
        return $this->levelInfo[0]['position'];
    }

    public function name() {
        return $this->levelInfo[0]['name'];
    }

    public function id() {
        return $this->levelInfo[0]['id'];
    }

    public function song() {
        return $this->levelInfo[0]['song'];
    }

    public function creator() {
        return $this->levelInfo[0]['creator'];
    }

    public function verifier() {
        if(!ListArt::NCL) {
            return $this->levelInfo[0]['verifier'];
        } else {
            return "";
        }
    }

    public function version() {
        return $this->levelInfo[0]['version'];
    }

    public function two_player() {
        return $this->levelInfo[0]['two_player'];
    }

    public function legacy() {
        return $this->levelInfo[0]['legacy'];
    }

    public function raw_name() {
        return $this->levelInfo[0]['raw_name'];
    }
}