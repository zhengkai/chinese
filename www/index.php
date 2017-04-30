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

<?php
foreach (CHAR as $s) {
	$data = file_get_contents(dirname(__DIR__) . '/cache/json/' . bin2hex($s) . '_' . $s);
	$data = json_decode($data, TRUE);
	echo '<p class="lead">', $s, '</p>';
	foreach($data['word'] as $pinyin => $word) {
		echo '<p>', $pinyin, ' ', implode('，', $word), '</p>';
	}
}
?>

</body>
</html>
