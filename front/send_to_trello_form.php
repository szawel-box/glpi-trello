<?php

include ("../../../inc/includes.php");

Session::checkRight("ticket", UPDATE);

if (!isset($_GET['ticket_id'])) {
    Html::displayErrorAndDie(__('Missing ticket ID', 'trello'));
}

$ticket = new Ticket();
if (!$ticket->getFromDB($_GET['ticket_id'])) {
    Html::displayErrorAndDie(__('Invalid ticket ID', 'trello'));
}

$config = Config::getConfigurationValues('plugin:trello');

if (empty($config['trello_api_key']) || empty($config['trello_api_token'])) {
    Html::displayErrorAndDie(__('Trello API credentials not configured', 'trello'));
}

// Debug: Sprawdź połączenie z API Trello
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.trello.com/1/members/me/boards?key=" . $config['trello_api_key'] . "&token=" . $config['trello_api_token']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Wyświetl informacje debugujące
echo "<div class='debug-info' style='background: #f5f5f5; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
echo "<h3>Debug Info:</h3>";
echo "<pre>";
echo "HTTP Code: " . $httpCode . "\n";
echo "Response: " . htmlspecialchars($response) . "\n";
echo "</pre>";
echo "</div>";

// Wyczyść treść z HTML zachowując formatowanie
$content = $ticket->fields['content'];
$content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$content = preg_replace('/<br\s*\/?>/i', "\n", $content);
$content = preg_replace('/<\/p>/i', "\n", $content);
$content = strip_tags($content);
$content = trim($content);

// Usuń encje HTML
$content = str_replace(['&#60;', '&#62;', '&nbsp;', '&#38;', '&#34;', '&#39;'], ['<', '>', ' ', '&', '"', "'"], $content);
$content = preg_replace('/&#[0-9]+;/', '', $content);
$content = preg_replace('/&[a-zA-Z]+;/', '', $content);

// Zachowaj pojedyncze nowe linie, usuń tylko nadmiarowe
$content = preg_replace('/\n\n\n+/', "\n\n", $content);

Html::header(__('Send to Trello', 'trello'), $_SERVER['PHP_SELF'], 'tools', 'PluginTrelloTicket');

