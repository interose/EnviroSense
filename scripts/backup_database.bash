#!/bin/bash

DATABASE="sfinternetofthings"
SQLFILE="sfinternetofthings.sql"
PORT=3336
SSH_PORT=8122

if test -f "/usr/local/opt/mysql-client/bin/mysqldump"; then
    BINDIR="/usr/local/opt/mysql-client/bin/"
elif test -f "/opt/homebrew/bin/mysqldump"; then
    BINDIR="/opt/homebrew/bin/"
else
    echo "ERROR: mysql-client not found"
    exit 1
fi

MYSQLDUMP="${BINDIR}mysqldump"
MYSQL="${BINDIR}mysql"

echo "1. Establishing tunnel  ... "
ssh -fN -p $SSH_PORT -L $PORT:127.0.0.1:3306 marcme@31.15.67.29
if [ $? != 0 ]; then
    echo "ERROR: could not establish ssh tunnel"
    exit 1
fi

echo "2. Duming data  ... "
PID=$(pgrep -f "N -p $SSH_PORT -L $PORT:")
$MYSQLDUMP --column-statistics=0 -h 127.0.0.1 -v --port=$PORT -u root -p $DATABASE > "$SQLFILE"
if [ $?  != 0 ]; then
    echo "ERROR: could not connect to remote database"
    kill $PID
    exit 1
fi
kill $PID
echo "done"

echo -n "3. Importing database to local server (old database) ... "
$MYSQL -v -u root -p -h 127.0.0.1 $DATABASE < "$SQLFILE"
if [ $? != 0 ]; then
    echo "ERROR: could not import dump into local database"
    rm -rf "$SQLFILE"
    exit 1
fi
rm -rf "$SQLFILE"
echo "done"

echo -n "4. Copy content to new database ... "
$MYSQL -v -u root -p -h 127.0.0.1 $DATABASE < sql_import_statements.sql
if [ $? != 0 ]; then
    echo "ERROR: could not execute sql statements on local database"
    exit 1
fi
echo "done"
