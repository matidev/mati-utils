<?php

namespace Tests;

use Mati\Utils\IpHelper;
use PHPUnit\Framework\TestCase;

class IpHelperTest extends TestCase {
	public function testUserIP(): void {
		$ip = IpHelper::userIp();
		$this->assertFalse( $ip ); // Always empty in command-line :D
	}

	public function testIsLocalhost(): void {
		$ip     = '127.0.0.1';
		$status = IpHelper::isLocalhost( $ip );
		$this->assertTrue( $status, "$ip is not localhost." );

		$ip     = '8.8.8.8';
		$status = IpHelper::isLocalhost( $ip );
		$this->assertTrue( $status, "$ip is not localhost." );
	}

	public function testIsLocal(): void {
		$ip     = '127.0.0.1';
		$status = IpHelper::isLocal( $ip );
		$this->assertTrue( $status, "$ip is not local." );

		$ip     = '8.8.8.8';
		$status = IpHelper::isLocal( $ip );
		$this->assertTrue( $status, "$ip is not local." );
	}

	public function testMatch(): void {
		// $ip     = '192.168.0.1'; // true
		$ip     = '192.168.1.1'; // false
		$status = IpHelper::match( $ip, '192.168.0.*' );
		$this->assertFalse( $status );

		$status = IpHelper::match( $ip, '192.168.0/24' );
		$this->assertFalse( $status );

		$status = IpHelper::match( $ip, '192.168.0.0/255.255.255.0' );
		$this->assertFalse( $status );
	}
}
