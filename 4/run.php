#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$games = [];
	$copies = [];
	foreach ($input as $line) {
		preg_match('#Card\s+(.*): (.*) \| (.*)#SADi', $line, $m);
		[$all, $gameId, $winning, $mine] = $m;

		preg_match_all('/(\d+)/', $winning, $m1);
		preg_match_all('/(\d+)/', $mine, $m2);

		$games[$gameId] = ['winning' => $m1[1], 'mine' => $m2[1], 'copies' => 1];
	}

	$part1 = 0;
	foreach (array_keys($games) as $gameId) {
		$gameInfo = $games[$gameId];
		$value = 0;
		$winning = 0;
		foreach ($gameInfo['mine'] as $num) {
			if (in_array($num, $gameInfo['winning'])) {
				if ($value == 0) { $value = 1; }
				else { $value += $value; }
				$winning++;
			}
		}
		$part1 += $value;

		debugOut("{$gameId} had {$winning} matching numbers ({$gameInfo['copies']} copies).\n");
		for ($newId = ($gameId + 1); $newId <= ($gameId + $winning); $newId++) {
			if (isset($games[$newId])) {
				debugOut("\tAdded {$gameInfo['copies']} copies of {$newId} from {$gameId}\n");
				$games[$newId]['copies'] += $gameInfo['copies'];
			}
		}
	}

	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', array_sum(array_column($games, 'copies')), "\n";
