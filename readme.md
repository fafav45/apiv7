# API v7 avec symfony

## mise en production
### install obamalou

``` cd public_html/ins-bs/api ```

``` git clone https://github.com/fafav45/apiv7.git ```

``` mv apiv7 v7```

``` cd v7 ```

``` composer install ```

### update obamalou

```git pull https://github.com/fafav45/apiv7.git ```

``` composer update ```

dans .env remplacer
APP_ENV=dev par APP_ENV=prod

``` php bin/console cache:clear --env=prod ```

#### problèmes
si fichiers modifiés sans passer par git, faire un
```git stash```

### install CND

``` opt/cpanel/ea-php73/root/usr/bin/php /opt/cpanel/composer/bin/composer install```
