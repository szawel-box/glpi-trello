<?php

class PluginTrello extends Plugin {
    public static function getTypeName($nb = 0) {
        return __('Trello Integration', 'trello');
    }

    public static function getConfig() {
        return Config::getConfigurationValues('plugin:trello');
    }

    public static function setConfig($values) {
        return Config::setConfigurationValues('plugin:trello', $values);
    }

    public static function canCreate() {
        return Session::haveRight('config', UPDATE);
    }

    public static function canView() {
        return Session::haveRight('config', READ);
    }

    public static function canUpdate() {
        return Session::haveRight('config', UPDATE);
    }
} 