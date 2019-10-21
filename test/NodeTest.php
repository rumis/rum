<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Rum\Node;

require __DIR__ . '/../vendor/autoload.php';

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
        $node->addRoute($p1, [function () use ($p1) {
            return $p1;
        }]);

        $hp1 = $node->getValue($p1);
        $this->assertNotEmpty($hp1); // 可取获取到路由处理方法
        $this->assertNotEmpty($hp1['handles']);  // 处理方法不为空
        $this->assertCount(1, $hp1['handles']);  // 只包含一个处理方法
        $this->assertEquals($hp1['handles'][0](), $p1);  // 处理方法返回字符串

        // 其他路由
        $p2 = '/user/two';
        $node->addRoute($p2, [function () use ($p2) {
            return $p2;
        }]);
        $hp2 = $node->getValue($p2);
        $this->assertEquals($hp2['handles'][0](), $p2);  // 处理方法返回字符串
    }

    /**
     * 参数测试
     */
    public function testParams()
    {
        $node = new Node();
        $p1 = '/user/:one';
        $node->addRoute($p1, [function () use ($p1) {
            return $p1;
        }]);

        $hp1 = $node->getValue('/user/murong');
        $this->assertCount(1, $hp1['params']);  // 只有一个参数
        $this->assertEquals($hp1['params']['one'], 'murong');

        $p2 = '/user/:one/id';
        $node->addRoute($p2, [function () use ($p2) {
            return $p2;
        }]);

        $hp2 = $node->getValue('/user/liumurong/id');
        $this->assertEquals($hp2['params']['one'], 'liumurong');

        $p3 = '/admin/x/*one';
        $node->addRoute($p3, [function () use ($p3) {
            return $p3;
        }]);

        $hp3 = $node->getValue('/admin/x/liu/murong');
        $this->assertEquals($hp3['params']['one'], 'liu/murong');

        $p4 = '/admin/y/:one/*id';
        $node->addRoute($p4, [function () use ($p4) {
            return $p4;
        }]);

        $hp4 = $node->getValue('/admin/y/murong/mixed/string/empty');
        $this->assertCount(2, $hp4['params']);
        $this->assertEquals($hp4['params']['one'], 'murong');
        $this->assertEquals($hp4['params']['id'], 'mixed/string/empty');
    }

    /**
     * 冲突--参数节点和普通节点冲突
     */
    public function testConflictParamsPath1()
    {
        $node = new Node();
        $p1  = '/user/name';
        $p2  = '/user/:one';
        $this->expectException('Exception');
        try {
            $node->addRoute($p1, [function () { }]);
            $node->addRoute($p2, [function () { }]);
        } catch (\Throwable $th) {
            $this->assertEquals($th->getMessage(), '参数节点不可包含其他子节点');
            throw $th;
        }
    }

    /**
     * 冲突--参数节点和普通节点冲突
     */
    public function testConflictParamsPath2()
    {
        $node = new Node();
        $p1  = '/user/name/getone';
        $p2  = '/user/:one';
        $this->expectException('Exception');
        try {
            $node->addRoute($p1, [function () { }]);
            $node->addRoute($p2, [function () { }]);
        } catch (\Throwable $th) {
            $this->assertEquals($th->getMessage(), '参数节点不可包含其他子节点');
            throw $th;
        }
    }
    /**
     * 冲突--路由重复
     */
    public function testConflictParamsPath3()
    {
        $node = new Node();
        $p1  = '/user/name';
        $p2  = '/user/name';
        $this->expectException('Exception');
        try {
            $node->addRoute($p1, [function () { }]);
            $node->addRoute($p2, [function () { }]);
        } catch (\Throwable $th) {
            $this->assertEquals($th->getMessage(), '此路由的响应方法已存在，请勿重复添加');
            throw $th;
        }
    }

    /**
     * 冲突--一段路径只能有一个参数
     */
    public function testConflictParamsPath4()
    {
        $node = new Node();
        $p1  = '/user/:name:id';
        $this->expectException('Exception');
        try {
            $node->addRoute($p1, [function () { }]);
        } catch (\Throwable $th) {
            $this->assertEquals($th->getMessage(), '一段路径中只能包含一个参数项');
            throw $th;
        }
    }
    /**
     * 冲突--必须存在参数名称
     */
    public function testConflictParamsPath5()
    {
        $node = new Node();
        $p1  = '/user/:/name';
        $this->expectException('Exception');
        try {
            $node->addRoute($p1, [function () { }]);
        } catch (\Throwable $th) {
            $this->assertEquals($th->getMessage(), '必须存在参数名称');
            throw $th;
        }
    }

    /**
     * 冲突--*参数只能出现在路由的最后一段
     */
    public function testConflictParamsPath6()
    {
        $node = new Node();
        $p1  = '/user/*item/one';
        $this->expectException('Exception');
        try {
            $node->addRoute($p1, [function () { }]);
        } catch (\Throwable $th) {
            $this->assertEquals($th->getMessage(), '*参数只能出现在路由的最后一段');
            throw $th;
        }
    }

    /**
     * 冲突--*参数前必须为/
     */
    public function testConflictParamsPath7()
    {
        $node = new Node();
        $p1  = '/user/item*one';
        $this->expectException('Exception');
        try {
            $node->addRoute($p1, [function () { }]);
        } catch (\Throwable $th) {
            $this->assertEquals($th->getMessage(), '*参数前必须为/');
            throw $th;
        }
    }
}
