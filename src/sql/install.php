<?php

return [

    'sqlite' => function() use ($models, $tables)
    {
        return [];
    },

    'mysql' => function() use ($models, $tables)
    {
        $q = [];

        // Events

        $q[] = "CREATE TABLE IF NOT EXISTS `$tables[event]` (
        `id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        `begins_at` DATETIME NOT NULL,
        `ends_at` DATETIME,
        `title` VARCHAR(255),
        `info` TEXT,
        `options` TEXT
        );";

        $q[] = "CREATE INDEX `$tables[event]_id_idx` ON `$tables[event]` (`id`);";
        $q[] = "CREATE INDEX `$tables[event]_begins_at_ends_at_idx` ON `$tables[event]` (`begins_at`, `ends_at`);";

        // Registrations

        $q[] = "CREATE TABLE IF NOT EXISTS `$tables[registration]` (
        `id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `id_event` INTEGER NOT NULL REFERENCES `$tables[event]` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        `name` VARCHAR(64),
        `options` TEXT
        );";

        $q[] = "CREATE INDEX `$tables[registration]_id_idx` ON `$tables[registration]` (`id`);";
        $q[] = "CREATE INDEX `$tables[registration]_id_event_idx` ON `$tables[registration]` (`id_event`);";

        return $q;
    },

    'pgsql' => function() use ($models, $tables)
    {
        return [];
    },

];
