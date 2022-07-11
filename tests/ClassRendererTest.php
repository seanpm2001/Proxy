<?php

declare(strict_types=1);

namespace Yiisoft\Proxy\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Yiisoft\Proxy\ClassConfigFactory;
use Yiisoft\Proxy\ClassRenderer;
use Yiisoft\Proxy\Tests\Stub\Line;
use Yiisoft\Proxy\Tests\Stub\Money;
use Yiisoft\Proxy\Tests\Stub\MyProxy;
use Yiisoft\Proxy\Tests\Stub\Node;
use Yiisoft\Proxy\Tests\Stub\NodeInterface;

class ClassRendererTest extends TestCase
{
    public function testRenderInterface(): void
    {
        $factory = new ClassConfigFactory();
        $config = $factory->getClassConfig(NodeInterface::class);
        $renderer = new ClassRenderer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rendering of interfaces is not supported.');
        $renderer->render($config);
    }

    public function testRenderClassWithoutParent(): void
    {
        $factory = new ClassConfigFactory();
        $config = $factory->getClassConfig(Line::class);
        $renderer = new ClassRenderer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class config is missing a parent.');
        $renderer->render($config);
    }

    public function testRenderInterfaceMethods(): void
    {
        $factory = new ClassConfigFactory();
        $config = $factory->getClassConfig(Money::class);
        $config->parent = MyProxy::class;

        $renderer = new ClassRenderer();
        $output = $renderer->render($config);
        $expectedOutput = <<<'EOD'
class Money extends Yiisoft\Proxy\Tests\Stub\MyProxy implements Countable
{
    public function count(): int
    {
        return $this->call('count', []);
    }
}
EOD;

        $this->assertSame($expectedOutput, $output);
    }

    public function testRenderOwnMethods(): void
    {
        $factory = new ClassConfigFactory();
        $config = $factory->getClassConfig(Node::class);
        $config->parent = MyProxy::class;

        $renderer = new ClassRenderer();
        $output = $renderer->render($config);
        $expectedOutput = <<<'EOD'
class Node extends Yiisoft\Proxy\Tests\Stub\MyProxy
{
    public function someMethod(): void
    {
        $this->call('someMethod', []);
    }
}
EOD;

        $this->assertSame($expectedOutput, $output);
    }
}
