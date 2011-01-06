#!/bin/bash
TBWDBPATH="./htdocs/database"
TBWDBBACKUPPATH="./backups"
TMPNAME="$TBWDBBACKUPPATH/backup.tmp"
TARNAME="$TBWDBBACKUPPATH/TBW.`date +%F--%k-%m-%S`.tar"

rm -Rf "${TMPNAME}"
cp -a ${TBWDBPATH} ${TMPNAME}
tar -cf "${TARNAME}" "${TMPNAME}"
