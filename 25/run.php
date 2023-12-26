#!/usr/bin/php
<?php
	$__CLI['long'] = ['graph'];
	$__CLI['extrahelp'][] = '      --graph              Output graph.';

	require_once(dirname(__FILE__) . '/../common/common.php');
	$generateGraph = isset($__CLIOPTS['graph']);
	$input = getInputLines();

	$entries = [];
	foreach ($input as $line) {
		preg_match('#(.*): (.*)#SADi', $line, $m);
		[$all, $component, $connections] = $m;
		$entries[$component] = [];
		foreach (explode(" ", $connections) as $c) {
			$entries[$component][$c] = true;
		}
	}

	// Add reverse connections
	foreach (array_keys($entries) as $cmp) {
		foreach (array_keys($entries[$cmp]) as $conn) {
			if (!isset($entries[$conn])) { $entries[$conn] = []; }
			if (!isset($entries[$conn][$cmp])) {
				$entries[$conn][$cmp] = true;
			}
		}
	}

	// Get Pairs
	$pairs = [];
	foreach ($entries as $w => $conns) {
		foreach ($conns as $c => $_) {
			$pairId = $w . ' -- ' . $c;
			$altPairId = $c . ' -- ' . $w;

			if (isset($pairs[$pairId]) || isset($pairs[$altPairId])) {
				continue;
			}

			$pairs[$pairId] = [$w, $c];
		}
	}

	function getWires($wires, $cmp) {
		$result = [];

		$check = [$cmp];
		while (!empty($check)) {
			$cmp = array_shift($check);
			if (isset($result[$cmp])) { continue; }
			$result[$cmp] = true;

			foreach (array_keys($wires[$cmp]) as $c) {
				$check[] = $c;
			}
		}

		return $result;
	}

	function getGroups($wires, $max) {
		$groups = [];
		$groupId = 0;

		foreach (array_keys($wires) as $w) {
			foreach ($groups as $g) {
				if (isset($g[$w])) {
					continue 2;
				}
			}

			$groups[$groupId] = getWires($wires, $w);
			$groupId++;

			if ($groupId > $max) { return []; }
		}

		return $groups;
	}

	function getSets($pairs) {
		foreach (array_keys($pairs) as $k1 => $p1) {
			foreach (array_keys($pairs) as $k2 => $p2) {
				if ($k2 <= $k1) { continue; }
				foreach (array_keys($pairs) as $k3 => $p3) {
					if ($k3 <= $k2) { continue; }

					yield([$p1, $p2, $p3]);
				}
			}
		}
	}

	function breakAndTest($entries, $pairs, $set) {
		$p1 = $pairs[$set[0]];
		$p2 = $pairs[$set[1]];
		$p3 = $pairs[$set[2]];

		$test = $entries;
		unset($test[$p1[0]][$p1[1]]);
		unset($test[$p1[1]][$p1[0]]);

		unset($test[$p2[0]][$p2[1]]);
		unset($test[$p2[1]][$p2[0]]);

		unset($test[$p3[0]][$p3[1]]);
		unset($test[$p3[1]][$p3[0]]);

		$groups = getGroups($test, 2);
		if (count($groups) == 2) {
			return count($groups[0]) * count($groups[1]);
		}

		return FALSE;
	}

	if ($generateGraph) {
		require_once(dirname(__FILE__) . '/../common/graphViz.php');

		$g = new graphViz\Graph(['strict' => true, 'layout' => 'neato']);

		foreach ($entries as $l => $conn) {
			$n1 = $g->getNodeByName($l, true);
			$edges = [];
			foreach (array_keys($conn) as $c) {
				$edges[] = $g->getNodeByName($c, true);
			}
			$g->addEdge(new graphViz\UndirectedEdge($n1, $edges));
		}

		$g->generate(__DIR__ . '/graph.svg');
		echo 'Look at graph.svg...', "\n";
		die();
	}

	$part1 = 0;
	if (isTest()) {
		foreach (getSets($pairs) as $set) {
			$check = breakAndTest($entries, $pairs, $set);
			if ($check !== false) {
				$part1 = $check;
				break;
			}
		}
	} else {
		$set = ['dfk -- nxk', 'hcf -- lhn', 'ldl -- fpg'];
		$part1 = breakAndTest($entries, $pairs, $set);
	}

	echo 'Part 1: ', $part1, "\n";
