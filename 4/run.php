#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$games = [];
	foreach ($input as $line) {
		preg_match('#Card (.*): (.*) \| (.*)#SADi', $line, $m);
		[$all, $gameId, $round1, $round2] = $m;

		preg_match_all('/(\d+)/', $round1, $m1);
		preg_match_all('/(\d+)/', $round2, $m2);

		$games[$gameId] = ['winning' => $m1[1], 'mine' => $m2[1]];
	}

	$part1 = 0;
	foreach ($games as $gameId => $numbers) {
		$value = 0;
		foreach ($numbers['mine'] as $num) {
			if (in_array($num, $numbers['winning'])) {
				if ($value == 0) { $value = 1; }
				else { $value += $value; }
			}
		}
		$part1 += $value;
	}

	echo 'Part 1: ', $part1, "\n";
//	echo 'Part 2: ', $part2, "\n";
