#!/bin/sh

cd `dirname $0`
cd ../../../..
TOPPATH=`pwd`
APPPATH="$TOPPATH/fuel/app"
OILPATH="$TOPPATH/fuel/packages/oil"

files="
classes/controller/admin.php
classes/controller/admin/article.php
classes/controller/base.php
classes/model/article.php
migrations/001_create_articles.php
views/admin/article/_form.php
views/admin/article/create.php
views/admin/article/edit.php
views/admin/article/index.php
views/admin/article/view.php
views/admin/dashboard.php
views/admin/login.php
views/admin/template.php
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
oil g admin article title:string[50] body:text

## Move generated files
cd $APPPATH
for i in $files; do
	mkdir -p `dirname "$OILPATH/tests/generated_files/g_admin/$i"`
	mv "$i" "$OILPATH/tests/generated_files/g_admin/$i"
done

## Show diffs
cd $OILPATH/tests
git diff generated_files/g_admin/
