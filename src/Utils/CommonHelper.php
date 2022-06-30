<?php

namespace Mati\Utils;

class CommonHelper {
	public static function dumpDie( $data, $addPre = true ): void {
		echo $addPre ? '<pre>' : '';
		var_dump( $data );
		echo $addPre ? '</pre>' : '';
		die( 1 );
	}

	public static function formatNumber( $number, $decimalSeparator = ',', $thousandsSeparator = '.' ) {
		if ( empty( $number ) || is_numeric( $number ) ) {
			return $number;
		}

		return number_format( $number, 0, $decimalSeparator, $thousandsSeparator );
	}

	public static function makeNumberCallLink( $num, $code = '+1' ): string {
		return $code . preg_replace( '/\D/', '', ltrim( $num, '0' ) );
	}

	public static function getUserIp(): string {
		return IpHelper::userIp();
	}

	public static function getUserAgent(): string {
		return ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
	}
}
