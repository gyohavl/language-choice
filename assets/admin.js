try {
	let url = new URL(location);
	let original = url.href;
	url.searchParams.delete('success');
	let replace = url.href;
	if (original != replace) {
		history.replaceState(null, '', replace);
	}
} catch (error) { }
