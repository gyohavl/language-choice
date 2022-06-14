try {
	let url = new URL(location);
	let original = url.href;
	url.searchParams.delete('success');
	let replace = url.href;
	if (original != replace) {
		history.replaceState(null, '', replace);
	}
} catch (error) { }

function updateTime(id) {
	if (document.getElementById(id + 'd').value) {
		document.getElementById(id).value = document.getElementById(id + 'd').value + ' ' + (document.getElementById(id + 't').value || '00:00');
	} else {
		document.getElementById(id).value = '';
	}
}

function bodyInsert(el) {
	document.getElementById('text.email_body').value += el.textContent;
}
