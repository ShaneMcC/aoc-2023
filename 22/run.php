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

		$bricks[$id] = ['location' => [intval($x1), intval($y1), intval($z1), intval($x2), intval($y2), intval($z2)], 'supports' => [], 'supportedBy' => [], 'fallen' => false];
		addBrick($map, $bricks, $id);
		$id++;
	}
	updateSupports($map, $bricks);

	function removeBrick(&$map, &$bricks, $id) {
		[$x1, $y1, $z1, $x2, $y2, $z2] = $bricks[$id]['location'];

		for ($z = $z1; $z <= $z2; $z++) {
			for ($y = $y1; $y <= $y2; $y++) {
				for ($x = $x1; $x <= $x2; $x++) {
					unset($map[$z][$y][$x]);
				}
			}
		}
	}

	function addBrick(&$map, &$bricks, $id) {
		[$x1, $y1, $z1, $x2, $y2, $z2] = $bricks[$id]['location'];

		for ($z = $z1; $z <= $z2; $z++) {
			for ($y = $y1; $y <= $y2; $y++) {
				for ($x = $x1; $x <= $x2; $x++) {
					$map[$z][$y][$x] = $id;
				}
			}
		}
	}

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

	function updateSupports(&$map, &$bricks) {
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
	}

	function fall(&$map, &$bricks) {
		foreach (array_keys($bricks) as $id) { $bricks[$id]['fallen'] = false; }

		$moved = false;
		do {
			$moved = false;
			foreach (array_keys($bricks) as $id) {
				[$x1, $y1, $z1, $x2, $y2, $z2] = $bricks[$id]['location'];

				$supportedBy = supportedBy($map, $bricks, $id);

				if (empty($supportedBy)) {
					$moved = true;

					// Remove brick
					removeBrick($map, $bricks, $id);

					// Update bricks array
					$bricks[$id]['location'] = [$x1, $y1, $z1 - 1, $x2, $y2, $z2 - 1];
					$bricks[$id]['fallen'] = true;

					// Add brick back
					addBrick($map, $bricks, $id);
				}
			}
		} while ($moved);

		updateSupports($map, $bricks);
		return [$map, $bricks];
	}

	// Settle all the bricks
	fall($map, $bricks);

	$part1 = 0;
	$disintegrateBlocks = [];
	foreach (array_keys($bricks) as $id) {
		if (isDebug()) {
			echo 'Brick ', $id;
			echo ' - ', json_encode($bricks[$id]);
			echo "\n";
			if (empty($bricks[$id]['supports'])) {
				echo "\t", 'supports nothing.', "\n";
			}
		}
		$canDisintegrate = true;
		foreach ($bricks[$id]['supports'] as $s) {
			if (count($bricks[$s]['supportedBy']) == 1) {
				$canDisintegrate = false;
				if (isDebug()) {
					echo "\t", 'is the only support for ', $s, "\n";
				}
			} else if (isDebug()) {
				echo "\t", 'helps support ', $s, ' with ', implode(', ', $bricks[$s]['supportedBy']), "\n";
			}
		}

		if ($canDisintegrate) {
			$disintegrateBlocks[$id] = true;
			if (isDebug()) { echo "\t", '- Disintegrate Safely.', "\n"; }
		}
	}

	echo 'Part 1: ', count($disintegrateBlocks), "\n";

	$part2 = 0;
	foreach (array_keys($bricks) as $id) {
		if (isset($disintegrateBlocks[$id])) { continue; }

		if (isDebug()) { echo 'Removing brick ', $id; }
		$testMap = $map;
		$testBricks = $bricks;
		removeBrick($testMap, $testBricks, $id);
		fall($testMap, $testBricks);

		$fallCount = 0;
		foreach ($testBricks as $b) {
			if ($b['fallen']) { $fallCount++; }
		}

		$part2 += $fallCount;
		if (isDebug()) { echo ' causes ', $fallCount, ' bricks to fall.', "\n"; }
	}

	echo 'Part 2: ', $part2, "\n";
