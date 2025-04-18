<?php
/**
 * Plugin to send tickets to Trello
 *
 * @since 1.0.0
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

function plugin_version_trello() {
    return array(
        'name'           => 'GLPI - Trello Integration',
        'version'        => '1.0.0',
        'author'         => 'My-it.pl - Paweł Adamczuk',
        'license'        => 'GPLv3+',
        'homepage'       => 'https://my-it.pl',
        'min_glpi_version' => '9.5',
    );
}

function plugin_trello_install() {
    // Instalacja pluginu
    Plugin::addConfig('trello_api_key', '');
    Plugin::addConfig('trello_api_token', '');
    Plugin::addConfig('trello_board_id', '');
    Plugin::addConfig('trello_list_id', '');
    return true;
}

function plugin_trello_uninstall() {
    // Odinstalowanie pluginu
    Plugin::deleteConfig('trello_api_key');
    Plugin::deleteConfig('trello_api_token');
    Plugin::deleteConfig('trello_board_id');
    Plugin::deleteConfig('trello_list_id');
    return true;
}

function plugin_trello_menu() {
    // Dodanie menu do pluginu w GLPI
    global $PLUGIN_HOOKS;
    $PLUGIN_HOOKS['add_config_page']['trello'] = 'front/send_to_trello_form.php';
}