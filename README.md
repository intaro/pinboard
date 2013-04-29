Intaro Pinboard
=============================

Intaro Pinboard is a simple web monitoring system which uses and aggregates data from [Pinba][1]. Developed on [Silex][2] framework.

Intaro Pinboard works with PHP 5.3.3 or later.

## Installation

1. Download application:

        $ git clone https://github.com/intaro/pinboard.git
        $ cd ./pinboard

2. Download [composer](http://getcomposer.org):

        $ curl -sS https://getcomposer.org/installer | php

3. Install dependency libraries throw composer:

        $ php composer.phar install

4. Create config file and enter parameters of connection to Pinba database:

        $ cp config/parameters.yml.dist config/parameters.yml
        $ nano config/parameters.yml

5. Initialize app (command will create additional tables and define crontab task):

        $ ./console init

6. Point the document root of your webserver or virtual host to the web/ directory. Read more in [Silex documentation][3]. Example for nginx + php-fpm:

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
                #uncomment when running via https
                #fastcgi_param HTTPS on;
            }
    
            location ~ /\.(ht|svn|git) {
                deny  all;
            }
        }

## More Information

Documentation in development.

## License

Intaro Pinboard is licensed under the MIT license.

[1]: http://pinba.org
[2]: http://silex.sensiolabs.org
[3]: http://silex.sensiolabs.org/doc/web_servers.html
