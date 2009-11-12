#!/bin/sh

CFGFILE="../include/config_inc.php"
TMPCFGFILE="./tmpcfgfile.tmp"

if [ $# -lt 1 ]
then
	echo "Usage: $0 <dir>"
	exit 1
fi

cat ${CFGFILE} | head -n-1 > ${TMPCFGFILE}
mv ${TMPCFGFILE} ${CFGFILE}

echo "define(\"TBW_ROOT\", \"${1}/\");" >> ${CFGFILE}
echo '?>' >> ${CFGFILE}
