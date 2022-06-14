<?php
function systemPage($view) {
	if ($view == 'state') {
		$html = '<h1>Stav systému</h1><ul>';
		$choiceStates = array('zatím nebylo naplánováno 🔴', 'je naplánováno ⌛', 'probíhá', 'bylo ukončeno ✅');
		$html .= '<li>přihlašování ' . (isChoiceOpen() ? 'probíhá 🟢' : 'neprobíhá, ' . $choiceStates[choiceState()]) . ' <a href="?edit=data&name=time">(upravit)</a></li>';
		$weekdays = array('neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota');
		$classes = getClasses();
		$states = array('🔴 chybí', '🟢 v pořádku');

		foreach ($classes as $class) {
			$html .= '<li>' . $class . '. třída<ul>';

			$result = sql('SELECT COUNT(*) FROM `' . prefixTable('students') . '` WHERE `class`=?', true, array($class));
			$number = isset($result[0]) && isset($result[0][0]) ? intval($result[0][0]) : 0;
			$state = $states[$number > 0];
			$html .= '<li>' . $number . ' studentů – ' . $state . ' <a href="?list=students">(upravit)</a></li>';

			$result = sql('SELECT COUNT(*) FROM `' . prefixTable('languages') . '` WHERE `class`=?', true, array($class));
			$number = isset($result[0]) && isset($result[0][0]) ? intval($result[0][0]) : 0;
			$state = $states[$number > 1];
			$html .= '<li>' . $number . ' jazyky – ' . $state . ' <a href="?list=languages">(upravit)</a></li></ul>';
		}

		$lastProcessedTime = null;

		foreach (array('from', 'to') as $fromTo) {
			$time = getDataValue('time.' . $fromTo);
			$states = array(
				'from' => array('🔴 chybí, je nutné ho doplnit, aby přihlašování mohlo začít ', '🟢 v pořádku'),
				'to' => array('🟡 není nastaven, přihlašování nebude ukončeno', '🟢 v pořádku', '🔴 čas ukončení není po čase spuštění')
			);

			if ($time) {
				$processedTime = new DateTime($time);
				$today = new DateTime('today');
				$days = $processedTime->diff($today)->days;
				$beforeAfter = $today < $processedTime ? 'bude za <abbr title="počítáno od dnešních 0:00">' . $days . ' dnů</abbr>' : 'bylo před <abbr title="počítáno od dnešních 0:00">' . $days . ' dny</abbr>';
				$html .= '<li>' . _t('time', $fromTo) . ': ' . $weekdays[$processedTime->format('w')] . ' ' . $processedTime->format('j. n. Y (G:i)') . ', což ' . $beforeAfter . ' – '
					. ($lastProcessedTime < $processedTime ? $states[$fromTo][1] : $states[$fromTo][2]);
				$lastProcessedTime = $processedTime;
			} else {
				$html .= '<li>' . _t('time', $fromTo) . ' – ' . $states[$fromTo][0];
			}

			$html .= ' <a href="?edit=data&name=time.' . $fromTo . '">(upravit)</a></li>';
		}

		$result = sql('SELECT COUNT(*) FROM `' . prefixTable('students') . '` WHERE `choice` IS NULL', true);
		$number = isset($result[0]) && isset($result[0][0]) ? intval($result[0][0]) : 0;
		$html .= $number ? '<li>ještě ' . $number . ' studentů nemá zvolený jazyk 🟡</li>' : '<li>všichni studenti mají zvolený jazyk 🟢</li>';
		// 	<li>text nahoře na webu je zadán, uživatel Jakub Novák (jakub.novak@email.cz) se spisovým číslem 123, který nastupuje z 9. třídy ho vidí takto:</li>
		// </ul>';
		// vyplněný web Jakuba Nováka odpovídající aktuální situaci
		return adminTemplate($html);
	} else if ($view == 'send') {
	} else if ($view == 'export') {
	}
}
