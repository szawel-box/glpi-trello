<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginTrelloConfig extends CommonDBTM {
   
   static function getTypeName($nb = 0) {
      return __('Trello Configuration', 'trello');
   }

   static function canCreate() {
      return Session::haveRight('config', UPDATE);
   }

   static function canView() {
      return Session::haveRight('config', READ);
   }

   static function canUpdate() {
      return Session::haveRight('config', UPDATE);
   }

   static function getConfig($force_reload = false) {
      static $config = null;

      if (is_null($config) || $force_reload) {
         $config = new self();
      }

      return $config;
   }

   static function showConfigForm() {
      $config = new self();
      
      echo "<form name='form' method='post' action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      echo "<div class='center' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'>";
      
      echo "<tr><th colspan='4'>" . __('Trello API Configuration', 'trello') . "</th></tr>";
      
      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('API Key', 'trello') . "</td>";
      echo "<td><input type='text' name='trello_api_key' value='" . Config::getConfigurationValues('plugin:trello')['trello_api_key'] . "'></td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('API Token', 'trello') . "</td>";
      echo "<td><input type='text' name='trello_api_token' value='" . Config::getConfigurationValues('plugin:trello')['trello_api_token'] . "'></td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Board ID', 'trello') . "</td>";
      echo "<td><input type='text' name='trello_board_id' value='" . Config::getConfigurationValues('plugin:trello')['trello_board_id'] . "'></td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('List ID', 'trello') . "</td>";
      echo "<td><input type='text' name='trello_list_id' value='" . Config::getConfigurationValues('plugin:trello')['trello_list_id'] . "'></td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='4' class='center'>";
      echo "<input type='submit' name='update' class='submit' value='" . __('Save') . "'>";
      echo "</td></tr>";
      
      echo "</table></div>";
      Html::closeForm();
   }

   static function updateConfig($input) {
      Config::setConfigurationValues('plugin:trello', [
         'trello_api_key' => $input['trello_api_key'],
         'trello_api_token' => $input['trello_api_token'],
         'trello_board_id' => $input['trello_board_id'],
         'trello_list_id' => $input['trello_list_id']
      ]);
   }
} 