#! /usr/bin/env php
<?php
$l = scandir(__DIR__ . '/cache/html');

$l = array_filter($l, function ($s) {
	return strpos($s, '.') === FALSE;
});

file_put_contents(__DIR__ . '/cache/fail.txt', '', LOCK_EX);

foreach ($l as $name) {
	$html = file_get_contents(__DIR__ . '/cache/html/' . $name);

	echo $name, "\n";

	$data = [
		'pinyin' => [],
		'word' => [],
	];

	// 拼音

	$match = [];
	preg_match_all('#<div class="pronounce" id="pinyin">(.*?)</div>#', $html, $match);
	if (!$match) {
		echo 'fail in ', $name;
		exit;
	}

	$k = $match[1][0];

	$match = [];
	preg_match_all('#<b>(.*?)</b>#', $k, $match);

	$data['pinyin'] = $match[1];

	// 释义

	$start = strpos($html, 'basicmean-wrapper');
	if (!$start) {
		echo 'error start ', $name, "\n";
		file_put_contents(__DIR__ . '/cache/fail.txt', $name . "\n", LOCK_EX | FILE_APPEND);
		continue;
	}
	$html = substr($html, $start);

	$end = strpos($html, 'more-button');
	if (!$end) {
		echo 'error end ', $name, "\n";
		file_put_contents(__DIR__ . '/cache/fail.txt', $name . "\n", LOCK_EX | FILE_APPEND);
		continue;
	}
	$html = substr($html, 0, $end);

	if (count($data['pinyin']) == 1) {
		$data['word'] = [$data['pinyin'][0] => demo($html)];
		file_put_contents(__DIR__ . '/cache/json/' . $name, json_encode($data), LOCK_EX);
	}
	continue;

	$match = [];
	preg_match_all('#<dl>(.*?)</dl>#', $html, $match);

	print_r($match);

	// echo $html, "\n";
	echo "\n";
}

function demo($html) {
	// $list = preg_split('#(：|。|:|,|.|\s|“|”)#', $html);
	$list = preg_split('#(：|<|>|！|。|（|）|“|”)#', $html);
	$list = array_filter($list, function ($s) {
		if ($s === '～') {
			return FALSE;
		}
		if (strlen($s) > 21) {
			return FALSE;
		}
		return strpos($s, '～') !== FALSE;
	});
	return array_values($list);
}
