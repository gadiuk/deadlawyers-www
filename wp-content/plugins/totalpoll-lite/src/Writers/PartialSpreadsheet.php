<?php

namespace TotalPoll\Writers;

use TotalPollVendors\TotalCore\Export\Spreadsheet;
use TotalPollVendors\TotalCore\Export\Writer;

/**
 * Class Spreadsheet.
 * @package TotalPollVendors\TotalCore\Export
 */
class PartialSpreadsheet extends Spreadsheet {
	/**
	 * Save to path.
	 *
	 * @param PartialWriter $writer
	 * @param        $path
	 */
	public function save( Writer $writer, $path ) {
		if ( ! file_exists( $path ) ) {
			touch( $path );
		}

		$content = $this->get( $writer );

		if ( $writer instanceof PartialJsonWriter && $content === ']' ) {
			$fileHandle = fopen( $path, 'r+' );
			ftruncate( $fileHandle, fstat( $fileHandle )['size'] - 1 );
			fclose( $fileHandle );
		}

		file_put_contents( $path, $content, FILE_APPEND );
	}
}
