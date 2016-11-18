#!/bin/bash

PHING=phing.phar
COMPOSER=composer.phar
PROPERTIES=build.properties
VERBOSE=

function usage {
  echo "";
  echo "-b|--build-properties       Location of the build.properties file";
  echo "-v|--verbose                runs phing.phar in verbose mode";
  echo "-h|--help                   this usage";
  echo "";
  exit;  
}


while [ "$1" != "" ]; do
  case $1 in
   -b | --build-properties )
   	shift
   	PROPERTIES=$1
        ;;
   -v | --verbose )
   	shift
   	VERBOSE=-verbose
   	;;
   -? | -h | --help )
   	usage
        exit
        ;;
   esac
   shift
done





          