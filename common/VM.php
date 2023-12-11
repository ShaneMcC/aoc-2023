<?php

	/**
	 * Simple VM
	 */
	class VM {
		/** @var int Current location. */
		protected $location = -1;

		/** @var callable[] Known Instructions. */
		protected $instrs = array();

		/** @var array Data to execute. */
		protected $data = array();

		/** @var array Read ahead optimisations */
		protected $readAheads = array();

		/** @var int Our exit code. */
		protected $exitCode = 0;

		/** @var bool Have we exited? */
		protected $exited = false;

		/** @var string Output from the VM. */
		protected $output = '';

		/** @var array VM Misc Data. */
		protected $miscData = [];

		/** @var bool Is debug mode enabled? */
		protected $debug = false;

		/** @var int Sleep time between debug output. */
		protected $sleep = 25000;

		/**
		 * Create a new VM.
		 *
		 * @param $data (Optional) Program execution data.
		 */
		function __construct($data = array()) {
			$this->init();
			$this->loadProgram($data);
		}

		/**
		 * Load in a new program and reset the VM State.
		 *
		 * @param $data Data to load.
		 */
		function loadProgram($data) {
			$this->data = $data;
			$this->reset();
		}

		/**
		 * Reset the VM.
		 */
		function reset() {
			$this->exitCode = 0;
			$this->location = -1;
			$this->clearOutput();
		}

		/**
		 * End program execution.
		 *
		 * This sets the location to beyond the data range, effectively
		 * stopping execution.
		 *
		 * @param $exitCode Set the exit code.
		 */
		function end($exitCode = 0) {
			$this->exited = true;
			$this->exitCode = $exitCode;
		}

		/**
		 * Get the vm exit code.
		 *
		 * @return int The program exit code.
		 */
		function exitCode(): int {
			return $this->exitCode;
		}

		/**
		 * Has this VM exited?
		 *
		 * @return bool True if we have exited.
		 */
		function hasExited(): bool {
			return $this->exited;
		}

		/**
		 * Clear stored output.
		 */
		public function clearOutput() {
			$this->output = '';
		}

		/**
		 * Get stored output.
		 *
		 * @return string The stored output.
		 */
		public function getOutput(): string {
			return $this->output;
		}

		/**
		 * Get the length of the stored output.
		 *
		 * @return int The length of the stored output.
		 */
		public function getOutputLength(): int {
			return strlen($this->output);
		}

		/**
		 * Append data to the output.
		 *
		 * @param $str String to append to output.
		 */
		public function appendOutput($str) {
			$this->output .= $str;
		}

		/**
		 * Set the output data.
		 *
		 * @param $str String to set output as.
		 */
		public function setOutput($str) {
			$this->output = $str;
		}

		/**
		 * Get MISC VM Data.
		 *
		 * @param $data Data type
		 * @return null|string Data value or NULL
		 */
		public function getMiscData($data): null|string {
			return isset($this->miscData[$data]) ? $this->miscData[$data] : null;
		}

		/**
		 * Set MISC VM Data.
		 *
		 * @param $data Data type
		 * @param $value Data value
		 * @return VM $this of this VM.
		 */
		public function setMiscData($data, $value): VM {
			$this->miscData[$data] = $value;
			return $this;
		}

		/**
		 * Set the value of debugging.
		 *
		 * @param $debug New value for debugging.
		 * @param $sleep (Default: 25000) Time between debug output lines (NULL not to change)
		 */
		public function setDebug($debug, $sleep = NULL) {
			$this->debug = $debug;
			if ($sleep !== NULL) {
				$this->sleep = $sleep;
			}
		}

		/**
		 * Get the instruction function by the given name.
		 *
		 * @param $instr Instruction name.
		 * @return callable Instruction function.
		 * @throws NoSuchInstrException if the instruction does not exist
		 */
		public function getInstr($instr): callable {
			if (isset($this->instrs[$instr])) { return $this->instrs[$instr]; }
			throw new NoSuchInstrException('Unknown Instr: ' . $instr);
		}

		/**
		 * Set the instruction by the given name to the given function.
		 *
		 * @param $instr Instruction name.
		 * @param $function New function.
		 */
		public function setInstr($instr, $function) {
			$this->instrs[$instr] = $function;
		}

		/**
		 * Check for data at the given location.
		 *
		 * @param $location Data location.
		 * @return bool True if there is data at the location.
		 */
		public function hasData($loc): bool {
			return isset($this->data[$loc]);
		}

		/**
		 * Get the data at the given location.
		 *
		 * @param $location Data location (or NULL for current).
		 * @return string Data from location.
		 * @throws BadDataLocationException If there is no such location
		 */
		public function getData($loc = null): string {
			if ($loc === null) { $loc = $this->getLocation(); }
			if (isset($this->data[$loc])) { return $this->data[$loc]; }
			throw new BadDataLocationException('Unknown Data Location: ' . $loc);
		}

		/**
		 * Set the data at the given location.
		 *
		 * @param $location Data location (or NULL for current).
		 * @param $val New Value
		 * @throws BadDataLocationException If there is no such location
		 */
		public function setData($loc, $val) {
			if ($loc === null) { $loc = $this->getLocation(); }
			if (isset($this->data[$loc])) {
				$this->data[$loc] = $val;
			} else {
				throw new BadDataLocationException('Unknown Data Location: ' . $loc);
			}
		}

		/**
		 * Init the Instructions.
		 */
		protected function init() { }

		/**
		 * Get the current execution location.
		 *
		 * @return int Location of current execution.
		 */
		function getLocation(): int {
			return $this->location;
		}

		/**
		 * Get the next execution location.
		 *
		 * @return int Location of next execution.
		 */
		function getNextLocation(): int {
			return $this->location + 1;
		}

		/**
		 * Jump to specific location.
		 *
		 * @param $loc Location to jump to.
		 */
		function jump($loc) {
			// We do -1 here becuase step() will do + 1 immediately so this
			// will put us in the right location.
			$this->location = $loc - 1;
		}

		/**
		 * Step a single instruction.
		 *
		 * @return bool True if we executed something, else false if we have no more
		 *         to execute.
		 */
		function step(): bool {
			$startLocation = $this->location;
			if (!$this->exited && isset($this->data[$this->location + 1])) {
				$this->location++;

				if (!empty($this->readAheads)) {
					$optimise = $this->doReadAheads();
					// If we optimised, assume we did something, and then we'll
					// continue in he next step
					if ($optimise !== false) {
						// -1 because step() does ++
						$this->location = $optimise - 1;
						return TRUE;
					}
				}

				try {
					return $this->doStep();
				} catch (Throwable $ex) {
					// Reset Location Pointer if this an exception rather than
					// just an interrupt.
					if (!($ex instanceof VMInterrupt)) {
						$this->location = $startLocation;
					}

					// Rethrow the error.
					throw $ex;
				}
			} else {
				return FALSE;
			}
		}

		/**
		 * Actually do what we need to for this step.
		 *
		 * @return true Returns true.
		 */
		function doStep(): true {
			$next = $this->data[$this->location];
			if ($this->debug) {
				if (isset($this->miscData['pid'])) {
					echo sprintf('[PID: %2s] ', $this->miscData['pid']);
				}
				$out = '';
				$out .= sprintf('(%4s) %-20s', $this->location, static::instrToString($next));
			}
			list($instr, $data) = $next;
			$ins = $this->getInstr($instr);
			$ret = $ins($this, $data);

			if ($this->debug) {
				$out .= ' | ';
				$out .= $ret;
				echo trim($out), "\n";
				usleep($this->sleep);
			}

			return TRUE;
		}

		/**
		 * Read ahead in the script to optimise where possible.
		 * This is called AFTER the location pointer has been moved, but before
		 * the instruction is read.
		 *
		 * Optimisations can either edit the instructions and allow continued
		 * execution (return $vm->getLocation()) or can perform the required
		 * state manipulation themselves and provide a new location to continue
		 * from.
		 *
		 * We stop processing optimisations after the first non-FALSE return.
		 *
		 * @return bool|int FALSE if no optimisations were made, else a location index
		 *         for the next instruction we should run.
		 */
		function doReadAheads(): bool|int {
			foreach ($this->readAheads as $function) {
				$return = call_user_func($function, $this);
				if ($return !== FALSE && $return !== NULL) { return $return; }
			}
			return FALSE;
		}

		/**
		 * Add a new ReadAhead optimiser for doReadAheads to use.
		 *
		 * @param $function Function to call in doReadAheads, should accept 1
		 *        parameter (which will be $this)  and return FALSE if no
		 *        optimisation occured, or a new location to continue execution
		 *        from.
		 */
		function addReadAhead($function) {
			$this->readAheads[] = $function;
		}

		/**
		 * Continue stepping through until we reach the end.
		 */
		function run() {
			while ($this->step()) { }
		}

		/**
		 * Parse instruction file into instruction array.
		 *
		 * @param $data Data to parse.
		 * @return string[] Data as an array.
		 */
		public static function parseInstrLines($input) {
			$data = array();
			foreach ($input as $lines) {
				if (preg_match('#([a-z]{3}) ([^\s]+)(?: (.*))?#SADi', $lines, $m)) {
					$data[] = array($m[1], array_slice($m, 2));
				}
			}
			return $data;
		}

		/**
		 * Display an instruction as a string.
		 *
		 * @param $instr Instruction to get string representation for.
		 * @return string version of instruction.
		 */
		public static function instrToString($instr): string {
			return $instr[0] . ' [' . implode(' ', $instr[1]) . ']';
		}
	}

	interface VMInterrupt { }
	class VMException extends Exception { }
	class NoSuchInstrException extends VMException { }
	class BadDataLocationException extends VMException { }
