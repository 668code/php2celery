<?php

/**
 * 添加异步任务
 * @author Li Feilong <feiyang8068@qq.com>
 */

require_once __DIR__.'/AsyncResult.php';

/**
 * Base on celery task
 */
class BaseTask {
    
    const TS_TASK_PREFIX = 'tasks.';

    /**
     * queue server
     * @var RedisClient
     */
    protected $redis;
    protected $routing_key;
    protected $exchange;

    /**
     * 
     * @param RedisClient $server
     */
    public function setQueueServer($server) {
        $this->redis = $server;
    }

    /**
     * 构造函数
     * @param string $routing_key
     * @param string $exchange
     */
    public function __construct($routing_key='demo', $exchange='demo') {
        $this->routing_key = $routing_key;
        $this->exchange = $exchange;
    }

    /**
     * 建立task format
     * @param type $name
     * @param type $kwargs dict
     * @param type $retries
     * @param type $expires
     * @return type
     */
    public function create($id, $name, array $kwargs, $retries = 0, $expires = NULL) {
        return array(
            'id' => $id,
            'task' => self::TS_TASK_PREFIX . $name,
            'kwargs' => $kwargs,
            'retries' => $retries,
            'expires' => $expires,
        );
    }

    /**
     * 
     * @param array $task
     * @param type $async_result
     * @return AsyncResult
     */
    public function delay(array $task, $async_result = false) {
        $task['id'] = sha1($task['id']);
        $bodyTask = array(
            'body' => base64_encode(json_encode($task)),
            'headers' => new stdClass,
            'content-type' => 'application/json',
            'properties' => array(
                'body_encoding' => 'base64',
                'delivery_info' => array(
                    'priority' => 0,
                    'routing_key' => $this->routing_key,
                    'exchange' => $this->exchange,
                ),
                'delivery_tag' => sha1(base64_encode(json_encode($task)) . time()),
                'delivery_mode' => 2
            ),
            'content-encoding' => 'utf-8'
        );
        
        $this->redis->rPush($this->routing_key, json_encode($bodyTask));

        if ($async_result) {
            return new AsyncResult($task['id'], $this->redis);
        } else {
            return $task['id'];
        }
    }
    
    
	public function __set($name, $value) {
		$this->$name = $value;
	}

}
