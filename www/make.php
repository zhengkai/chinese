<?php
require dirname(__DIR__) . '/common.inc.php';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>语文题</title>
<link rel="stylesheet" href="style.css" type="text/css" />
</head>
<body>

<div class="box">
<?php
$l = CHAR;
shuffle($l);

$l = array_slice($l, 0, 42);

$j = [];
$i = 0;
foreach ($l as $s) {
	$i++;
	$data = file_get_contents(dirname(__DIR__) . '/cache/json/' . bin2hex($s) . '_' . $s);
	$data = json_decode($data, TRUE);

	$lWord = $data['word'];

	if (!$lWord) {
		echo $s, "\n";
		print_r($data);
		exit;
	}

	$pinyin = array_keys($lWord);
	shuffle($pinyin);
	$pinyin = current($pinyin);

	$word = $lWord[$pinyin];

	shuffle($word);
	$wordSelect = current($word);

	$ruby = '<ruby>______<rt>' . $pinyin . '</rt></ruby>';

	$a = str_replace('～', $ruby, $wordSelect);

	echo '<div>', $a, '</div>', "\n";
	if ($i % 3 == 0) {
		echo '<br style="clear: both;" />', "\n";
	}

	if ($i == 21) {
		echo '</div>', "\n";
		echo '<div class="page-break"></div>', "\n";
		echo '<div class="box">', "\n";
	}

	$j[] = [
		'char' => $s,
		'pinyin' => $pinyin,
		'word' => $wordSelect,
	];
}
?>
</div>

<div class="page-break"></div>

<div class="box" style="border-top: 1px solid black;">
<?php
foreach ($j as $a) {
	$s = str_replace('～', '<u>' . $a['char'] . '</u>', $a['word']);
	echo '<div>', $s, '</div>', "\n";
}
?>
</div>

</body>
</html>
