# API v7 avec symfony

## mise en production
** install obamalou

` cd public_html/ins-bs/api

` git clone https://github.com/fafav45/apiv7.git

` mv apiv7 v7

` cd v7

` composer update

dans .env remplacer
APP_ENV=dev par APP_ENV=prod

` php bin/console cache:clear