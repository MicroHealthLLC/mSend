#!/bin/bash
LOGFILE=/var/log/msendupdate.log

timestamp()
{
 date +"%Y-%m-%d %T"
}

exec &> >(tee $LOGFILE) 2>&1

set -x

echo "$(timestamp): Deploying mSend"

cd /var/www/mSend \
&& sudo git pull \
&& chown -R chown -R apache:apache *
&& apachectl restart

echo "$(timestamp): mSend Deployed"

set +x
