<?php

/**
 *
 * Class DirtyBruteForceEquationSolver
 *
 * A PHP Composer class that's solves your 'complex' equations the dirty brute force way!
 *
 * @version v1.0
 * @author Jan-Age Laroo <jan-age@minus3.nl>
 * @license MIT
 * @example See examples-folder
 *
 */
class DirtyBruteForceEquationSolver
{

	/**
	 * Dumb standard class, better than an array
	 *
	 * @var stdClass
	 */
	protected $oCalc = NULL;

	/**
	 * Centralized init
	 *
	 * @param $psFormula
	 * @param $pfAnswer
	 * @param $piDecimalPrecision
	 * @throws UnexpectedValueException
	 */
	protected function _initCalcObj($psFormula, $pfAnswer, $piDecimalPrecision)
	{

		if (FALSE === strpos($psFormula, '$x')) {
			throw new UnexpectedValueException('Need at least one \'$x\' to calculate!');
		}

		$this->oCalc = new stdClass;
		$this->oCalc->formula = $psFormula;
		$this->oCalc->currentX = (float)0;
		$this->oCalc->lastX = (float)0;
		$this->oCalc->minX = NULL; //floatval('-INF');
		$this->oCalc->maxX = NULL; //floatval('INF');
		$this->oCalc->targetAnswer = $pfAnswer;
		$this->oCalc->currentAnswer = NULL;
		$this->oCalc->solved = FALSE;
		$this->oCalc->solvedExact = FALSE;
		$this->oCalc->decimalprecision = (int)$piDecimalPrecision;
		$this->oCalc->_calc_count = 0;
		$this->oCalc->_calc_timestart = NULL;
		$this->oCalc->_calc_timeend = NULL;
		$this->oCalc->targetAnswer = round($this->oCalc->targetAnswer, $this->oCalc->decimalprecision);

	}

	/**
	 * Action! Solve the equation
	 *
	 * @param $psFormula
	 * @param $pfAnswer
	 * @param int $piDecimalPrecision
	 * @return float
	 */
	public function solve($psFormula, $pfAnswer, $piDecimalPrecision = 8)
	{

		$this->_initCalcObj($psFormula, $pfAnswer, $piDecimalPrecision);

		return $this->_solve();
	}

	/**
	 * Total duration that it took to solve the problem
	 * Only relevant for debugging
	 *
	 * @return float
	 */
	public function getDuration()
	{

		return ($this->oCalc->_calc_timeend - $this->oCalc->_calc_timestart);
	}

	/**
	 * How many brute force calculations were made?
	 * Only relevant for debugging
	 *
	 * @return integer
	 */
	public function getCycleCount()
	{

		return $this->oCalc->_calc_count;
	}

	/**
	 * The answer!
	 *
	 * @return float
	 */
	public function getAnswer()
	{

		return $this->oCalc->currentX;
	}

	/**
	 * Is the calculated a precise or approximately answer
	 *
	 * @return boolean
	 */
	public function isSolvedExact()
	{

		return $this->oCalc->solvedExact;
	}

	/**
	 * @return float
	 * @throws UnexpectedValueException
	 */
	protected function _solve()
	{

		// Start timer
		$this->oCalc->_calc_timestart = microtime(TRUE);

		while (FALSE === $this->oCalc->solved) {
			$this->oCalc->_calc_count++;

			// De 'formule'
			//$this->oCalc->currentAnswer = $this->oCalc->currentX + ($this->oCalc->currentX * $this->oCalc->currentX) + ($this->oCalc->currentX * $this->oCalc->currentX * $this->oCalc->currentX);
			$x = $this->oCalc->currentX;
			//var_dump($this->oCalc->formula);
			$this->oCalc->currentAnswer = @eval('return (' . $this->oCalc->formula . ');');
			if (FALSE === $this->oCalc->currentAnswer) {
				throw new UnexpectedValueException('Hmmm... there seems to be something wrong with your formula!');
			}

			$this->oCalc->currentAnswer = round($this->oCalc->currentAnswer, $this->oCalc->decimalprecision);

			if ($this->oCalc->currentAnswer > $this->oCalc->targetAnswer) {
				// Result too high, divide by 2

				if ($this->oCalc->maxX == NULL OR ($this->oCalc->maxX > $this->oCalc->currentX)) {
					$this->oCalc->maxX = $this->oCalc->currentX;
				}

				$this->oCalc->lastX = $this->oCalc->currentX;
				if ($this->oCalc->minX == NULL && $this->oCalc->maxX == 0) {
					$this->oCalc->currentX = -99999999999;
				} elseif ($this->oCalc->minX == NULL) {
					$this->oCalc->currentX = $this->oCalc->maxX / 2;
				} else {
					$this->oCalc->currentX = (($this->oCalc->maxX - $this->oCalc->minX) / 2) + $this->oCalc->minX;
				}
			} elseif ($this->oCalc->currentAnswer < $this->oCalc->targetAnswer) {
				// Result too low, times 2

				if ($this->oCalc->minX == NULL OR ($this->oCalc->minX < $this->oCalc->currentX)) {
					$this->oCalc->minX = $this->oCalc->currentX;
				}

				$this->oCalc->lastX = $this->oCalc->currentX;
				if ($this->oCalc->maxX == NULL && $this->oCalc->minX == 0) {
					$this->oCalc->currentX = 99999999999;
				} elseif ($this->oCalc->maxX == NULL) {
					$this->oCalc->currentX = $this->oCalc->minX * 2;
				} else {
					$this->oCalc->currentX = (($this->oCalc->maxX - $this->oCalc->minX) / 2) + $this->oCalc->minX;
				}

			} else {
				// Solved!
				$this->oCalc->solved = TRUE;
			}
			$this->oCalc->currentX = round($this->oCalc->currentX, $this->oCalc->decimalprecision);

			if ($this->oCalc->currentAnswer == $this->oCalc->targetAnswer) {
				// Solved, perfect match!
				$this->oCalc->solved = TRUE;
				$this->oCalc->solvedExact = TRUE;
				break;
			} elseif ($this->oCalc->currentX == $this->oCalc->lastX) {
				// Solved, as close as humanly possible
				$this->oCalc->solved = TRUE;
				$this->oCalc->solvedExact = FALSE;
				break;
			}

			// Failsafe
			if ($this->oCalc->_calc_count >= 1000) break;
		}
		
		// Stop timer
		$this->oCalc->_calc_timeend = microtime(TRUE);

		return $this->oCalc->currentX;
	}

}

?>