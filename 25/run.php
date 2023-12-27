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

	// Get nodes and edges.
	$nodes = [];
	$edges = [];
	foreach ($entries as $e => $conn) {
		$nodes[$e] = [];
		foreach ($conn as $c => $_) {
			$pairId = implode(' -- ', sorted('sort', [$e, $c]));
			$nodes[$e][] = $pairId;
			$edges[$pairId] = [$e, $c];
		}
	}

	function getConnected($nodes, $edges, $node) {
		$result = [];

		$check = [$node];
		while (!empty($check)) {
			$node = array_shift($check);
			if (isset($result[$node])) { continue; }
			$result[$node] = true;

			foreach ($nodes[$node] as $c) {
				$check[] = array_values(array_filter($edges[$c], fn($i) => ($i != $node)))[0];
			}
		}

		return $result;
	}

	function getGroups($nodes, $edges) {
		$groups = [];
		$groupId = 0;

		foreach (array_keys($nodes) as $n) {
			foreach ($groups as $g) {
				if (isset($g[$n])) {
					continue 2;
				}
			}

			$groups[$groupId] = getConnected($nodes, $edges, $n);
			$groupId++;
		}

		return $groups;
	}

	function breakAndTest($nodes, $edges, $set) {
		foreach ($set as $s) {
			$e = $edges[$s];
			$nodes[$e[0]] = array_filter($nodes[$e[0]], fn($i) => ($i != $s));
			$nodes[$e[1]] = array_filter($nodes[$e[1]], fn($i) => ($i != $s));
		}

		$groups = getGroups($nodes, $edges);
		if (count($groups) == 2) {
			return count($groups[0]) * count($groups[1]);
		}

		return FALSE;
	}

	function drawGraph($nodes, $edges, $labels = false) {
		require_once(dirname(__FILE__) . '/../common/graphViz.php');

		$g = new graphViz\Graph(['strict' => false, 'layout' => 'neato']);

		$drawnEdges = [];

		foreach ($nodes as $n => $es) {
			$n1 = $g->getNodeByName($n, true);
			foreach ($es as $e) {
				if (!isset($drawnEdges[$e])) {
					$n2 = $g->getNodeByName(array_values(array_filter($edges[$e], fn($i) => $i != $n))[0], true);
					$options = $labels ? ['label' => $e] : [];
					$g->addEdge(new graphViz\UndirectedEdge($n1, $n2, $options));
					$drawnEdges[$e] = true;
				}
			}
		}

		$g->generate(__DIR__ . '/graph.svg');
	}

	if ($generateGraph) {
		drawGraph($nodes, $edges);
		echo 'Look at graph.svg...', "\n";
		die();
	}

	function contract(&$nodes, &$edges, $rand, $newNode) {
		// Contract the node.
		$edge = $edges[$rand];
		[$n1, $n2] = $edge;

		$nodes[$newNode] = array_merge($nodes[$n1], $nodes[$n2]);
		$nodes[$newNode] = array_unique(array_values(array_filter($nodes[$newNode], fn($i) => $i != $rand)));

		unset($edges[$rand]);
		unset($nodes[$n1]);
		unset($nodes[$n2]);

		$removeEdges = [];
		foreach ($nodes[$newNode] as $e) {
			$edges[$e] = array_values(array_filter($edges[$e], fn($i) => ($i != $n1 && $i != $n2)));
			if (empty($edges[$e])) {
				$removeEdges[] = $e;
			} else {
				$edges[$e][] = $newNode;
			}
		}

		foreach ($removeEdges as $e) {
			unset($edges[$e]);
			$nodes[$newNode] = array_values(array_filter($nodes[$newNode], fn($i) => $i != $e));
		}
	}

	function kargers($nodes, $edges) {
		$i = 0;

		while (count($nodes) != 2) {
			$rand = array_rand($edges);
			contract($nodes, $edges, $rand, "new{$i}");
			$i++;
		}

		return [$nodes, $edges];
	}

	$part1 = 0;
	$attempt = 1;
	while (true) {
		if (isDebug()) { echo $attempt++, "\n"; }
		[$testNodes, $testEdges] = kargers($nodes, $edges);

		if (count($testEdges) == 3) {
			$part1 = breakAndTest($nodes, $edges, array_keys($testEdges));
			break;
		}
	}

	echo 'Part 1: ', $part1, "\n";
