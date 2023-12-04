#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$games = [];
	$copies = [];
	foreach ($input as $line) {
		preg_match('#Card (.*): (.*) \| (.*)#SADi', $line, $m);
		[$all, $gameId, $round1, $round2] = $m;

		preg_match_all('/(\d+)/', $round1, $m1);
		preg_match_all('/(\d+)/', $round2, $m2);

		$gameId = trim($gameId);
		$games[$gameId] = ['winning' => $m1[1], 'mine' => $m2[1]];
		$copies[$gameId] = 1;
	}

	$part1 = 0;
	foreach ($games as $gameId => $numbers) {
		$value = 0;
		$winning = 0;
		foreach ($numbers['mine'] as $num) {
			if (in_array($num, $numbers['winning'])) {
				if ($value == 0) { $value = 1; }
				else { $value += $value; }
				$winning++;
			}
		}
		$part1 += $value;

		debugOut("{$gameId} had {$winning} matching numbers ($copies[$gameId] copies).\n");
		for ($i = 0; $i < $winning; $i++) {
			$newId = $gameId + 1 + $i;
			if (isset($copies[$newId])) {
				debugOut("\tAdded {$copies[$gameId]} copies of {$newId} from {$gameId}\n");
				$copies[$newId] += $copies[$gameId];
			}
		}
	}

	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', array_sum($copies), "\n";
