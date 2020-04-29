<?php

namespace FoldingAtHome;

class OSStats
{
	protected $os;

	protected $AMDGPUs;
	protected $NVIDIAGPUs;
	protected $CPUs;
	protected $CPUCores;
	protected $TFLOPS;
	protected $TFLOPSx86;

	/**
	 * OSStats constructor.
	 *
	 * @param $os
	 * @param $AMDGPUs
	 * @param $NVIDIAGPUs
	 * @param $CPUs
	 * @param $CPUCores
	 * @param $TFLOPS
	 * @param $TFLOPSx86
	 */
	public function __construct(string $os, int $AMDGPUs, int $NVIDIAGPUs, int $CPUs, int $CPUCores, int $TFLOPS, int $TFLOPSx86) {
		$this->os = $os;
		$this->AMDGPUs = $AMDGPUs;
		$this->NVIDIAGPUs = $NVIDIAGPUs;
		$this->CPUs = $CPUs;
		$this->CPUCores = $CPUCores;
		$this->TFLOPS = $TFLOPS;
		$this->TFLOPSx86 = $TFLOPSx86;
	}

	public static function createFromArray(array $array) {
		return new OSStats($array[0], $array[1], $array[2], $array[3], $array[4], $array[5], $array[6]);
	}

	public function __get($name) {
		return $this->{$name};
	}
}