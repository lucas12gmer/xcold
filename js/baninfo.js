const templateSelect = document.getElementById("template-select");

templateSelect.addEventListener("change", () => {
    const selectedOption = templateSelect.options[templateSelect.selectedIndex];
    if (selectedOption.value !== "") {
        const timeAndUnit = selectedOption.text.split(" - ")[0];
        const reason = selectedOption.text.split(" - ")[1];
        const time = timeAndUnit.substring(0, timeAndUnit.length - 1);
        var unit = "";
        switch (timeAndUnit.substring(timeAndUnit.length - 1)) {
            case "m":
                unit = "minutes";
                break;
            case "h":
                unit = "hours";
                break;
            case "d":
                unit = "days";
                break;
            case "w":
                unit = "weeks";
                break;
            case "y":
                unit = "years";
                break;
        }
        document.getElementById("period").value = time;
        document.getElementById("reason").value = reason;
        document.getElementById("period-select").value = unit;
    }
});


var clearButton = document.getElementById('clear-button');
if (clearButton != null) {
    clearButton.addEventListener('click', function () {
        var uuid = clearButton.getAttribute('uuid');
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'mysql/delete_all_bans.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send('uuid=' + uuid);
        xhr.onload = function () {
            showInfoBox(xhr.responseText);
        };
    });
}

var banFormSubmit = document.getElementById('banformsubmit');
if (banFormSubmit != null) {
    banFormSubmit.addEventListener('submit', function (e) {
        e.preventDefault();

        var formData = new FormData(banFormSubmit);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'mysql/ban_player.php');
        xhr.send(formData);

        xhr.onload = function () {
            showInfoBox(xhr.responseText);
        };
    });

}

var unbanFormSubmit = document.getElementById('unbanformsubmit');
if (unbanFormSubmit != null) {
    unbanFormSubmit.addEventListener('submit', function (e) {
        e.preventDefault();

        var formData = new FormData(unbanFormSubmit);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'mysql/unban_player.php');
        xhr.send(formData);

        xhr.onload = function () {
            showInfoBox(xhr.responseText);
        };
    });

}

document.querySelectorAll('.delete-btn').forEach(function (button) {
    button.addEventListener('click', function () {
        var uuid = button.getAttribute('uuid');
        var startTime = button.getAttribute('startTime');
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'mysql/delete_ban.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send('uuid=' + uuid + '&startTime=' + startTime);
        xhr.onload = function () {
            showInfoBox(xhr.responseText);
        };
    });
});

function showInfoBox(message) {
    var infoBox = document.getElementById('info-box');

    infoBox.textContent = message;
    infoBox.style.opacity = 1;

    if (message.startsWith('Error')) {
        infoBox.style.color = '#D8000C';
        infoBox.style.backgroundColor = '#FFBABA';
    } else {
        infoBox.style.color = '#270';
        infoBox.style.backgroundColor = '#DFF2BF';
        setTimeout(function () {
            location.reload();
        }, 2000);
    }
}

var unbanForm = document.getElementById('unbanform');
var banForm = document.getElementById('banform');

var banButton = document.getElementById('ban-button');
var unbanButton = document.getElementById('unban-button');


if (banButton != null) {
    banButton.addEventListener('click', function (event) {
        event.stopPropagation();

        banForm.style.display = "block";
        setTimeout(function () {
            banForm.style.opacity = 1;
        }, 10);
    });
}

if (unbanButton != null) {
    unbanButton.addEventListener('click', function (event) {
        event.stopPropagation();

        unbanForm.style.display = "block";
        setTimeout(function () {
            unbanForm.style.opacity = 1;
        }, 10);
    });
}


document.addEventListener('click', function (event) {
    if (event.target !== banButton && !banForm.contains(event.target)) {
        banForm.style.opacity = 0;
        setTimeout(function () {
            banForm.style.display = "none";
        }, 500);
    }

    if (event.target !== unbanButton && !unbanForm.contains(event.target)) {
        unbanForm.style.opacity = 0;
        setTimeout(function () {
            unbanForm.style.display = "none";
        }, 500);
    }
});
