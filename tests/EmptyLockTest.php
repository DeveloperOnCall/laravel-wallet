<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Objects\EmptyLock;

class EmptyLockTest extends TestCase
{

    /**
     * @return void
     */
    public function testSimple(): void
    {
        $empty = new EmptyLock();
        $this->assertTrue($empty->block(1));
        $this->assertTrue($empty->block(1, null));
        $this->assertNull($empty->get());
        $this->assertTrue($empty->get(static function () {
            return true;
        }));
    }

    /**
     * @return void
     */
    public function testOwner(): void
    {
        $empty = new EmptyLock();
        $str = $empty->owner();
        $this->assertIsString($str);
        $this->assertEquals($str, $empty->owner());
    }

}
