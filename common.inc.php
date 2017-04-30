<?php
(function() {
	$s = require __DIR__ . '/data.inc.php';
	$l = str_split($s, 3);
	define('CHAR', array_values(array_unique($l)));
})();
