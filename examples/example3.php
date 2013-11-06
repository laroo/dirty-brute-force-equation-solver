<?php

require_once( __DIR__ . '/../class.DirtyBruteForceEquationSolver.php');

$oSolver = new DirtyBruteForceEquationSolver();

for($precision = 9; $precision >= 0; $precision -= 3) {
	$oSolver->solve('sqrt($x)', 42, $precision);

	echo "Precision: " . $precision . PHP_EOL;
	echo "Answer:    " . $oSolver->getAnswer() . PHP_EOL;
	echo "Duration:  " . round($oSolver->getDuration()*1000, 4) . ' ms' . PHP_EOL;
	echo "Cycles:    " . $oSolver->getCycleCount() . PHP_EOL;
	echo PHP_EOL;

}
