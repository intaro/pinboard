<?php

namespace Pinboard\Console;
use Symfony\Component\Console\Application as ConsoleApplication;

class Application extends ConsoleApplication
{
    protected $app = null;

    public function setSilex($app)
    {
        $this->app = $app;
    }

    public function getSilex()
    {
        return $this->app;
    }
}
