const searchInput = document.getElementById('search-input');
const toggleSwitch = document.getElementById('toggler');
const currentTheme = localStorage.getItem('theme');

searchInput.addEventListener('keyup', function (event) {
    if (event.key === 'Enter') {
        const playerName = searchInput.value;
        const currentUrl = window.location.href;
        const urlParts = currentUrl.split('/'); // split the URL into parts by '/'
        const newUrl = urlParts.slice(0, -1).join('/') + `/baninfo.php?name=${playerName}`;

        window.location.href = newUrl;
    }
});

toggleSwitch.addEventListener('change', switchTheme, false);

if (currentTheme) {
    document.documentElement.setAttribute('data-theme', currentTheme);

    if (currentTheme === 'dark') {
        toggleSwitch.checked = true;
    }
}

function switchTheme(e) {
    var currentTheme = document.documentElement.getAttribute("data-theme");
    if (e.target.checked) {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.setAttribute('data-theme', 'light');
        localStorage.setItem('theme', 'light');
    }
}
