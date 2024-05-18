$(document).ready(function () {
    // Delete button click handler
    $('.delete-btn').click(function () {
        var username = $(this).data('username');
        var confirmed = confirm('Are you sure you want to delete this user?');
        if (confirmed) {
            $.ajax({
                url: 'mysql/update_user.php?action=delete',
                method: 'POST',
                data: { username: username },
                success: function (response) {
                    window.location.reload();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert('Error deleting user: ' + textStatus);
                }
            });
        }
    });

    // Checkbox click handler
    $('.update').click(function () {
        var username = $(this).data('username');
        var column = $(this).data('column');
        var value = $(this).is(':checked') ? 1 : 0;

        $.ajax({
            url: 'mysql/update_user.php?action=update',
            method: 'POST',
            data: { username: username, column: column, value: value },
            success: function (response) {
                window.location.reload();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert('Error updating user: ' + errorThrown);
            }
        });
    });

    // Add user form submit handler
    $('#addUserForm').submit(function (event) {
        event.preventDefault();

        var username = $('#username').val();

        var password = $('#password').val();
        var canBan = $('#canBan').is(':checked') ? 1 : 0;
        var canUnban = $('#canUnban').is(':checked') ? 1 : 0;
        var canDeletePastBans = $('#canDeletePastBans').is(':checked') ? 1 : 0;
        var canMute = $('#canMute').is(':checked') ? 1 : 0;
        var canUnmute = $('#canUnmute').is(':checked') ? 1 : 0;
        var canDeletePastMutes = $('#canDeletePastMutes').is(':checked') ? 1 : 0;
        var canEditUsers = $('#canEditUsers').is(':checked') ? 1 : 0;

        $.ajax({
            url: 'mysql/update_user.php?action=add',
            method: 'POST',
            data: {
                username: username,
                password: password,
                canBan: canBan,
                canUnban: canUnban,
                canDeletePastBans: canDeletePastBans,
                canMute: canMute,
                canUnmute: canUnmute,
                canDeletePastMutes: canDeletePastMutes,
                canEditUsers: canEditUsers,
            },
            success: function (response) {
                alert(response);
                window.location.reload();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert('Error adding user: ' + errorThrown);
            }
        });
    });
});