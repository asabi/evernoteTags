# Evernote wildcard tag search

Provides wildcard searching of tags through an alfred workflow

#Install

Clone this repository into the root of your user folder (you can put it anywhere, but if you don't you will need to adjust the alfred workflow accordingly)

cd evernoteTags

Make sure you have composer installed:

curl -sS https://getcomposer.org/installer | php

Run

php composer.phar update

copy config.ini.template to config.ini , and add your evernote API token. You can get a new token by going to: https://dev.evernote.com/doc/articles/dev_tokens.php


You can quickly test the it works by running "php index.php refresh" from the command line. You should see some XML as the results. This will also build the database of tags for you.

Double click on the "Evernote Wildcard Tags Search.alfredworkflow" file in the root of the repository, that will add the workflow to Alfred.

#Using this in daily workflows

In alfred type: "tag {tag name}", the list of tags should update as you type.

Once the list shows, select the one you want, this will populate it in the application you currently have open, you can use it to populate tags in evernote when you process items with tags.

If you need to re-sync the list of tags with evernote, run "tag refresh" using Alfred, wait for a few seconds until it shows that it finished successfully - hopefully :-)
