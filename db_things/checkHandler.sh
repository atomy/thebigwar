# !/bin/bash

cmdexec="./eventhandler --daemon --no-jabber"
pidfile="../database.global/eventhandler.pid"

if test -r ${pidfile}
then
        ppid=`cat ${pidfile}`

        if `kill -CHLD ${ppid} >/dev/null 2>&1`
        then
                echo "still running"
                exit 0
        fi

        echo "down - running..."
        nohup ${cmdexec} >/dev/null 2>&1 &
else
        echo "theres no pid file, just start it"
        nohup ${cmdexec} >/dev/null 2>&1 &
fi
