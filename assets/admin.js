try {
    let url = new URL(location);
    let original = url.href;
    url.searchParams.delete('success');
    url.searchParams.delete('homepage');
    let replace = url.href;
    if (original != replace) {
        history.replaceState(null, '', replace);
    }
} catch (error) { }

const abbrs = [...document.getElementsByTagName('abbr')];
abbrs.forEach(el => {
    el.tabIndex = 0;
});

function updateTime(id) {
    if (document.getElementById(id + 'd').value) {
        document.getElementById(id).value = document.getElementById(id + 'd').value + ' ' + (document.getElementById(id + 't').value || '00:00');
    } else {
        document.getElementById(id).value = '';
    }
}

function bodyInsert(el, id) {
    document.getElementById(id).value += el.textContent;
}

const textareas = [...document.getElementsByTagName('textarea')];
textareas.forEach(el => {
    el.ondragover = () => false;
    el.ondragend = () => false;
    el.ondrop = function (e) {
        var file = e.dataTransfer.files[0];
        var reader = new FileReader();
        reader.onload = function (event) {
            el.value += event.target.result;
        };
        reader.readAsText(file);
        return false;
    };
});
