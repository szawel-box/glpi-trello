<?php
define('PLUGIN_TRELLO_VERSION', '1.0.0');

// Init the hooks of the plugins.
function plugin_init_trello() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['trello'] = true;
   
   if (Session::getLoginUserID()) {
      if (Session::haveRight('config', UPDATE)) {
         $PLUGIN_HOOKS['config_page']['trello'] = 'front/config.form.php';
         Plugin::registerClass('PluginTrelloConfig', ['addtabon' => 'Config']);
      }

      if (Session::haveRight('ticket', READ)) {
         Plugin::registerClass('PluginTrelloTicket');
         $PLUGIN_HOOKS['menu_toadd']['trello'] = ['tools' => 'PluginTrelloTicket'];
      }

      // Dodaj przycisk "Wyślij do Trello" w interfejsie biletu
      $PLUGIN_HOOKS['post_item_form']['trello'] = 'plugin_trello_post_item_form';
   }
}

function plugin_trello_post_item_form($params) {
    if ($params['item'] instanceof Ticket && Session::haveRight('ticket', UPDATE)) {
        $ticket_id = $params['item']->getID();
        $url = Plugin::getWebDir('trello') . '/front/send_to_trello_form.php?ticket_id=' . $ticket_id;
        
        echo "<a href='{$url}' class='btn btn-primary' style='margin: 5px;'>";
        echo "<i class='fas fa-paper-plane'></i> ";
        echo __('Send to Trello', 'trello');
        echo "</a>";
    }
}

// Get the name and the version of the plugin
function plugin_version_trello() {
   global $CFG_GLPI;
   return [
      'name'           => 'GLPI - Trello Integration',
      'version'        => PLUGIN_TRELLO_VERSION,
      'author'         => 'My-it.pl - Paweł Adamczuk',
      'license'        => 'GPLv3+',
      'homepage'       => 'https://my-it.pl',
      'logo'           => Plugin::getWebDir('trello') . '/trello.png',
      'requirements'   => [
         'glpi'   => [
            'min' => '9.5',
            'max' => '10.1',
            'dev' => false
         ]
      ]
   ];
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_trello_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '9.5', 'lt') || version_compare(GLPI_VERSION, '10.1', 'gt')) {
      echo "This plugin requires GLPI >= 9.5 and < 10.1";
      return false;
   }
   return true;
}

// Check configuration process for plugin
function plugin_trello_check_config($verbose = false) {
   if (true) {
      return true;
   }

   if ($verbose) {
      echo "Installed, but not configured";
   }
   return false;
}

function plugin_trello_install() {
   global $DB;

   if (!$DB->tableExists("glpi_plugin_trello_config")) {
      $query = "CREATE TABLE `glpi_plugin_trello_config` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
         `value` text COLLATE utf8_unicode_ci,
         PRIMARY KEY (`id`),
         UNIQUE KEY `name` (`name`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      
      $DB->query($query) or die("Nie można utworzyć tabeli glpi_plugin_trello_config " . $DB->error());
   }

   // Dodaj domyślne wartości konfiguracyjne
   Config::setConfigurationValues('plugin:trello', [
      'trello_api_key' => '',
      'trello_api_token' => '',
      'trello_board_id' => '',
      'trello_list_id' => ''
   ]);

   return true;
}

function plugin_trello_uninstall() {
   global $DB;

   $tables = ['glpi_plugin_trello_config'];
   
   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`");
   }

   Config::deleteConfigurationValues('plugin:trello', [
      'trello_api_key',
      'trello_api_token',
      'trello_board_id',
      'trello_list_id'
   ]);

   return true;
}

// Funkcja dodająca przycisk do linii czasu zgłoszenia
function plugin_trello_timeline_actions($item) {
   if (!($item instanceof Ticket)) {
      return [];
   }
   
   if (!Session::haveRight('plugin_trello', READ)) {
      return [];
   }
   
   return [
      'trello' => [
         'icon'  => 'fas fa-paper-plane',
         'label' => __('Send to Trello', 'trello'),
         'url'   => '../plugins/trello/front/send_to_trello_form.php?ticket_id=' . $item->getID()
      ]
   ];
} 