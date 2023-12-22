#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$map = [];
	$id = 1;
	$bricks = [];

	foreach ($input as $line) {
		preg_match('#(.*),(.*),(.*)~(.*),(.*),(.*)#SADi', $line, $m);
		[$all, $x1, $y1, $z1, $x2, $y2, $z2] = $m;
		$diff = [$x2 - $x1, $y2 - $y1, $z2 - $z1];

		for ($z = $z1; $z <= $z2; $z++) {
			for ($y = $y1; $y <= $y2; $y++) {
				for ($x = $x1; $x <= $x2; $x++) {
					$map[$z][$y][$x] = $id;
				}
			}
		}

		$bricks[$id] = ['location' => [intval($x1), intval($y1), intval($z1), intval($x2), intval($y2), intval($z2)], 'supports' => [], 'supportedBy' => []];
		$id++;
	}
	$bricks = updateSupports($map, $bricks);

	function supportedBy($map, $bricks, $id) {
		if ($id == 'floor') { return ['floor']; }

		[$x1, $y1, $z1, $x2, $y2, $z2] = $bricks[$id]['location'];

		$z = min($z1, $z2);

		$supportedBy = [];

		if ($z == 1) { $supportedBy[] = 'floor'; }

		for ($y = $y1; $y <= $y2; $y++) {
			for ($x = $x1; $x <= $x2; $x++) {
				if (isset($map[$z - 1][$y][$x])) {
					$supportedBy[] = $map[$z - 1][$y][$x];
				}
			}
		}

		return array_unique($supportedBy);
	}

	function updateSupports($map, $bricks) {
		unset($bricks['floor']);

		foreach (array_keys($bricks) as $id) {
			$bricks[$id]['supports'] = [];
			$bricks[$id]['supportedBy'] = [];
		}

		// Check the supports
		foreach (array_keys($bricks) as $id) {
			$bricks[$id]['supportedBy'] = supportedBy($map, $bricks, $id);

			foreach ($bricks[$id]['supportedBy'] as $s) {
				$bricks[$s]['supports'][] = $id;
			}
		}

		unset($bricks['floor']);
		return $bricks;
	}

	function fall($map, $bricks) {

		$moved = false;
		do {
			$moved = false;
			foreach (array_keys($bricks) as $id) {
				[$x1, $y1, $z1, $x2, $y2, $z2] = $bricks[$id]['location'];

				$z = min($z1, $z2);

				$supportedBy = supportedBy($map, $bricks, $id);

				if (empty($supportedBy)) {
					$moved = true;

					// Remove brick
					for ($z = $z1; $z <= $z2; $z++) {
						for ($y = $y1; $y <= $y2; $y++) {
							for ($x = $x1; $x <= $x2; $x++) {
								unset($map[$z][$y][$x]);
							}
						}
					}

					// Add brick back, 1 lower.
					for ($z = $z1; $z <= $z2; $z++) {
						for ($y = $y1; $y <= $y2; $y++) {
							for ($x = $x1; $x <= $x2; $x++) {
								$map[$z - 1][$y][$x] = $id;
							}
						}
					}

					// Update bricks array
					$bricks[$id]['location'] = [$x1, $y1, $z1 - 1, $x2, $y2, $z2 - 1];
				}
			}
		} while ($moved);

		$bricks = updateSupports($map, $bricks);
		return [$map, $bricks];
	}

	// Settle all the bricks
	[$settledMap, $settledBricks] = fall($map, $bricks);

	$part1 = 0;
	foreach (array_keys($settledBricks) as $id) {
		if (isDebug()) {
			echo 'Brick ', $id;
			echo ' - ', json_encode($settledBricks[$id]);
			echo "\n";
			if (empty($settledBricks[$id]['supports'])) {
				echo "\t", 'supports nothing.', "\n";
			}
		}
		$canDisintegrate = true;
		foreach ($settledBricks[$id]['supports'] as $s) {
			if (count($settledBricks[$s]['supportedBy']) == 1) {
				$canDisintegrate = false;
				if (isDebug()) {
					echo "\t", 'is the only support for ', $s, "\n";
				}
			} else if (isDebug()) {
				echo "\t", 'helps support ', $s, ' with ', implode(', ', $settledBricks[$s]['supportedBy']), "\n";
			}
		}

		if ($canDisintegrate) {
			if (isDebug()) { echo "\t", '- Disintegrate Safely.', "\n"; }
			$part1++;
		}
	}

	echo 'Part 1: ', $part1, "\n";

	// $part2 = 0;
	// echo 'Part 2: ', $part2, "\n";
