<?php

require_once __DIR__.'/BaseTask.php';

/**
 * Description of Hello
 *
 * @author Li Feilong <feiyang8068@qq.com>
 */
class Hello extends BaseTask {

    protected $message = '';
	
	private $_name = 'hello_issue';

    /**
     * Hello World
     * @param type $message
     * @param type $queue_server
     */
    public function __construct($message, $queue_server) {
        parent::__construct();
        $this->message = $message;
        
        if ($queue_server) {
            $this->setQueueServer($queue_server);
        }
    }

    /**
     * 异步任务
     * @return type
     */
    public function async($async=false) {
        $kwargs = array('message' => $this->message);
        $id = md5($this->message) . time();
        $task = $this->create($id, $this->_name, $kwargs);
        return $this->delay($task, $async);
    }

}
