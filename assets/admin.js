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
	document.getElementById(id).value = (document.getElementById(id + 'd').value || '2050-01-01') + ' ' + (document.getElementById(id + 't').value || '00:00');
}
