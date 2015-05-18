<?php

require_once __DIR__.'/Hello.php';
require_once __DIR__.'/redis_client.php';

/**
 * Description of HelloTest
 *
 * @author Li Feilong <feiyang8068@qq.com>
 */
class HelloTest extends PHPUnit_Framework_TestCase {
    
    public function testEmpty() {
        $stack = array();
        $this->assertEmpty($stack);
        return $stack;
    }
    
    public function testAsync() {
        $redis = new RedisClient('10.0.0.192', 6379);
        $task = new Hello('I can run fast', $redis);
        $task->setQueueServer($redis);
        $asyncResult = $task->async(True);
        $this->assertNotEmpty($asyncResult);
        sleep(1);
        var_dump($asyncResult);
        print_r($asyncResult->isSuccess());
    }
    
}
