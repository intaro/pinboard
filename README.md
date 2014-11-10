Intaro Pinboard
=============================

[Intaro Pinboard][1] (Pinba Board) is a realtime PHP monitoring system which aggregates and displays [Pinba][2] data.

![Intaro Pinboard](http://intaro.github.io/pinboard/img/main-img.png)


Developed on [Silex][3] framework and works with PHP 5.3.3 or later.

## Installation

1. Download application:

        $ git clone git://github.com/intaro/pinboard.git
        $ cd ./pinboard
        $ git checkout v1.3

2. Download [composer](http://getcomposer.org):

        $ curl -sS https://getcomposer.org/installer | php

3. Install dependency libraries through composer:

        $ php composer.phar install

4. Create config file and enter parameters of connection to Pinba database:

        $ cp config/parameters.yml.dist config/parameters.yml
        $ nano config/parameters.yml

5. Initialize app (command will create additional tables and define crontab task):

        $ ./console migrations:migrate
        $ ./console register-crontab

6. Point the document root of your webserver or virtual host to the web/ directory. Read more in [Silex documentation][4]. Example for nginx + php-fpm:

        server {
            listen 80;
            server_name pinboard.site.ru;
            root /var/www/pinboard/web;

            #site root is redirected to the app boot script
            location = / {
                try_files @site @site;
            }

            #all other locations try other files first and go to our front controller if none of them exists
            location / {
                try_files $uri $uri/ @site;
            }

            #return 404 for all php files as we do have a front controller
            location ~ \.php$ {
               return 404;
            }

            location @site {
                fastcgi_pass unix:/tmp/php-fpm.sock;
                include fastcgi_params;
                fastcgi_param  SCRIPT_FILENAME $document_root/index.php;
                fastcgi_param HTTPS $https if_not_empty;
            }

            location ~ /\.(ht|svn|git) {
                deny  all;
            }
        }

More details in section [Installation](http://github.com/intaro/pinboard/wiki/Installation) of the documentation.

## Update

### Update from 0.1 to 1.0

Branch 1.0 brings migrations machinery which allows to update Pinboard easy when it requires database schema transformation.

Switch to branch 1.0

    $ git fetch
    $ git checkout v1.0

Update vendors

    $ php composer.phar update

Register migration

    $ ./console migrations:version --add 20131013132150

### Update between 1.x versions

Switch to branch 1.x

    $ git fetch
    $ git checkout v1.x

Update vendors

    $ php composer.phar update

Apply changes to database

    $ ./console migrations:migrate

Add to `parameters.yml` new options from `parameters.yml.dist`.

## More Information

Documentation in [Wiki][5].

## License

Intaro Pinboard is licensed under the MIT license.

[1]: http://intaro.github.io/pinboard/
[2]: http://pinba.org
[3]: http://silex.sensiolabs.org
[4]: http://silex.sensiolabs.org/doc/web_servers.html
[5]: https://github.com/intaro/pinboard/wiki