echo "<style>
.trello-form {
    max-width: 900px;
    margin: 20px auto;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.trello-form table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.trello-form th {
    background: #0079BF;
    color: white;
    padding: 15px;
    font-size: 1.2em;
    border-radius: 8px 8px 0 0;
}

.trello-form td {
    padding: 12px 15px;
    vertical-align: middle;
}

.trello-form select, .trello-form textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.trello-form select:hover, .trello-form textarea:hover {
    border-color: #0079BF;
}

.trello-form select:focus, .trello-form textarea:focus {
    border-color: #0079BF;
    outline: none;
    box-shadow: 0 0 0 2px rgba(0,121,191,0.2);
}

.trello-form textarea {
    min-height: 100px;
    resize: vertical;
}

.trello-form .submit {
    background: #0079BF;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.3s;
}

.trello-form .submit:hover {
    background: #026AA7;
}

.trello-form .cancel {
    background: #EDEDED;
    color: #444;
}

.trello-form .cancel:hover {
    background: #E2E2E2;
}

.debug-info {
    display: none;
}
</style>";

echo "<div class='trello-form center'>";
echo "<form method='post' action='send_to_trello.php'>";
echo "<table class='tab_cadre_fixe'>";

// Nagłówek formularza
echo "<tr><th colspan='2'>Wyślij zgłoszenie do Trello</th></tr>";

// Informacje o zgłoszeniu
echo "<tr class='tab_bg_1'>";
echo "<td>Tytuł zgłoszenia</td>";
echo "<td>";
echo "<input type='text' name='card_name' class='form-control' value='#" . $ticket->fields['id'] . " - " . $ticket->fields['name'] . "' style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
echo "</td>";
echo "</tr>";

// Wybór tablicy
echo "<tr class='tab_bg_1'>";
echo "<td>Tablica Trello</td>";
echo "<td>";
echo "<select name='board_id' id='board_select' class='form-control' required>";
echo "<option value=''>Ładowanie tablic...</option>";
echo "</select>";
echo "</td>";
echo "</tr>";

// Wybór listy
echo "<tr class='tab_bg_1'>";
echo "<td>Lista Trello</td>";
echo "<td>";
echo "<select name='list_id' id='list_select' class='form-control' required disabled>";
echo "<option value=''>Najpierw wybierz tablicę</option>";
echo "</select>";
echo "</td>";
echo "</tr>";

// Wybór członka
echo "<tr class='tab_bg_1'>";
echo "<td>Przypisz do</td>";
echo "<td>";
echo "<select name='member_id' id='member_select' class='form-control'>";
echo "<option value=''>Najpierw wybierz tablicę</option>";
echo "</select>";
echo "</td>";
echo "</tr>";

// Dodaj pole do edycji treści przed sekcją etykiet
echo "<tr class='tab_bg_1'>";
echo "<td>Treść do wysłania</td>";
echo "<td>";
echo "<textarea name='card_content' class='form-control' rows='10' style='width: 100%;'>";
echo htmlspecialchars($content);
echo "</textarea>";
echo "</td>";
echo "</tr>";

// Dodatkowy opis
echo "<tr class='tab_bg_1'>";
echo "<td>Dodatkowy opis</td>";
echo "<td>";
echo "<textarea name='additional_desc' class='form-control' rows='4'></textarea>";
echo "</td>";
echo "</tr>";

// Wybór etykiet
echo "<tr class='tab_bg_1'>";
echo "<td>Etykiety</td>";
echo "<td>";
echo "<select name='labels[]' id='label_select' class='form-control' multiple disabled>";
echo "<option value=''>Najpierw wybierz tablicę</option>";
echo "</select>";
echo "</td>";
echo "</tr>";

// Przyciski
echo "<tr class='tab_bg_2'>";
echo "<td colspan='2' class='center'>";
echo "<input type='hidden' name='ticket_id' value='" . $ticket->fields['id'] . "'>";
echo "<input type='submit' name='send' value='Wyślij do Trello' class='submit' id='submit-btn'>";
echo "&nbsp;";
echo "<input type='button' name='cancel' value='Anuluj' class='submit cancel' onclick='history.back()'>";
echo "</td>";
echo "</tr>";

echo "</table>";
Html::closeForm();
echo "</div>";

// Dodaj zabezpieczenie przed podwójnym wysłaniem
echo "<script type='text/javascript'>
jQuery(document).ready(function($) {
    $('form').on('submit', function(e) {
        var form = $(this);
        var submitBtn = $('#submit-btn');
        
        if (form.data('submitted')) {
            e.preventDefault();
            return;
        }
        
        submitBtn.prop('disabled', true);
        submitBtn.val('Wysyłanie...');
        form.data('submitted', true);
    });
});
</script>";

// Skrypt JavaScript do pobierania danych z API Trello
echo "<script type='text/javascript'>
jQuery(document).ready(function($) {
    var defaultBoardName = 'MY-IT.PL';
    var defaultListName = 'DO ZROBIENIA';
    var defaultBoardId = '';
    var defaultListId = '';

    // Pobierz tablice
    $.get('https://api.trello.com/1/members/me/boards?key=" . $config['trello_api_key'] . "&token=" . $config['trello_api_token'] . "', function(boards) {
        var boardSelect = $('#board_select');
        boardSelect.empty();
        boardSelect.append($('<option>').val('').text('Wybierz tablicę'));
        $.each(boards, function(i, board) {
            var option = $('<option>').val(board.id).text(board.name);
            if (board.name === defaultBoardName) {
                defaultBoardId = board.id;
                option.prop('selected', true);
            }
            boardSelect.append(option);
        });
        if (defaultBoardId) {
            loadBoardData(defaultBoardId);
        }
    });

    function loadBoardData(boardId) {
        // Pobierz listy
        $.get('https://api.trello.com/1/boards/' + boardId + '/lists?key=" . $config['trello_api_key'] . "&token=" . $config['trello_api_token'] . "', function(lists) {
            var listSelect = $('#list_select');
            listSelect.empty();
            listSelect.append($('<option>').val('').text('Wybierz listę'));
            $.each(lists, function(i, list) {
                var option = $('<option>').val(list.id).text(list.name);
                if (list.name === defaultListName) {
                    option.prop('selected', true);
                }
                listSelect.append(option);
            });
            listSelect.prop('disabled', false);
        });

        // Pobierz członków
        $.get('https://api.trello.com/1/boards/' + boardId + '/members?key=" . $config['trello_api_key'] . "&token=" . $config['trello_api_token'] . "', function(members) {
            var memberSelect = $('#member_select');
            memberSelect.empty();
            memberSelect.append($('<option>').val('').text('Bez przypisania'));
            $.each(members, function(i, member) {
                memberSelect.append($('<option>').val(member.id).text(member.fullName || member.username));
            });
            memberSelect.prop('disabled', false);
        });

        // Pobierz etykiety
        $.get('https://api.trello.com/1/boards/' + boardId + '/labels?key=" . $config['trello_api_key'] . "&token=" . $config['trello_api_token'] . "', function(labels) {
            var labelSelect = $('#label_select');
            labelSelect.empty();
            $.each(labels, function(i, label) {
                var labelText = label.name || label.color;
                labelSelect.append($('<option>').val(label.id).text(labelText));
            });
            labelSelect.prop('disabled', false);
        });
    }

    // Po wyborze tablicy, pobierz dane
    $('#board_select').change(function() {
        var boardId = $(this).val();
        if (boardId) {
            loadBoardData(boardId);
        } else {
            $('#list_select, #member_select, #label_select').prop('disabled', true).empty()
                .append($('<option>').val('').text('Najpierw wybierz tablicę'));
        }
    });
});
</script>";

Html::footer(); 