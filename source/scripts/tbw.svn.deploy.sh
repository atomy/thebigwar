#!/bin/bash
#
# script for checking out a given svn revision and backing up the current folder
#

# name of the database folder
DBDIR="database"

SVNUSER="hudson"
SVNPASSWORD="36SWShCBzMwl"

# full svn url to the svn repo
SVNREPOPATH="https://svn.jackinpoint.net/tbw/trunk/source/"

# no restart file to prevent eventhandler starts by crontab - KEEP IN SYNC WITH db_things/checkHandler.sh
NORESTARTSFILE="${HOME}/EVENTHANDLER.KEEPDEAD"

# we save the svn rev in here
SVNREVFILE="${HOME}/htdocs/db_things/SVNVERSION"

> ${SVNREVFILE}

TARGETREV=0
TARGETREVNUM=0

cd ${HOME}/htdocs/db_things
./toggleUniMaintenance.sh on

touch ${NORESTARTSFILE}
echo "Deploying from SVN ${SVNREPOPATH}..."

if [ ${1} ] && [ ${1} -gt 0 ] ;
then
	TARGETREV=${1}
	echo "Using Revision ${TARGETREV}."
else
	echo "Warning: No Revision given, using default HEAD!"
	TARGETREV="HEAD"
fi

TARGETREVNUM=`svn info ${SVNREPOPATH} --username ${SVNUSER} --password ${SVNPASSWORD} -r ${TARGETREV} | grep "Revision:" | cut -d" " -f2`

cd ${HOME}

# backup our current stuff
#chmod -R 750 htdocs.bak
#cp -R htdocs htdocs.bak

echo "Killing Eventhandler, should be restarted later by cronjob."
if [ -r htdocs/database.global/eventhandler.pid ] ; then
	EVPID=`cat htdocs/database.global/eventhandler.pid`
	if [ 0${EVPID} -gt 0 ] ; then
		kill ${EVPID}
	fi
fi
killall -q "/usr/bin/php"

# export the given revision
svn --username ${SVNUSER} --password ${SVNPASSWORD} export ${SVNREPOPATH} ./htdocs/ --force -r ${TARGETREV}

cd ${HOME}/htdocs/

echo "Relinking item files..."
# verlinken aller items zu den benoetigten plaetzen
# ln -s ziel ursprung
cd ${DBDIR}/items/
ln -fs ../../database.global/schiffe schiffe
ln -fs ../../database.global/gebaeude gebaeude
ln -fs ../../database.global/forschung forschung
ln -fs ../../database.global/verteidigung verteidigung
ln -fs ../../database.global/roboter roboter

cd ${HOME}/htdocs/db_things/
ln -fs ../database.global/schiffe schiffe
ln -fs ../database.global/gebaeude gebaeude
ln -fs ../database.global/forschung forschung
ln -fs ../database.global/verteidigung verteidigung
ln -fs ../database.global/roboter roboter

echo "done."

# lets care about our eventhandler
cd ../db_things

chmod +x checkHandler.sh
cd ..
chmod -R 770 *
cd db_things

rm -f ${NORESTARTSFILE}

echo "All done."
echo "Revision ${TARGETREV} (${TARGETREVNUM}) of ${SVNREPOPATH} deployed!"
echo "Uni maintenance removed!"
echo ${TARGETREVNUM} > ${SVNREVFILE}

./checkHandler.sh

./toggleUniMaintenance.sh off

exit 0
