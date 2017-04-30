#! /usr/bin/env php
<?php
$list = require __DIR__ . '/data.inc.php';

$url_tpl = 'http://dict.baidu.com/s?wd=';
for ($i = 0, $j = strlen($list) / 3; $i < $j; $i += 3) {
	$s = substr($list, $i, 3);
	$url = $url_tpl . urlencode($s);
	echo $url, "\n";

	$file = __DIR__ . '/cache/html/' . bin2hex($s) . '_' . $s;

	if (file_exists($file) && filesize($file) > 1) {
		continue;
	}

	echo 'get url ', $url, "\n";
	$html = file_get_contents($url);

	echo 'write file ', $file, "\n";
	file_put_contents($file, $html);

	sleep(1);
}
