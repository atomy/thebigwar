#!/bin/sh
#
# script to set the uni maintenance status
# paramters:
# on - enables uni maintenance
# off - disables uni maintenance
# check - outputs the current maintenance status
# <none> - toggles the maintenance status
#
# atomy - atomy@thebigwar.org
#

cd ~/htdocs/
cp .htaccess .htaccess.old

grep "#Deny from All #REPLACEMARKER" .htaccess 2>&1 >/dev/null

ISMAINT=$?

if [ -z $1 ] ;
then
	TOGGLE=2
else
	TOGGLE=0
fi

if [ ${TOGGLE} -ne 2 ] && [ $1 = "on" ] ;
then
        TOGGLE=1
elif [ ${TOGGLE} -ne 2 ] && [ $1 = "off" ] ;
then
        TOGGLE=0
elif [ ${TOGGLE} -ne 2 ] && [ $1 = "check" ] ;
then
	if [ ${ISMAINT} -eq 0 ] ; 
	then
		echo "Maintenance mode is off."
		exit 0
	elif [ ${ISMAINT} -eq 1 ] ;
	then
		echo "Maintenance mode is on."
		exit 0
	fi
else
        TOGGLE=2
fi

if [ ${ISMAINT} -eq 0 ] && [ ${TOGGLE} -eq 0 ] ;
then
	echo "Maintenance mode is already off, exiting."
	exit 0
elif [ ${ISMAINT} -eq 1 ] && [ ${TOGGLE} -eq 1 ] ;
then
	echo "Maintenance mode is already on, exiting."
	exit 0
fi

if [ ${ISMAINT} -eq 0 ] ;
then
	echo "Uni is running normal, setting to maintenance!"
	cat .htaccess.old | sed 's/#Deny from All #REPLACEMARKER/Deny from All #REPLACEMARKER/g' > .htaccess
else
	echo "Uni is set to maintenance, setting to normal!"
	cat .htaccess.old | sed 's/Deny from All #REPLACEMARKER/#Deny from All #REPLACEMARKER/g' > .htaccess
fi

rm .htaccess.old

echo "done."
