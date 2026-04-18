<?php

namespace TotalPoll\Limitations;

use TotalPollVendors\TotalCore\Limitations\Limitation;

/**
 * Period Limitation
 * @package TotalPoll\Limitations
 */
class Period extends Limitation {
	/**
	 * Limitation check logic.
	 *
	 * @return bool|\WP_Error
	 */
	public function check() {
		$startDate = empty( $this->args['start'] ) ? false : TotalPoll( 'datetime', [ $this->args['start'] ] );
		$endDate   = empty( $this->args['end'] ) ? false : TotalPoll( 'datetime', [ $this->args['end'] ] );
		$now       = TotalPoll( 'datetime' );

		if ( $startDate && $startDate->getTimestamp() > $now->getTimestamp() ):
			$interval = $startDate->diff( $now, true );

			return new \WP_Error(
				'start_date',
				sprintf(
				 esc_html__( 'This poll has not started yet (%s left).', 'totalpoll' ),
					$this->getDiffForHumans( $interval )
				)
			);
		endif;

		if ( $endDate && $endDate->getTimestamp() < $now->getTimestamp() ):
			$interval = $endDate->diff( $now, true );

			return new \WP_Error(
				'end_date',
				sprintf(
				 esc_html__( 'This poll has ended (since %s).', 'totalpoll' ),
					$this->getDiffForHumans( $interval )
				)
			);
		endif;

		return true;
	}

	private function getDiffForHumans( $interval ) {
		if ( $interval->y > 0 ):
			return $interval->format( _n( '%y year', '%y years', $interval->y, 'totalpoll' ) );
		elseif ( $interval->m > 0 ):
			return $interval->format( _n( '%m month', '%m months', $interval->m, 'totalpoll' ) );
		elseif ( $interval->d > 0 ):
			return $interval->format( _n( '%d day', '%d days', $interval->d, 'totalpoll' ) );
		elseif ( $interval->h > 0 ):
			return $interval->format( _n( '%h hour', '%h hours', $interval->h, 'totalpoll' ) );
		elseif ( $interval->i > 0 ):
			return $interval->format( _n( '%i minute', '%i minutes', $interval->i, 'totalpoll' ) );
		elseif ( $interval->s > 0 ):
			return $interval->format( _n( '%s second', '%s seconds', $interval->s, 'totalpoll' ) );
		endif;

		return '';
	}
}
