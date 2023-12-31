#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$entries = [];
	foreach ($input as $line) {
		preg_match('#(.*),\s+(.*),\s+(.*)\s+@\s+(.*),\s+(.*),\s+(.*)#SADi', $line, $m);
		[$all, $pX, $pY, $pZ, $vX, $vY, $vZ] = $m;
		$entries[] = ['p' => [$pX, $pY, $pZ], 'v' => [$vX, $vY, $vZ]];
	}

	// https://stackoverflow.com/questions/563198/how-do-you-detect-where-two-line-segments-intersect/563275#563275
	function getLineIntersection($p0_x, $p0_y, $p1_x, $p1_y, $p2_x, $p2_y, $p3_x, $p3_y) {
		$s10_x = $p1_x - $p0_x;
		$s10_y = $p1_y - $p0_y;
		$s32_x = $p3_x - $p2_x;
		$s32_y = $p3_y - $p2_y;

		$denom = $s10_x * $s32_y - $s32_x * $s10_y;
		if ($denom == 0)
			return false; // Collinear
		$denomPositive = $denom > 0;

		$s02_x = $p0_x - $p2_x;
		$s02_y = $p0_y - $p2_y;
		$s_numer = $s10_x * $s02_y - $s10_y * $s02_x;
		if (($s_numer < 0) == $denomPositive)
			return false; // No collision

		$t_numer = $s32_x * $s02_y - $s32_y * $s02_x;
		if (($t_numer < 0) == $denomPositive)
			return false; // No collision

		if ((($s_numer > $denom) == $denomPositive) || (($t_numer > $denom) == $denomPositive))
			return false; // No collision
		// Collision detected
		$t = $t_numer / $denom;
		$i_x = $p0_x + ($t * $s10_x);
		$i_y = $p0_y + ($t * $s10_y);

		return [$i_x, $i_y];
}

	$testMin = isTest() ? 7 : 200000000000000;
	$testMax = isTest() ? 27 : 400000000000000;
	$part1 = 0;
	$i = 1;
	foreach ($entries as $aid => $a) {
		foreach ($entries as $bid => $b) {
			if ($bid <= $aid) { continue; }

			$vAmount = 10000000000000000000;

			$p0_x = $a['p'][0];
			$p0_y = $a['p'][1];
			$p1_x = $a['p'][0] + ($a['v'][0] * $vAmount);
			$p1_y = $a['p'][1] + ($a['v'][1] * $vAmount);

			$p2_x = $b['p'][0];
			$p2_y = $b['p'][1];
			$p3_x = $b['p'][0] + ($b['v'][0] * $vAmount);
			$p3_y = $b['p'][1] + ($b['v'][1] * $vAmount);

			$int = getLineIntersection($p0_x, $p0_y, $p1_x, $p1_y, $p2_x, $p2_y, $p3_x, $p3_y);

			if (isDebug()) {
				echo 'I: ', $i, "\n";
				$i++;
				echo 'A: ', json_encode($a), "\n";
				echo 'B: ', json_encode($b), "\n";
				echo json_encode($int);
				echo "\n";
			}

			if ($int !== FALSE) {
				if ($int[0] >= $testMin && $int[0] <= $testMax && $int[1] >= $testMin && $int[1] <= $testMax) {
					$part1++;
				}
			}
		}
	}

	echo 'Part 1: ', $part1, "\n";

	// Fuck this.
	$lines = [];
	$lines[] = '#!/usr/bin/python3';
	$lines[] = 'rocks = []';
	$count = 0;
	foreach ($entries as $e) {
		$lines[] = "rocks.append((({$e['p'][0]},{$e['p'][1]},{$e['p'][2]}),({$e['v'][0]},{$e['v'][1]},{$e['v'][2]})))";
		$count++;
		if ($count >= 3) { break; }
	}

	$lines[] = <<<FUCKTHIS
from z3 import Real, Solver

solver = Solver()
rpx = Real("rpx")
rpy = Real("rpy")
rpz = Real("rpz")
rvx = Real("rvx")
rvy = Real("rvy")
rvz = Real("rvz")

for i, ((x,y,z), (vx,vy,vz)) in enumerate(rocks):
    t = Real(f"t{i}")
    solver.add(t >= 0)
    solver.add(x + vx * t == rpx + rvx * t)
    solver.add(y + vy * t == rpy + rvy * t)
    solver.add(z + vz * t == rpz + rvz * t)
solver.check()
print(solver.model().eval(rpx + rpy + rpz))
FUCKTHIS;

	$code = implode("\n", $lines);
	$tempFile = tempnam(sys_get_temp_dir(), 'AOC23');
	file_put_contents($tempFile, $code);
	chmod($tempFile, 0700);
	$part2 = exec($tempFile);
	unlink($tempFile);

	echo 'Part 2: ', $part2, "\n";
