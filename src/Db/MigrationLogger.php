<?php

namespace Mrkrash\Estimate\Db;

class MigrationLogger implements \RedBeanPHP\Logger
{
    private String $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * @inheritDoc
     */
    public function log()
    {
        $query = func_get_arg(0);
        if (preg_match('/^(CREATE|ALTER)/', $query)) {
            file_put_contents($this->file, "{$query};\n");
        }
    }
}