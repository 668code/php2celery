#/usr/bin/env python
# -*- coding: utf-8 -*-

from __future__ import absolute_import
from celery import Celery, Task

celery = Celery('tasks')
celery.config_from_object('celeryconfig')

celery.conf.update(
    CELERY_RESULT_SERIALIZER='json',
    CELERY_TASK_SERIALIZER='json',

    CELERY_TASK_RESULT_EXPIRES=60,

    CELERY_DEFAULT_QUEUE="demo",
    CELERY_DEFAULT_EXCHANGE="demo",

    CELERY_RESULT_EXCHANGE = "demo",
    CELERY_DEFAULT_ROUTING_KEY = 'demo'
)


class DebugTask(Task):
    """
    base, write log when failure
    """
    abstract = True

    def after_return(self, *args, **kwargs):
        print('Task returned: %r' % (self.request, ))

    def on_failure(self, exc, task_id, args, kwargs, einfo):
        print 'FAILURE task_id: %s, kwargs: %s' % (task_id, kwargs)
        print('Task returned: %r' % (self.request, ))
        
    def on_retry(self, exc, task_id, args, kwargs, einfo):
        print 'RETRY'
        print('Task returned: %r' % (self.request, ))

    def on_success(self, retval, task_id, args, kwargs):
        print 'SUCCESS task_id: %s, kwargs: %s' % (task_id, kwargs)


@celery.task(base=DebugTask)
def hello_issue(message):
    print 'hello_issue: Hello %s' % message

