<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginTrelloProfile extends Profile {
   static $rightname = "plugin_trello";
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getType() == 'Profile') {
         return __('Trello Integration', 'trello');
      }
      return '';
   }
   
   static function createFirstAccess($profiles_id) {
      $rightname = self::$rightname;
      $profile = new self();
      
      foreach (ProfileRight::getAllPossibleRights() as $right) {
         ProfileRight::updateProfileRights($profiles_id, 
            array($rightname => ALLSTANDARDRIGHT));
      }
   }
   
   static function install(Migration $migration) {
      global $DB;
      
      $profile = new self();
      
      // Add rights in glpi_profilerights table
      foreach ($DB->request("SELECT *
                           FROM `glpi_profiles`") as $prof) {
         self::createFirstAccess($prof['id']);
      }
   }
   
   static function uninstall(Migration $migration) {
      global $DB;
      
      $tables = array('glpi_profilerights');
      
      foreach ($tables as $table) {
         $migration->dropField($table, 'plugin_trello');
      }
   }
} 