<?php
include ("../../../inc/includes.php");

if (!isset($_GET['board_id'])) {
    die(json_encode(['error' => 'Missing board_id']));
}

$trello = new Trello();
$lists = $trello->getLists($_GET['board_id']);
echo json_encode($lists); 