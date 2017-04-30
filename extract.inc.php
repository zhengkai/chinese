#! /usr/bin/env php
<?php
$l = scandir(__DIR__ . '/cache/html');

$l = array_filter($l, function ($s) {
	return strpos($s, '.') === FALSE;
});

file_put_contents(__DIR__ . '/cache/fail.txt', '', LOCK_EX);
file_put_contents(__DIR__ . '/empty_word.txt', '', LOCK_EX);

foreach ($l as $name) {
	$html = file_get_contents(__DIR__ . '/cache/html/' . $name);

	$bDefine = file_exists(__DIR__ . '/data/define/' . $name . '.php');
	$aDefine = [];
	if ($bDefine) {
		$aDefine = require __DIR__ . '/data/define/' . $name . '.php';
		if ($aDefine['replace'] ?? FALSE) {
			unset($aDefine['replace']);
			file_put_contents(__DIR__ . '/cache/json/' . $name, json_encode($aDefine) . "\n", LOCK_EX);
			continue;
		}
	}

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

	$k = str_replace(['[', ']'], '', $k);

	if (empty($k)) {
		echo $html;
		print_r($match);
		echo $name;
		exit;
	}

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

	// 拼音调整

	if ($bDefine) {
		if ($aDefine['pinyin_add'] ?? FALSE) {
			$data['pinyin'] = array_unique(array_merge($data['pinyin'], $aDefine['pinyin_add']));
		}
		if ($aDefine['pinyin_sub'] ?? FALSE) {
			foreach ($aDefine['pinyin_sub'] as $pinyin) {
				unset($data['pinyin'][array_search($pinyin, $data['pinyin'])]);
			}
		}
		$data['pinyin'] = array_values($data['pinyin']);
	}

	if (count($data['pinyin']) == 1) {

		// 单音字
		$word = demo($html);
		$lWord = [$data['pinyin'][0] => $word];

	} else {

		// 多音字
		// echo $name, "\n";

		$match = [];
		preg_match_all('#<dl>(.*?)</dl>#', $html, $match);

		$match = $match[1];

		if (count($match) != count($data['pinyin'])) {
			echo $name, "\n";
			echo 'pinyin not match ', implode(' ', $data['pinyin']), "\n";
			print_r($match);
			exit;
		}

		$lWord = [];

		foreach ($match as $line) {
			$sub = [];
			preg_match_all('#\[(.*?)\]#', $line, $sub);

			if (!$sub) {
				echo 'not match ', $name, "\n";
				print_r($match);
				print_r($data['pinyin']);
				exit;
			}

			$pinyin = $sub[1][0];

			$lWord[$pinyin] = demo($line);
		}
	}

	foreach ($lWord as $pinyin => $word) {
		if ($bDefine && !empty($aDefine['word_add'][$pinyin])) {
			$word = array_merge($word, $aDefine['word_add'][$pinyin]);
		}

		$lWord[$pinyin] = $word;
	}

	$kWord = array_keys($lWord);
	sort($kWord);
	$kPinyin = $data['pinyin'];
	sort($kPinyin);

	if ($kWord != $kPinyin) {
		echo $name, ' ', implode(',', $kWord), ' != ', implode(',', $kPinyin), "\n";
		exit;
	}

	foreach ($aDefine['word_sub'] ?? [] as $sub) {
		unset($kWord[$sub]);
		$k = array_search($sub, $data['pinyin']);
		if ($k === FALSE) {
			echo $name, "\n";
			echo 'not found ', $sub, "\n";
			exit;
		}
		unset($data['pinyin'][$k]);
		unset($lWord[$sub]);
	}

	foreach ($lWord as $pinyin => $word) {
		if (!$word) {
			echo $name, ' no word ', $pinyin, "\n";
			file_put_contents(__DIR__ . '/empty_word.txt', $name . ' ' . $pinyin . "\n", LOCK_EX | FILE_APPEND);
		}
	}

	$data['word'] = $lWord;

	echo "\n", $name, "\n";
	print_r($data);

	file_put_contents(__DIR__ . '/cache/json/' . $name, json_encode($data) . "\n", LOCK_EX);

	// print_r($match);

	// echo $html, "\n";
}

function demo($html) {
	// $list = preg_split('#(：|。|:|,|.|\s|“|”)#', $html);
	$list = preg_split('#(：|〔|〕|<|>|！|。|（|）|“|”)#', $html);
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
