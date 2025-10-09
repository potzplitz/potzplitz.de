<?php

class Header {
    public function show_header() {
        $Template = new Template();
        $Template->load_template("general/header.php");

        if(ARR_USERINFO['userid'] == -1) {
            $pfp = "default.jpg";
            $username = "<a style='text-decoration: none;' href='/account/login'>Login</a>";

        } else {
            // $pfp = ARR_USERINFO['userid'] . ".jpg";
            $pfp = "default.jpg";
            $username = ARR_USERINFO['username'];
        }

        $Template->load_hash([
            "USERNAME" => $username,
            "USERID" => ARR_USERINFO['userid'],
            "PROFILEPICTURE" => "/static/profilepictures/" . $pfp
        ]);
        $Template->compile_template();
        $Template->show_template();
    }
}