<?php
include ("../../../inc/includes.php");

$trello = new Trello();
$boards = $trello->getBoards();
echo json_encode($boards); 