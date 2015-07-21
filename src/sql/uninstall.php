<?php

return [

    'sqlite' => function() use ($models, $tables)
    {
        return [];
    },

    'mysql' => function() use ($models, $tables)
    {
        $q = [];

        $q[] = "DROP TABLE IF EXISTS `$tables[registration]`";
        $q[] = "DROP TABLE IF EXISTS `$tables[event]`";

        return $q;
    },

    'pgsql' => function() use ($models, $tables)
    {
        return [];
    },

];
