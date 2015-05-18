<?php

/*
 * AsyncResult Object
 * @author Li Feilong <feiyang8068@qq.com>
 */

class CeleryException extends Exception {
    
}

/**
 * Celery AsyncResult
 * 
 */
class AsyncResult {

    const TASK_META_PREFIX = 'celery-task-meta-';

    private $task_id; // string, queue name
    /**
     *
     * @var RedisClient
     */
    private $redis;
    private $body;
    
    private $timeout = 5;

    /**
     * 
     * @param type $task_id
     * @param type $redis
     */
    public function __construct($task_id, $redis) {
        $this->task_id = $task_id;
        $this->redis = $redis;
    }
    
    /**
     * 设置
     * @param type $timeout
     */
    public function setTimeout($timeout) {
        $this->timeout = intval($timeout);
    }

    /**
     * 获取完全执行结果
     * @return array
     */
    public function getMessageBody() {
        $message = $this->redis->get(self::TASK_META_PREFIX . $this->task_id);

        if ($message !== false) {
            $this->body = json_decode($message);
            return $this->body;
        }

        return FALSE;
    }

    /**
     * 获取执行结果
     */
    public function get() {
        if (!$this->isReady()) {
            throw new CeleryTimeoutException(sprintf('Redis task %s(%s) did not return after %d seconds', $this->task_name, json_encode($this->task_args), $this->timeout), 4);
        }
        return $this->getResult();
    }

    /**
     * Get the Task Id
     * @return string
     */
    function getId() {
        return $this->task_id;
    }

    /**
     * Check if a task result is ready
     * @return bool
     */
    function isReady() {
        return ($this->getMessageBody() !== false);
    }

    /**
     * Return task status (needs to be called after isReady() returned true)
     * @return string 'SUCCESS', 'FAILURE' etc - see Celery source
     */
    function getStatus() {
        if (!$this->isReady()) {
            throw new CeleryException('Called getStatus before task was ready');
        }
        $ret = $this->getMessageBody();
        return isset($ret->status) ? $ret->status : false;
    }

    /**
     * Check if task execution has been successful or resulted in an error
     * @return bool
     */
    function isSuccess() {
        return($this->getStatus() == 'SUCCESS');
    }

    /**
     * If task execution wasn't successful, return a Python traceback
     * @return string
     */
    function getTraceback() {
        if (!$this->isReady()) {
            throw new CeleryException('Called getTraceback before task was ready');
        }
        $ret = $this->getMessageBody();
        return isset($ret->traceback) ? $ret->traceback : false;
    }

    /**
     * Return a result of successful execution.
     * In case of failure, this returns an exception object
     * @return mixed Whatever the task returned
     */
    function getResult() {
        if (!$this->body) {
            throw new CeleryException('Called getResult before task was ready');
        }
        return $this->body->result;
    }

}
