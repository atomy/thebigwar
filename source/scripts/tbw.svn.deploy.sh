#!/bin/bash
#
# script for checking out a given svn revision and backing up the current folder
#

# name of the database folder
DBDIR="db"

# full svn url to the svn repo
SVNREPOPATH="https://jackinpoint.net/svn/testtbw/tags/last-successful/TBW.PostCommit/"

# no restart file to prevent eventhandler starts by crontab - KEEP IN SYNC WITH db_things/checkHandler.sh
NORESTARTSFILE="$HOME/EVENTHANDLER.KEEPDEAD"

TARGETREV=0

cd ~/htdocs/db_things
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

cd ~/

# backup our current stuff
chmod -R 750 htdocs.bak
cp -R htdocs htdocs.bak

echo "Killing Eventhandler, should be restarted later by cronjob."
kill `cat htdocs/database.global/eventhandler.pid`

# export the given revision
svn export ${SVNREPOPATH} ./htdocs/ --force -r ${TARGETREV}

cd ~/htdocs/

echo "Relinking item files..."
# verlinken aller items zu den benoetigten plaetzen
# ln -s ziel ursprung
cd ${DBDIR}/items/
ln -fs ../../database.global/schiffe schiffe
ln -fs ../../database.global/gebaeude gebaeude
ln -fs ../../database.global/forschung forschung
ln -fs ../../database.global/verteidigung verteidigung
ln -fs ../../database.global/roboter roboter

cd ~/htdocs/db_things/
ln -fs ../database.global/schiffe schiffe
ln -fs ../database.global/gebaeude gebaeude
ln -fs ../database.global/forschung forschung
ln -fs ../database.global/verteidigung verteidigung
ln -fs ../database.global/roboter roboter

echo "done."

# lets care about our eventhandler
cd ../db_things

chmod +x checkHandler.sh

rm -f ${NORESTARTSFILE}

echo "All done."
echo "Revision ${TARGETREV} of ${SVNREPOPATH} deployed!"

echo "Uni maintenance removed!"

./checkHandler.sh

./toggleUniMaintenance.sh off

exit 0
