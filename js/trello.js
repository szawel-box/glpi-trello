$(document).ready(function() {
    // Pobierz tablice
    $.ajax({
        url: '../ajax/get_boards.php',
        method: 'GET',
        success: function(response) {
            var boards = JSON.parse(response);
            var boardSelect = $('#board_select');
            boardSelect.empty();
            boardSelect.append($('<option>').val('').text('Wybierz tablicę'));
            
            boards.forEach(function(board) {
                boardSelect.append($('<option>')
                    .val(board.id)
                    .text(board.name));
            });
        }
    });

    // Gdy wybrano tablicę, pobierz listy i członków
    $('#board_select').change(function() {
        var boardId = $(this).val();
        if (boardId) {
            // Pobierz listy
            $.ajax({
                url: '../ajax/get_lists.php',
                method: 'GET',
                data: { board_id: boardId },
                success: function(response) {
                    var lists = JSON.parse(response);
                    var listSelect = $('#list_select');
                    listSelect.empty();
                    listSelect.append($('<option>').val('').text('Wybierz listę'));
                    
                    lists.forEach(function(list) {
                        listSelect.append($('<option>')
                            .val(list.id)
                            .text(list.name));
                    });
                    
                    listSelect.prop('disabled', false);
                }
            });

            // Pobierz członków
            $.ajax({
                url: '../ajax/get_members.php',
                method: 'GET',
                data: { board_id: boardId },
                success: function(response) {
                    var members = JSON.parse(response);
                    var memberSelect = $('#member_select');
                    memberSelect.empty();
                    memberSelect.append($('<option>').val('').text('Wybierz członka'));
                    
                    members.forEach(function(member) {
                        memberSelect.append($('<option>')
                            .val(member.id)
                            .text(member.fullName || member.username));
                    });
                    
                    memberSelect.prop('disabled', false);
                }
            });
        }
    });
}); 