#!/bin/sh

cd `dirname $0`
cd ../../../..
TOPPATH=`pwd`
APPPATH="$TOPPATH/fuel/app"
OILPATH="$TOPPATH/fuel/packages/oil"

files="
classes/controller/article.php
classes/model/article.php
migrations/001_create_articles.php
views/article/_form.php
views/article/create.php
views/article/edit.php
views/article/index.php
views/article/view.php
views/template.php
"

## Check files to exist
cd $APPPATH
# files exists?
exists=0
for i in $files; do
	if [ -f "$i" ]; then
		echo "File exists: APPPATH/$i"
		exists=1
	fi
done
if [ $exists -eq 1 ]; then
	echo "Error: Files already exist"
	exit 1
fi

## Run oil generate admin command
cd $TOPPATH
oil g scaffold article title:string[50] body:text

## Move generated files
cd $APPPATH
for i in $files; do
	mkdir -p `dirname "$OILPATH/tests/generated_files/g_scaffold/$i"`
	mv "$i" "$OILPATH/tests/generated_files/g_scaffold/$i"
done

## Show diffs
cd $OILPATH/tests
git diff generated_files/g_scaffold/
