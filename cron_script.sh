#! /bin/bash

if [ -z $1 ]
then
	echo 'Error: Address of the website on the server not given !'
	exit 1
fi

rm -R $1/tmp/*
