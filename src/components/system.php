<?php
function systemPage($view) {
	if ($view == 'state') {
		$html = '<h1>Stav systému</h1><ul>
			<li>přihlašování probíhá / neprobíhá, je naplánováno / je ukončeno / zatím nebylo naplánováno</li>
			<li>n studentů – v pořádku <a href="?list=students">(upravit)</a></li>
			<li>n jazyků pro 5. třídu, m jazyků pro 9. třídu – v pořádku <a href="?list=languages">(upravit)</a></li>
			<li>čas spuštění – pondělí 5. 8. 2022 (8:00), v pořádku / chybí, je nutné ho doplnit, aby přihlašování mohlo začít <a href="?edit=data&name=time.from">(upravit)</a></li>
			<li>čas ukončení – pondělí 5. 8. 2022 (8:00), v pořádku / není nastaven, přihlašování nebude ukončeno <a href="?edit=data&name=time.to">(upravit)</a></li>
			<li>text nahoře na webu je zadán, uživatel Jakub Novák (jakub.novak@email.cz) se spisovým číslem 123, který nastupuje z 9. třídy ho vidí takto:</li>
		</ul>';
		// vyplněný web Jakuba Nováka odpovídající aktuální situaci
		return adminTemplate($html);
	} else if ($view == 'send') {

	} else if ($view == 'export') {
		
	}
}
