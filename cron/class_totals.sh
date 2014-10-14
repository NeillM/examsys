#!/bin/bash

# This file is part of Rogō
#
# Rogō is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Rogō is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

# Environment variables in use.
# ROGO - location of rogo base directory
# ROGOCRONLOGS - location to log output of script

# Declare variables.
ROGODIR="${ROGO}/testing"
ROGOSCRIPT="class_totals_with_script_cli.php"
TIMESTAMP=$(date +"%Y-%m-%d-%H:%M:%S")
LOGDIR=${ROGOCRONLOGS}/checktotals
LOCKFILE=checktotals

# Check for log directory and create if not there.
if [ ! -d ${LOGDIR} ]; then
	mkdir ${LOGDIR}
fi

LOGFILE="${LOGDIR}/checktotals.log.${TIMESTAMP}"

# Run rogo script and log if not already running.
if [ ! -f /var/www/cronlock/${LOCKFILE} ]
   then
        touch /var/www/cronlock/${LOCKFILE}
        cd ${ROGODIR}
        php ${ROGOSCRIPT} > ${LOGFILE} &
        rm -Rf /var/www/cronlock/${LOCKFILE}
fi
