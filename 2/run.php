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

	function checkValidGames($games, $red, $green, $blue) {
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

	function checkMinimumGames($games) {
		$gamePowers = [];
		foreach ($games as $gameId => $allBalls) {
			$minRed = 0;
			$minBlue = 0;
			$minGreen = 0;
			foreach ($allBalls as $balls) {
				$minRed = max(($balls['red'] ?? 0), $minRed);
				$minGreen = max(($balls['green'] ?? 0), $minGreen);
				$minBlue = max(($balls['blue'] ?? 0), $minBlue);
			}
			$power = $minRed * $minBlue * $minGreen;

			$gamePowers[] = $power;
		}

		return $gamePowers;
	}

	$part1 = checkValidGames($entries, 12, 13, 14);
	echo 'Part 1: ', array_sum($part1), "\n";

	$part2 = checkMinimumGames($entries);
	echo 'Part 2: ', array_sum($part2), "\n";
