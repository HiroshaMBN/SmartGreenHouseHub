#!/bin/bash

# Check if the required arguments (phone number and message) are provided
if [ $# -ne 2 ]; then
    echo "Usage: $0 <phone_number> <message>"
    exit 1
fi

# Assign the first argument to phone_number and the second argument to message
PHONE_NUMBER=$1
MESSAGE=$2

# Path to the gammu executable (change this if needed)
GAMMU_CMD=$(which gammu)

# Check if gammu is installed and available
if [ -z "$GAMMU_CMD" ]; then
    echo "Error: Gammu is not installed or not in the PATH."
    exit 1
fi

# Send SMS using Gammu
$GAMMU_CMD sendsms TEXT "$PHONE_NUMBER" -text "$MESSAGE"

# Check if the command was successful
if [ $? -eq 0 ]; then
    echo "SMS sent successfully to $PHONE_NUMBER."
else
    echo "Failed to send SMS."
    exit 1
fi
	
