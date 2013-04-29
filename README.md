Intaro Pinboard
=============================

Intaro Pinboard is a simple web monitoring system which uses and aggregates data from Pinba. Developed on [Silex][1] framework.

Intaro Pinboard works with PHP 5.3.3 or later.

## Installation

1. Download [composer](http://getcomposer.org)

    $ curl -sS https://getcomposer.org/installer | php

2. Install dependency libraries throw composer

    $ php composer.phar install

3. Create config file and enter parameters of connection to Pinba database

    $ cp config/parameters.yml.dist config/parameters.yml
    $ nano config/parameters.yml

4. Initialize app (command will create additional tables and define crontab task)

    $ ./console init

## More Information

Documentation in development.

## License

Intaro Pinboard is licensed under the MIT license.

[1]: http://silex.sensiolabs.org
