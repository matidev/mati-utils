<?php

namespace Mati\Utils;

use InvalidArgumentException;
use RuntimeException;

/**
 * IP Address Helper
 * @note Refactor longman/ip-tools library @link https://github.com/akalongman/php-ip-tools
 * @since 1.0.0
 */
class IpHelper {
	public static function userIp(): string {
		$ipaddress = '';
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		} else if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		} else if ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		} else if ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		} else if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		}

		return $ipaddress;
	}

	public static function isLocal( $ip = null ): bool {
		$ip = empty( $ip ) ? self::userIp() : $ip;

		$localIpv4Ranges = [
			'10.*.*.*',
			'127.*.*.*',
			'192.168.*.*',
			'169.254.*.*',
			'172.16.0.0-172.31.255.255',
			'224.*.*.*',
		];
		$localIpv6Ranges = [
			'fe80::/10',
			'::1/128',
			'fc00::/7',
		];

		return self::match( $ip, self::isValidV4( $ip ) ? $localIpv4Ranges : $localIpv6Ranges );
	}

	public static function isRemote( $ip = null ): bool {
		return self::isValid( $ip ) && ! self::isLocal( $ip );
	}

	public static function isLocalhost( $ip = null ): bool {
		$ip = empty( $ip ) ? self::userIp() : $ip;

		return in_array( $ip, [ '127.0.0.1', '::1' ] );
	}

	public static function isValid( $ip ): bool {
		return self::isValidV4( $ip ) || self::isValidV6( $ip );
	}

	public static function isValidV4( $ip ): bool {
		return (bool) filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );
	}

	public static function isValidV6( $ip ): bool {
		return (bool) filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 );
	}

	public static function ip2long( $ip ) {
		$long = - 1;
		if ( self::isValidV6( $ip ) ) {
			if ( ! function_exists( 'bcadd' ) ) {
				throw new RuntimeException( 'BCMATH extension not installed.' );
			}

			$inet = inet_pton( $ip );
			$bin  = '';
			for ( $bit = strlen( $inet ) - 1; $bit >= 0; $bit -- ) {
				$bin = sprintf( '%08b', ord( $inet[ $bit ] ) ) . $bin;
			}

			$dec = '0';
			for ( $i = 0, $iMax = strlen( $bin ); $i < $iMax; $i ++ ) {
				$dec = bcmul( $dec, '2' );
				$dec = bcadd( $dec, $bin[ $i ] );
			}
			$long = $dec;
		} else if ( self::isValidV4( $ip ) ) {
			$long = ip2long( $ip );
		}

		return $long;
	}

	public static function long2ip( $dec, $ipv6 = false ) {
		if ( $ipv6 ) {
			if ( ! function_exists( 'bcadd' ) ) {
				throw new RuntimeException( 'BCMATH extension not installed.' );
			}

			$bin = '';
			do {
				$bin = bcmod( $dec, '2' ) . $bin;
				$dec = bcdiv( $dec, '2' );
			} while ( bccomp( $dec, '0' ) );

			$bin = str_pad( $bin, 128, '0', STR_PAD_LEFT );
			$ip  = [];
			for ( $bit = 0; $bit <= 7; $bit ++ ) {
				$binPart = substr( $bin, $bit * 16, 16 );
				$ip[]    = dechex( bindec( $binPart ) );
			}
			$ip    = implode( ':', $ip );
			$ipStr = inet_ntop( inet_pton( $ip ) );
		} else {
			$ipStr = long2ip( $dec );
		}

		return $ipStr;
	}

	public static function match( $ip, $ranges ): bool {
		if ( is_array( $ranges ) ) {
			foreach ( $ranges as $range ) {
				$match = self::compare( $ip, $range );
				if ( $match ) {
					return true;
				}
			}
		} else {
			return self::compare( $ip, $ranges );
		}

		return false;
	}

	public static function compare( $ip, $range ): bool {
		if ( ! self::isValid( $ip ) ) {
			throw new InvalidArgumentException( 'Input IP "' . $ip . '" is invalid!' );
		}

		if ( strpos( $range, '/' ) !== false ) {
			$status = self::processWithSlash( $ip, $range );
		} else if ( strpos( $range, '*' ) !== false ) {
			$status = self::processWithAsterisk( $ip, $range );
		} else if ( strpos( $range, '-' ) !== false ) {
			$status = self::processWithMinus( $ip, $range );
		} else {
			$status = ( $ip === $range );
		}

		return $status;
	}

	protected static function processWithSlash( $ip, string $range ): bool {
		$isv6 = self::isValidV6( $ip );
		[ $range, $netmask ] = explode( '/', $range, 2 );

		if ( $isv6 ) {
			if ( strpos( $netmask, ':' ) !== false ) {
				$netmask    = str_replace( '*', '0', $netmask );
				$netmaskDec = self::ip2long( $netmask );

				return ( ( self::ip2long( $ip ) & $netmaskDec ) === ( self::ip2long( $range ) & $netmaskDec ) );
			}

			$x = explode( ':', $range );
			while ( count( $x ) < 8 ) {
				$x[] = '0';
			}

			[ $a, $b, $c, $d, $e, $f, $g, $h ] = $x;
			$range       = sprintf(
				"%u:%u:%u:%u:%u:%u:%u:%u",
				empty( $a ) ? '0' : $a,
				empty( $b ) ? '0' : $b,
				empty( $c ) ? '0' : $c,
				empty( $d ) ? '0' : $d,
				empty( $e ) ? '0' : $e,
				empty( $f ) ? '0' : $f,
				empty( $g ) ? '0' : $g,
				empty( $h ) ? '0' : $h
			);
			$rangeDec    = self::ip2long( $range );
			$ipDec       = self::ip2long( $ip );
			$wildcardDec = ( 2 ** ( 32 - $netmask ) ) - 1;
			$netmaskDec  = ~$wildcardDec;

			return ( ( $ipDec & $netmaskDec ) === ( $rangeDec & $netmaskDec ) );
		}

		if ( strpos( $netmask, '.' ) !== false ) {
			$netmask    = str_replace( '*', '0', $netmask );
			$netmaskDec = self::ip2long( $netmask );

			return ( ( self::ip2long( $ip ) & $netmaskDec ) === ( self::ip2long( $range ) & $netmaskDec ) );
		}

		$x = explode( '.', $range );
		while ( count( $x ) < 4 ) {
			$x[] = '0';
		}

		[ $a, $b, $c, $d ] = $x;
		$range       = sprintf( "%u.%u.%u.%u", empty( $a ) ? '0' : $a, empty( $b ) ? '0' : $b, empty( $c ) ? '0' : $c, empty( $d ) ? '0' : $d );
		$rangeDec    = self::ip2long( $range );
		$ipDec       = self::ip2long( $ip );
		$wildcardDec = ( 2 ** ( 32 - $netmask ) ) - 1;
		$netmaskDec  = ~$wildcardDec;

		return ( ( $ipDec & $netmaskDec ) === ( $rangeDec & $netmaskDec ) );
	}

	protected static function processWithAsterisk( $ip, string $range ): bool {
		$isv6 = self::isValidV6( $ip );
		if ( strpos( $range, '*' ) !== false ) {
			$lower = str_replace( '*', ( $isv6 ? '0000' : '0' ), $range );
			$upper = str_replace( '*', ( $isv6 ? 'ffff' : '255' ), $range );
			$range = "$lower-$upper";
		}

		return strpos( $range, '-' ) !== false && self::processWithMinus( $ip, $range );
	}

	protected static function processWithMinus( $ip, string $range ): bool {
		[ $lower, $upper ] = explode( '-', $range, 2 );
		$ipLong = self::ip2long( $ip );

		return ( ( $ipLong >= self::ip2long( $lower ) ) && ( $ipLong <= self::ip2long( $upper ) ) );
	}

	protected static function matchRange( $ip, $range ): bool {
		$ipParts    = array_filter( explode( '.', $ip ) );
		$rangeParts = array_filter( explode( '.', $range ) );

		$ipParts = array_slice( $ipParts, 0, count( $rangeParts ) );

		return implode( '.', $rangeParts ) === implode( '.', $ipParts );
	}
}
