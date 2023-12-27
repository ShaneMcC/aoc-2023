<?php

    namespace graphviz;

    class Graph {
        private array $options = [];
        private array $nodes = [];
        private array $edges = [];

        public function __construct(array $options = []) {
            $this->options = $options;
        }

        public function setOpt(string $key, $value) {
            $this->options[$key] = $value;
        }

        public function getNodeByName(string $name, bool $create = false) {
            if ($create && !isset($this->nodes[$name])) {
                $this->nodes[$name] = new Node($name);
            }
            return $this->nodes[$name] ?? FALSE;
        }

        public function addNode(Node $node) {
            $this->nodes[$node->getName()] = $node;
        }

        public function validEdge(Edge $edge) {
            return !($edge instanceof DirectedEdge);
        }

        public function addEdge(Edge $edge) {
            $this->edges[] = $edge;
        }

        public function getGraphType() {
            return ($this->options['type'] ?? 'graph');
        }

        public function generateOutput(): string {
            $output = [];

            $graphType = $this->getGraphType();
            $strict = ($this->options['strict'] ?? false) ? 'strict ' : '';

            $output[] = "{$strict}{$graphType} {";
            foreach ($this->options as $k => $v) {
                if ($k == "strict" || $k == "type") { continue; }

                $output[] = "\"{$k}\"=\"{$v}\"";
            }
            foreach ($this->nodes as $node) {
                $o = $node->generateOutput();
                if (!empty($o)) { $output[] = $o; }
            }
            foreach ($this->edges as $edge) {
                $o = $edge->generateOutput();
                if (!empty($o)) { $output[] = $o; }
            }
            $output[] = "}";

            return implode("\n", $output);
        }

        public function generate($outputFile, $dotFile = null) {
            $unlink = false;

            if ($dotFile == null) {
                $unlink = true;
                $dotFile = tempnam(sys_get_temp_dir(), 'gviz');
            }

			file_put_contents($dotFile, $this->generateOutput());
			exec('cat ' . escapeshellarg($dotFile) . ' | dot -Tsvg > ' . escapeshellarg($outputFile));

            if ($unlink) {
                unlink($dotFile);
            }
        }
    }

    class Digraph extends Graph {
        public function getGraphType() { return 'digraph'; }
        public function validEdge(Edge $edge) { return true; }
    }

    class Node {
        private string $name;
        private array $options;

        public function __construct(String $name, array $options = []) {
            $this->name = $name;
            $this->options = $options;
        }

        public function setOpt(string $key, $value) {
            $this->options[$key] = $value;
        }

        public function getName(): string {
            return $this->name;
        }

        public function generateOutput(): string {
            if (empty($this->options)) { return ''; }

            $output = "\"{$this->name}\"";
            foreach ($this->options as $k => $v) {
                $output .= " [\"{$k}\"=\"{$v}\"]";
            }
            return $output;
        }
    }

    abstract class Edge {
        private Node $node1;
        private mixed $node2;
        private array $options;

        public function __construct(Node $node1, Node|array $node2, array $options = []) {
            $this->node1 = $node1;
            $this->node2 = $node2;
            $this->options = $options;
        }

        public function setOpt(string $key, $value) {
            $this->options[$key] = $value;
        }

        public function generateOutput(): string {
            $output = "\"{$this->node1->getName()}\"";
            $output .= ($this instanceof DirectedEdge) ? ' ->' : ' --';
            if (is_array($this->node2)) {
                $output .= " {";
                foreach ($this->node2 as $n) {
                    $output .= " \"{$n->getName()}\"";
                }
                $output .= " }";
            } else {
                $output .= " \"{$this->node2->getName()}\"";
            }
            foreach ($this->options as $k => $v) {
                $output .= " [\"{$k}\"=\"{$v}\"]";
            }
            return $output;
        }
    }

    class DirectedEdge extends Edge { }
    class UndirectedEdge extends Edge { }
