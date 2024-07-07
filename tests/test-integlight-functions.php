<?php

use PHPUnit\Framework\TestCase;

class InteglightBreadcrumbTest extends TestCase
{
    public function test_helper_addUl()
    {
        // テスト対象のクラスをインスタンス化
        $breadcrumb = new InteglightBreadcrumb();

        // テストデータ
        $breadcrumbData = '<li>Test</li>';
        $expectedOutput = '<ul class="create_bread">'
            . '<i class="fa-solid fa-house"></i>'
            . '<li><a href="' . home_url() . '">HOME</a></li>'
            . '<i class="fa-solid fa-angle-right"></i>'
            . $breadcrumbData
            . '</ul>';

        // Reflectionを使用してプライベートメソッドにアクセス
        $reflection = new ReflectionClass($breadcrumb);
        $method = $reflection->getMethod('helper_addUl');
        $method->setAccessible(true);

        // メソッドを実行し、結果を検証
        $output = $method->invokeArgs($breadcrumb, [$breadcrumbData]);
        $this->assertEquals($expectedOutput, $output);
    }
}
