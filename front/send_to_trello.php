<?php

include ("../../../inc/includes.php");

Session::checkRight("ticket", UPDATE);

if (!isset($_POST['ticket_id']) || !isset($_POST['board_id']) || !isset($_POST['list_id'])) {
    Html::displayErrorAndDie(__('Missing required parameters', 'trello'));
}

$ticket = new Ticket();
if (!$ticket->getFromDB($_POST['ticket_id'])) {
    Html::displayErrorAndDie(__('Invalid ticket ID', 'trello'));
}

$config = Config::getConfigurationValues('plugin:trello');

if (empty($config['trello_api_key']) || empty($config['trello_api_token'])) {
    Html::displayErrorAndDie(__('Trello API credentials not configured', 'trello'));
}

// Przygotuj dane karty
$cardName = isset($_POST['card_name']) ? $_POST['card_name'] : "[#" . $ticket->fields['id'] . "] " . $ticket->fields['name'];

// Użyj treści z formularza jeśli została podana
$content = isset($_POST['card_content']) ? $_POST['card_content'] : '';

// Wyczyść tekst ze wszystkich wariantów \r\n i formatowania
$content = str_replace('\\r\\n', "\n", $content);
$content = str_replace('\\n', "\n", $content);
$content = str_replace('\\r', "\n", $content);
$content = str_replace('\r\n', "\n", $content);
$content = str_replace('\n', "\n", $content);
$content = str_replace('\r', "\n", $content);
$content = str_replace("\r\n", "\n", $content);
$content = str_replace("\r", "\n", $content);
$content = preg_replace('/\n\s+/', "\n", $content);
$content = preg_replace('/\n{3,}/', "\n\n", $content);
$content = trim($content);

// Przygotuj opis karty
$cardDesc = __('Ticket URL', 'trello') . ": " . $CFG_GLPI['url_base'] . "/front/ticket.form.php?id=" . $ticket->fields['id'] . "\n\n";
$cardDesc .= $content;

// Dodaj dodatkowy opis jeśli został podany
if (!empty($_POST['additional_desc'])) {
    $cardDesc .= "\n\n" . $_POST['additional_desc'];
}

// Dodaj informacje o zgłaszającym
$user = new User();
if ($user->getFromDB($ticket->fields['users_id_recipient'])) {
    $cardDesc .= "\n\n" . __('Requester', 'trello') . ": " . $user->getFriendlyName();
}

// Dodaj informacje o kategorii
if ($ticket->fields['itilcategories_id']) {
    $category = new ITILCategory();
    if ($category->getFromDB($ticket->fields['itilcategories_id'])) {
        $cardDesc .= "\n" . __('Category', 'trello') . ": " . $category->fields['name'];
    }
}

// Dodaj informacje o priorytecie
$cardDesc .= "\n" . __('Priority', 'trello') . ": " . Ticket::getPriorityName($ticket->fields['priority']);

// Debugowanie - zapisz finalną treść do pliku
file_put_contents('/tmp/trello_debug_final.txt', print_r($cardDesc, true));

// Przygotuj URL do API Trello
$url = "https://api.trello.com/1/cards";
$data = [
    'key' => $config['trello_api_key'],
    'token' => $config['trello_api_token'],
    'idList' => $_POST['list_id'],
    'name' => $cardName,
    'desc' => $cardDesc,
    'pos' => 'top'
];

// Dodaj członka do karty jeśli został wybrany
if (!empty($_POST['member_id'])) {
    $data['idMembers'] = $_POST['member_id'];
    
    // Debug: Zapisz informacje o member_id
    file_put_contents('/tmp/trello_debug_member.txt', 
        "Member ID from POST: " . $_POST['member_id'] . "\n" .
        "Data array: " . print_r($data, true)
    );
}

// Debug: Pokaż używane dane
Html::header(__('Send to Trello', 'trello'), $_SERVER['PHP_SELF'], 'tools', 'PluginTrelloTicket');

echo "<style>
.debug-section {
    max-width: 900px;
    margin: 20px auto;
    font-family: 'Courier New', monospace;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 8px;
}

