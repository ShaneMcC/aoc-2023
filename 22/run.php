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

		$bricks[$id] = ['location' => [intval($x1), intval($y1), intval($z1), intval($x2), intval($y2), intval($z2)], 'supports' => [], 'supportedBy' => []];
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

	function hasSupport(&$map, &$bricks, $id) {
		if ($id == 'floor') { return true; }
		[$x1, $y1, $z1, $x2, $y2, $z2] = $bricks[$id]['location'];
		$z = min($z1, $z2);
		if ($z == 1) { return true; }

		for ($y = $y1; $y <= $y2; $y++) {
			for ($x = $x1; $x <= $x2; $x++) {
				if (isset($map[$z - 1][$y][$x])) {
					return true;
				}
			}
		}

		return false;
	}

	function supportedBy(&$map, &$bricks, $id) {
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
		$moved = false;
		do {
			$moved = false;
			foreach (array_keys($bricks) as $id) {
				if (!hasSupport($map, $bricks, $id)) {
					$moved = true;

					// Remove brick from map
					removeBrick($map, $bricks, $id);

					// Move brick down 1
					$bricks[$id]['location'][2] -= 1;
					$bricks[$id]['location'][5] -= 1;

					// Add brick back
					addBrick($map, $bricks, $id);
				}
			}
		} while ($moved);

		updateSupports($map, $bricks);
	}

	// Settle all the bricks
	fall($map, $bricks);

	$part1 = 0;
	$disintegrateBlocks = [];
	foreach (array_keys($bricks) as $id) {
		if (isDebug()) {
			echo 'Brick ', $id, ' - ', json_encode($bricks[$id]), "\n";
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
	foreach (array_keys($bricks) as $testId) {
		if (isset($disintegrateBlocks[$testId])) { continue; }

		$fallCount = 0;
		$testBricks = $bricks;
		unset($testBricks[$testId]);

		do {
			$fallen = false;
			foreach (array_keys($testBricks) as $id) {
				$remainingSupports = 0;
				foreach ($testBricks[$id]['supportedBy'] as $s) {
					if (isset($testBricks[$s]) || $s == 'floor') { $remainingSupports++; }
				}

				if ($remainingSupports == 0) {
					unset($testBricks[$id]);
					$fallCount++;
					$fallen = true;
				}
			}
		} while ($fallen);

		$part2 += $fallCount;
		if (isDebug()) {
			echo 'Removing brick ', $testId, ' causes ', $fallCount, ' bricks to fall.', "\n";
		}
	}

	echo 'Part 2: ', $part2, "\n";
