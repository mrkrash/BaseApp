<?php

namespace Mrkrash\Base\Config;

define('REDBEAN_MODEL_PREFIX', 'Mrkrash\\Base\\Model\\');

use Mrkrash\Base\Db\MigrationLogger;
use RedBeanPHP\R;
use RedBeanPHP\RedException;
use RedBeanPHP\ToolBox;

class ToolBoxFactory
{
    protected ToolBox $toolbox;
    private const DSN = 'sqlite:' . __DIR__ . '/../../db/Base.db';

    /**
     * @throws RedException
     */
    public function __invoke(): ToolBox
    {
        $this->toolbox = R::createToolbox(static::DSN);
        $this->toolbox->getDatabaseAdapter()
            ->getDatabase()
            ->setLogger((new MigrationLogger(sprintf('db/migration_%s.sql', date('Y-m-d')))))
            ->setEnableLogging(true);

        return $this->toolbox;
    }
}