.debug-section h2 {
    background: #0079BF;
    color: white;
    padding: 15px 20px;
    border-radius: 8px 8px 0 0;
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.debug-content {
    padding: 20px;
}

.debug-group {
    margin-bottom: 25px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 15px;
}

.debug-group:last-child {
    margin-bottom: 0;
}

.debug-group h3 {
    color: #42526E;
    font-size: 14px;
    margin: 0 0 15px 0;
    padding-bottom: 8px;
    border-bottom: 1px solid #ddd;
}

.debug-item {
    display: grid;
    grid-template-columns: 150px 1fr;
    gap: 15px;
    margin-bottom: 10px;
    line-height: 1.4;
}

.debug-label {
    color: #42526E;
    font-weight: 600;
    font-size: 13px;
}

.debug-value {
    color: #172b4d;
    word-break: break-all;
    font-size: 13px;
    background: #fff;
    padding: 8px 12px;
    border-radius: 4px;
    border: 1px solid #ddd;
}

.debug-response {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    max-height: 400px;
    overflow-y: auto;
}

.debug-response pre {
    margin: 0;
    white-space: pre-wrap;
    font-size: 13px;
    line-height: 1.5;
    color: #172b4d;
}

.success-response {
    border-left: 4px solid #36B37E;
}

.error-response {
    border-left: 4px solid #FF5630;
}

.message-box {
    margin: 20px auto;
    max-width: 900px;
    padding: 15px 20px;
    border-radius: 4px;
    background: #E3FCEF;
    border: 1px solid #36B37E;
    color: #006644;
}

.message-box a {
    color: #0052CC;
    text-decoration: none;
}

.message-box a:hover {
    text-decoration: underline;
}

.message-box.error {
    background: #FFEBE6;
    border-color: #FF5630;
    color: #BF2600;
}
</style>";

// Dodaj sekcję debugowania dla member_id
echo "<div class='debug-group'>";
echo "<h3>Dane członka</h3>";
echo "<div class='debug-item'>";
echo "<span class='debug-label'>Member ID:</span>";
echo "<span class='debug-value'>" . (isset($_POST['member_id']) ? $_POST['member_id'] : 'Nie wybrano') . "</span>";
echo "</div>";
echo "</div>";

// Wyślij żądanie do API Trello
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Sekcja debugowania
echo "<div class='debug-section'>";
echo "<h2>Informacje debugowania</h2>";
echo "<div class='debug-content'>";

// Dane do wysłania
echo "<div class='debug-group'>";
echo "<h3>Dane do wysłania</h3>";
echo "<div class='debug-item'>";
echo "<span class='debug-label'>API URL:</span>";
echo "<span class='debug-value'>" . $url . "</span>";
echo "</div>";
echo "<div class='debug-item'>";
echo "<span class='debug-label'>ID Listy:</span>";
echo "<span class='debug-value'>" . $_POST['list_id'] . "</span>";
echo "</div>";
echo "</div>";

// Odpowiedź API
echo "<div class='debug-group'>";
echo "<h3>Odpowiedź API</h3>";
echo "<div class='debug-item'>";
echo "<span class='debug-label'>Kod HTTP:</span>";
echo "<span class='debug-value'>" . $httpCode . "</span>";
echo "</div>";
echo "<div class='debug-response " . ($httpCode == 200 ? 'success-response' : 'error-response') . "'>";
echo "<pre>" . json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
echo "</div>";
echo "</div>";

echo "</div>";
echo "</div>";

if ($httpCode === 200) {
    // Zapisz informację o wysłaniu do Trello w historii zgłoszenia
    $response_data = json_decode($response, true);
    $card_url = $response_data['shortUrl'];
    
    echo "<div class='message-box'>";
    echo "Zgłoszenie zostało pomyślnie wysłane do Trello: <a href='" . $card_url . "' target='_blank'>" . $card_url . "</a>";
    echo "</div>";
    
    $log = [
        'items_id' => $ticket->fields['id'],
        'itemtype' => 'Ticket',
        'type' => Log::HISTORY_TYPE_SIMPLE,
        'service' => 'trello',
        'linked_action' => Log::HISTORY_TYPE_LOG,
        'user_name' => $_SESSION['glpiname'],
        'date_mod' => $_SESSION['glpi_currenttime'],
        'id_search_option' => 0,
        'old_value' => '',
        'new_value' => sprintf(__('Sent to Trello: %s', 'trello'), $card_url)
    ];
    
    Log::history($ticket->fields['id'], 'Ticket', $log);
    
    Session::addMessageAfterRedirect(
        __('Ticket successfully sent to Trello', 'trello'),
        true,
        INFO
    );

    // Jeśli karta została utworzona i jest dodatkowy opis, dodaj go jako komentarz
    if (!empty($_POST['additional_desc'])) {
        $card_id = $response_data['id'];
        
        // Przygotuj URL do dodania komentarza
        $comment_url = "https://api.trello.com/1/cards/" . $card_id . "/actions/comments";
        $comment_data = [
            'key' => $config['trello_api_key'],
            'token' => $config['trello_api_token'],
            'text' => $_POST['additional_desc']
        ];
        
        // Wyślij komentarz
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $comment_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($comment_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $comment_response = curl_exec($ch);
        curl_close($ch);
    }
} else {
    echo "<div class='message-box error'>";
    echo "Wystąpił błąd podczas wysyłania do Trello: " . htmlspecialchars($response);
    echo "</div>";
    
    Session::addMessageAfterRedirect(
        __('Error sending ticket to Trello', 'trello') . ': ' . $response,
        true,
        ERROR
    );
}

Html::back(); 