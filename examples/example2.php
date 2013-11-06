<?php

require_once( __DIR__ . '/../class.DirtyBruteForceEquationSolver.php');

$oSolver = new DirtyBruteForceEquationSolver();
$fAnswer = $oSolver->solve('$x + ($x * $x) + ($x * $x * $x)', 256, 2);
echo "Answer: " . $fAnswer . PHP_EOL;
