<?php

class Userlist implements AdminModuleInterface {
    private $users = [];
    public function init() {
        set_title("Userlist");
        $this->displayUserList();
    } 

    private function displayUserList() {
        echo "TODO";
    }

    private function loadUsers() {
        $DB = new Database();

        $query = "SELECT * from t_users";
        $DB->query($query, []);
        $this->users = $DB->RSArray;
    }
}