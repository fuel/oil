#!/bin/sh

cd `dirname $0`
cd ../../../..
TOPPATH=`pwd`
APPPATH="$TOPPATH/fuel/app"

cd $APPPATH

rm -f classes/controller/admin.php
rm -f classes/controller/admin/article.php
rm -f classes/controller/base.php
rm -f classes/model/article.php
rm -f migrations/001_create_articles.php
rm -f views/admin/article/_form.php
rm -f views/admin/article/create.php
rm -f views/admin/article/edit.php
rm -f views/admin/article/index.php
rm -f views/admin/article/view.php
rm -f views/admin/dashboard.php
rm -f views/admin/login.php
rm -f views/admin/template.php
rm -f views/template.php

rm -f classes/controller/article.php
rm -f classes/model/article.php
rm -f migrations/001_create_articles.php
rm -f views/article/_form.php
rm -f views/article/create.php
rm -f views/article/edit.php
rm -f views/article/index.php
rm -f views/article/view.php
rm -f views/template.php
