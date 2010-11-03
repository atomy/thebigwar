#!/bin/sh

CFGFILE="../include/config_inc.php"
TMPCFGFILE="./tmpcfgfile.tmp"

if [ $# -lt 1 ]
then
	echo "Usage: $0 <dir>"
	exit 1
fi

# clear file
touch ${TMPCFGFILE}
> ${TMPCFGFILE}

echo '<?php' >> ${TMPCFGFILE}
echo "define(\"TBW_ROOT\", \"${1}/\");" >> ${TMPCFGFILE}
cat ${CFGFILE} | head -n-1 | tail -n +2 >> ${TMPCFGFILE}
echo '?>' >> ${TMPCFGFILE}

mv ${TMPCFGFILE} ${CFGFILE}
