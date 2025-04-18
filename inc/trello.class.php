<?php

class Trello {
    private $apiKey;
    private $apiToken;
    private $boardId;
    private $listId;

    public function __construct() {
        // Pobranie ustawień z konfiguracji GLPI
        $config = Config::getConfigurationValues('plugin:trello');
        $this->apiKey = $config['trello_api_key'];
        $this->apiToken = $config['trello_api_token'];
        $this->boardId = $config['trello_board_id'];
        $this->listId = $config['trello_list_id'];
    }

    public function getBoards() {
        $url = "https://api.trello.com/1/members/me/boards?key={$this->apiKey}&token={$this->apiToken}";
        $result = file_get_contents($url);
        return json_decode($result, true);
    }

    public function getLists($boardId) {
        $url = "https://api.trello.com/1/boards/{$boardId}/lists?key={$this->apiKey}&token={$this->apiToken}";
        $result = file_get_contents($url);
        return json_decode($result, true);
    }

    public function getBoardMembers($boardId) {
        $url = "https://api.trello.com/1/boards/{$boardId}/members?key={$this->apiKey}&token={$this->apiToken}";
        $response = file_get_contents($url);
        if ($response === false) {
            return ['error' => 'Failed to get board members'];
        }
        return json_decode($response, true);
    }

    public function createCard($taskTitle, $taskDescription) {
        $url = "https://api.trello.com/1/cards";
        $data = array(
            'name' => $taskTitle,
            'desc' => $taskDescription,
            'idList' => $this->listId,
            'key' => $this->apiKey,
            'token' => $this->apiToken
        );

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ),
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return json_decode($result, true);
    }
}