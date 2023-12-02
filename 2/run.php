#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$entries = [];
	foreach ($input as $line) {
		preg_match('#Game (.*): (.*)#SADi', $line, $m);
		[$all, $gameId, $results] = $m;

		$balls = [];
		$bits = explode(';', $results);
		foreach ($bits as $bit) {
			$attemptBalls = [];
			if (preg_match_all('/([0-9]+) (red|blue|green)/', $bit, $m)) {
				for ($i = 0; $i < count($m[0]); $i++) {
					$attemptBalls[$m[2][$i]] = $m[1][$i];
				}
			}
			$balls[] = $attemptBalls;
		}

		$entries[$gameId] = $balls;
	}

	function checkGames($games, $red, $green, $blue) {
		$validGames = [];
		foreach ($games as $gameId => $allBalls) {
			$valid = true;
			foreach ($allBalls as $balls) {
				if (($balls['red'] ?? 0) > $red || ($balls['green'] ?? 0) > $green || ($balls['blue'] ?? 0) > $blue) {
					$valid = false;
					break;
				}
			}
			if ($valid) {
				$validGames[] = $gameId;
			}
		}

		return $validGames;
	}

	$part1 = checkGames($entries, 12, 13, 14);
	echo 'Part 1: ', array_sum($part1), "\n";

	// $part2 = -1;
	// echo 'Part 2: ', $part2, "\n";
