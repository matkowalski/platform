<?php declare(strict_types=1);

namespace Shopware\Tests\Unit\Core\Checkout\Cart\Event;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Event\SalesChannelContextAssembledEvent;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Test\Cart\Common\Generator;
use Shopware\Core\Framework\Log\Package;

/**
 * @covers \Shopware\Core\Checkout\Cart\Event\SalesChannelContextAssembledEvent
 *
 * @internal
 */
#[Package('checkout')]
class SalesChannelContextAssembledEventTest extends TestCase
{
    public function testConstruct(): void
    {
        $order = new OrderEntity();
        $context = Generator::createSalesChannelContext();

        $event = new SalesChannelContextAssembledEvent($order, $context);

        static::assertSame($order, $event->getOrder());
        static::assertSame($context->getContext(), $event->getContext());
    }
}
