

webuser用户执行，避免权限过大
celery worker --app=demo -l DEBUG

-c 2 默认值为5，可以调小一点，避免mysql连接丢失。

stop
ps aux | grep 'celery worker' | awk '{print $2}' | xargs kill

start
celery worker --app=tasks -l DEBUG --workdir=/path --uid=webuser --gid=webuser




