<?php

namespace Tests;

use Mati\Utils\VietnameseHelper;
use PHPUnit\Framework\TestCase;

class VietnameseHelperTest extends TestCase
{
    public function testUserIP(): void
    {
        $result = VietnameseHelper::makeSlug('Hàm hỗ trợ tạo slug từ chuỗi tiếng Việt có dấu.');

        echo "\n\n$result\n";

        $this->assertNotEmpty($result);
    }
}
