<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Rum\Node;

/**
 * 路由树测试
 */
final class NodeTest extends TestCase
{

    /**
     * 测试基础路由
     */
    public function testNormal()
    {

        $node = new Node();
        $p1 = '/user/one';
        $node->addRoute($p1, function () use ($p1) {
            return $p1;
        });

        $handle = $node->getValue($p1);
        $this->assertNotEmpty($handle); // 可取获取到路由处理方法
        $this->assertNotEmpty($handle['handles']);  // 处理方法不为空
        // $this->assertCount(1, $handle['handles']);  // 只包含一个处理方法
        $this->assertEquals($handle['handles'], null);
        $this->assertEquals($handle['handles'][0](), $p1);  // 处理方法返回字符串

    }
}
