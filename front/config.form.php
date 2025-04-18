<?php
include ("../../../inc/includes.php");

Session::checkRight("config", UPDATE);

Html::header('Trello Configuration', $_SERVER['PHP_SELF'], "config", "plugins");

if (isset($_POST["update"])) {
   Config::setConfigurationValues('plugin:trello', [
      'trello_api_key' => $_POST['trello_api_key'],
      'trello_api_token' => $_POST['trello_api_token'],
      'trello_board_id' => $_POST['trello_board_id'],
      'trello_list_id' => $_POST['trello_list_id']
   ]);
   Session::addMessageAfterRedirect(__('Configuration saved successfully', 'trello'));
   Html::back();
}

PluginTrelloConfig::showConfigForm();

Html::footer(); 