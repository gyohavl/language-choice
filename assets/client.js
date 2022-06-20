init();
var countdown, countDownDate, refreshResolved = true;

function init(refreshed) {
    if (document.getElementById('refreshBefore')) {
        document.getElementById('refreshBefore').innerHTML = '<p>Zbývající čas: <span id="remaining">00:00</span></p><p>' + getRefreshButton('obnovit stránku', refreshed) + '</p>';
        countDownDate = new Date(document.getElementById('refreshBefore').getAttribute('data-time')).getTime();
        clearInterval(countdown);
        countdown = setInterval(count, 1000);
        count(true);
    }

    if (document.getElementById('refreshDuring')) {
        document.getElementById('refreshDuring').innerHTML = getRefreshButton('aktualizovat počty volných míst', refreshed);
    }
}

function getRefreshButton(text, refreshed) {
    setTimeout(function () {
        if (document.getElementById('refreshButton')) {
            document.getElementById('refreshButton').disabled = false;
            document.getElementById('refreshButton').removeAttribute('class');
        }
    }, 3000);
    const loaded = refreshed ? ' class="loaded"' : '';
    return '<button type="button" id="refreshButton"' + loaded + ' onclick="refresh(this);" disabled>' + text + '</button><span class="progress-text"></span>';
}

function count(initial) {
    var now = new Date().getTime();
    var distance = countDownDate - now;
    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((distance % (1000 * 60)) / 1000);
    hours = hours > 0 ? hours + ':' : '';
    minutes = minutes < 10 ? '0' + minutes : minutes;
    seconds = seconds < 10 ? '0' + seconds : seconds;

    if (distance < 1000) {
        if (document.getElementById('remaining')) {
            document.getElementById('remaining').innerHTML = '00:00';

            if (distance > -10000 && !initial && refreshResolved) {
                refresh();
            }
        } else {
            clearInterval(countdown);
        }
    } else {
        if (document.getElementById('remaining')) {
            document.getElementById('remaining').innerHTML = hours + minutes + ':' + seconds;
        } else {
            clearInterval(countdown);
        }
    }
}

function refresh(el) {
    refreshResolved = false;
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function () {
        document.getElementById('choice').innerHTML = this.responseText;
        refreshResolved = true;
        init(true);
    };
    xhttp.open('GET', location.search + '&ajax=1', true);
    xhttp.send();

    if (el) {
        el.disabled = true;
        el.setAttribute('class', 'loading');
    }
}

function choose(el, event, key, language) {
    if (event) {
        event.preventDefault();
    }

    const xhttp = new XMLHttpRequest();
    xhttp.onload = function () {
        document.getElementById('choice').innerHTML = this.responseText;
        init();
    };
    xhttp.open('POST', '.', true);
    xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhttp.send('key=' + key + '&language=' + language + '&ajax=1');

    if (el) {
        el.disabled = true;
        el.setAttribute('class', 'loading');
    }
}
