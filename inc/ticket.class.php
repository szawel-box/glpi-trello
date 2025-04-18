<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginTrelloTicket extends CommonDBTM {
    
    public static function getTypeName($nb = 0) {
        return __('Trello Integration', 'trello');
    }

    static function canView() {
        return Session::haveRight('ticket', READ);
    }

    static function canCreate() {
        return Session::haveRight('ticket', UPDATE);
    }

    static function canUpdate() {
        return Session::haveRight('ticket', UPDATE);
    }

    static function getMenuContent() {
        $menu = [];
        if (Session::haveRight('ticket', READ)) {
            $menu['title'] = self::getTypeName(2);
            $menu['page'] = '/plugins/trello/front/config.form.php';
            $menu['icon'] = 'fas fa-paper-plane';
        }
        return $menu;
    }
}