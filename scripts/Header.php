<?php

class Header {
    public function show_header($request, $title) {

        load_css("header");

        $DB = new Database();

        $User = new User(SESS_USERID);
        $User->load_user();
        
        $TemplateHeader = new Template();
        $TemplateHeader->load_template("general/header.php");
        $TemplateHeader->load_hash([
            "USERNAME" => ARR_USERINFO['userid'] == -1 ? "<a href='/account/login'>Login</a>" : ARR_USERINFO['username'],
            "PROFILEPICTURE" => "/static/profilepictures/default.jpg",
            "HYPERLINK" => $request,
            "TITLE" => $title,
            "DISP_ADMIN" => ($User->isAdmin()) ? "" : "hiddenIp"
        ]);
        $TemplateHeader->compile_template();
        return $TemplateHeader->get_output();
    }
}