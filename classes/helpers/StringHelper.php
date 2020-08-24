<?php

namespace webp\classes\helpers;

/**
 * Class StringHelper Helps to work with strings.
 */
class StringHelper {
	/**
	 * Checks whether `$haystack` ands with substring `$needle`.
	 *
	 * @param $haystack
	 * @param $needle
	 *
	 * @return bool Returns true if `$haystack` ands with substring `$needle`.
	 */
	public static function endsWith( $haystack, $needle ): bool {
		$length = strlen( $needle );

		if ( ! $length ) {
			return true;
		}

		return substr( $haystack, - $length ) === $needle;
	}
}
