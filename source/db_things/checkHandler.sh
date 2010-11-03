# !/bin/bash
# no restart file to prevent eventhandler starts by crontab - KEEP IN SYNC WITH tbw.svn.deploy.sh
NORESTARTSFILE="$HOME/EVENTHANDLER.KEEPDEAD"

cmdexec="./eventhandler.php --daemon --no-jabber"
pidfile="../database.global/eventhandler.pid"

if [ -e ${NORESTARTSFILE} ] ;
then
	echo "lock file exists, exiting..."
	exit 0
fi

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
