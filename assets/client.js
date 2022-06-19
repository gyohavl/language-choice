init();

function init() {
    if (document.getElementById('refreshDuring')) {
        document.getElementById('refreshDuring').innerHTML = '<button type="button" id="refreshButton" onclick="refresh();" disabled>aktualizovat počty volných míst</button>';
        setTimeout(function () { document.getElementById('refreshButton').disabled = false; }, 3000);
    }
}

function refresh() {
    document.getElementById('refreshButton').disabled = true;
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function () {
        document.getElementById('choice').innerHTML = this.responseText;
        init();
    };
    xhttp.open('GET', location.search + '&ajax=1', true);
    xhttp.send();
}

function choose(el, event, key, language) {
    event.preventDefault();
    el.disabled = true;
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function () {
        document.getElementById('choice').innerHTML = this.responseText;
        init();
    };
    xhttp.open('POST', '.', true);
    xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhttp.send('key=' + key + '&language=' + language + '&ajax=1');
}
