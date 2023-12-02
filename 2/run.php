#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$games = [];
	foreach ($input as $line) {
		preg_match('#Game (.*): (.*)#SADi', $line, $m);
		[$all, $gameId, $rounds] = $m;

		$gameBalls = [];
		$rounds = explode(';', $rounds);
		foreach ($rounds as $round) {
			$roundBalls = [];
			if (preg_match_all('/([0-9]+) (red|blue|green)/', $round, $m)) {
				for ($i = 0; $i < count($m[0]); $i++) {
					$roundBalls[$m[2][$i]] = $m[1][$i];
				}
			}
			$gameBalls[] = $roundBalls;
		}

		$games[$gameId] = $gameBalls;
	}

	$colours = ['red', 'green', 'blue'];
	$allowedValues = ['red' => 12, 'green' => 13, 'blue' => 14];
	$validGames = [];
	$gamePowers = [];
	foreach ($games as $gameId => $gameBalls) {
		$valid = true;
		$min = [];
		foreach ($colours as $colour) {
			foreach ($gameBalls as $roundBalls) {
				$min[$colour] = max(($roundBalls[$colour] ?? 0), ($min[$colour] ?? 0));
			}
			if ($valid && ($min[$colour] ?? 0) > ($allowedValues[$colour] ?? 0)) {
				$valid = false;
			}
		}
		if ($valid) { $validGames[] = $gameId; }
		$gamePowers[] = array_product($min);
	}

	echo 'Part 1: ', array_sum($validGames), "\n";
	echo 'Part 2: ', array_sum($gamePowers), "\n";
