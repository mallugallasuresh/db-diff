<?php
/**
 * DbDiff Engine
 * High-performance schema comparison and migration script generator for MySQL/MariaDB
 */

class MySQLComparator {

    /**
     * Test connection to a MySQL server
     */
    public static function testConnection(array $config): array {
        $startTime = microtime(true);
        try {
            $pdo = self::getConnection($config, false);
            $stmt = $pdo->query("SELECT VERSION() AS ver, DATABASE() AS cur_db");
            $info = $stmt->fetch(PDO::FETCH_ASSOC);
            $elapsedMs = round((microtime(true) - $startTime) * 1000, 2);

            // Fetch list of databases
            $databases = [];
            try {
                $dbStmt = $pdo->query("SHOW DATABASES");
                while ($row = $dbStmt->fetch(PDO::FETCH_NUM)) {
                    $dbName = $row[0];
                    if (!in_array($dbName, ['information_schema', 'performance_schema', 'mysql', 'sys'])) {
                        $databases[] = $dbName;
                    }
                }
                sort($databases);
            } catch (Exception $e) {
                // Ignore if SHOW DATABASES permission is restricted
            }

            return [
                'success' => true,
                'message' => 'Connection successful!',
                'version' => $info['ver'] ?? 'Unknown',
                'latency_ms' => $elapsedMs,
                'databases' => $databases,
                'selected_db' => $config['database'] ?? ''
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }

    /**
     * Get PDO connection
     */
    public static function getConnection(array $config, bool $includeDatabase = true): PDO {
        $host = trim($config['server'] ?? '127.0.0.1');
        $port = intval($config['port'] ?? 3306);
        $user = trim($config['username'] ?? 'root');
        $pass = $config['password'] ?? '';
        $database = trim($config['database'] ?? '');

        if (empty($host)) {
            throw new InvalidArgumentException("Server host cannot be empty.");
        }

        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        if ($includeDatabase && !empty($database)) {
            $dsn .= ";dbname={$database}";
        }

        $initCommandAttr = defined('Pdo\Mysql::ATTR_INIT_COMMAND') 
            ? constant('Pdo\Mysql::ATTR_INIT_COMMAND') 
            : (defined('PDO::MYSQL_ATTR_INIT_COMMAND') ? PDO::MYSQL_ATTR_INIT_COMMAND : 1002);

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 6, // 6 seconds connection timeout
            $initCommandAttr => "SET NAMES utf8mb4"
        ];

        return new PDO($dsn, $user, $pass, $options);
    }

    /**
     * Extract comprehensive schema metadata from a database
     */
    public static function extractSchema(array $config, array $options = []): array {
        $pdo = self::getConnection($config, true);
        $dbName = trim($config['database']);

        $schema = [
            'database' => $dbName,
            'server' => $config['server'],
            'port' => $config['port'] ?? 3306,
            'tables' => [],
            'views' => [],
            'routines' => [],
            'triggers' => []
        ];

        // 1. Fetch tables
        $sql = "SELECT 
                    TABLE_NAME, 
                    TABLE_TYPE, 
                    ENGINE, 
                    TABLE_COLLATION, 
                    TABLE_ROWS, 
                    AUTO_INCREMENT, 
                    TABLE_COMMENT
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = :schema
                ORDER BY TABLE_NAME";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':schema' => $dbName]);
        $tablesRaw = $stmt->fetchAll();

        foreach ($tablesRaw as $t) {
            $tableName = $t['TABLE_NAME'];
            if ($t['TABLE_TYPE'] === 'VIEW') {
                $schema['views'][$tableName] = [
                    'name' => $tableName,
                    'type' => 'VIEW'
                ];
                continue;
            }

            $schema['tables'][$tableName] = [
                'name' => $tableName,
                'engine' => $t['ENGINE'] ?? 'InnoDB',
                'collation' => $t['TABLE_COLLATION'] ?? '',
                'rows' => (int)($t['TABLE_ROWS'] ?? 0),
                'comment' => $t['TABLE_COMMENT'] ?? '',
                'columns' => [],
                'primary_key' => [],
                'indexes' => [],
                'foreign_keys' => [],
                'create_table_sql' => ''
            ];
        }

        // 2. Fetch Columns
        $sql = "SELECT 
                    TABLE_NAME,
                    COLUMN_NAME,
                    ORDINAL_POSITION,
                    COLUMN_DEFAULT,
                    IS_NULLABLE,
                    DATA_TYPE,
                    COLUMN_TYPE,
                    CHARACTER_MAXIMUM_LENGTH,
                    NUMERIC_PRECISION,
                    NUMERIC_SCALE,
                    CHARACTER_SET_NAME,
                    COLLATION_NAME,
                    COLUMN_KEY,
                    EXTRA,
                    COLUMN_COMMENT
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = :schema
                ORDER BY TABLE_NAME, ORDINAL_POSITION";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':schema' => $dbName]);
        $columnsRaw = $stmt->fetchAll();

        foreach ($columnsRaw as $col) {
            $tbl = $col['TABLE_NAME'];
            if (!isset($schema['tables'][$tbl])) {
                continue;
            }

            $colName = $col['COLUMN_NAME'];
            $schema['tables'][$tbl]['columns'][$colName] = [
                'name' => $colName,
                'position' => (int)$col['ORDINAL_POSITION'],
                'type' => $col['DATA_TYPE'],
                'column_type' => $col['COLUMN_TYPE'],
                'nullable' => strtoupper($col['IS_NULLABLE']) === 'YES',
                'default' => $col['COLUMN_DEFAULT'],
                'extra' => strtolower($col['EXTRA'] ?? ''),
                'collation' => $col['COLLATION_NAME'],
                'charset' => $col['CHARACTER_SET_NAME'],
                'key' => $col['COLUMN_KEY'],
                'comment' => $col['COLUMN_COMMENT'] ?? ''
            ];
        }

        // 3. Fetch Indexes
        $sql = "SELECT 
                    TABLE_NAME,
                    INDEX_NAME,
                    NON_UNIQUE,
                    SEQ_IN_INDEX,
                    COLUMN_NAME,
                    COLLATION,
                    SUB_PART,
                    INDEX_TYPE,
                    INDEX_COMMENT
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = :schema
                ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':schema' => $dbName]);
        $indexesRaw = $stmt->fetchAll();

        foreach ($indexesRaw as $idx) {
            $tbl = $idx['TABLE_NAME'];
            if (!isset($schema['tables'][$tbl])) {
                continue;
            }

            $idxName = $idx['INDEX_NAME'];
            $colName = $idx['COLUMN_NAME'];

            if ($idxName === 'PRIMARY') {
                $schema['tables'][$tbl]['primary_key'][] = $colName;
                continue;
            }

            if (!isset($schema['tables'][$tbl]['indexes'][$idxName])) {
                $schema['tables'][$tbl]['indexes'][$idxName] = [
                    'name' => $idxName,
                    'unique' => ((int)$idx['NON_UNIQUE'] === 0),
                    'type' => $idx['INDEX_TYPE'] ?? 'BTREE',
                    'columns' => []
                ];
            }
            $schema['tables'][$tbl]['indexes'][$idxName]['columns'][] = [
                'name' => $colName,
                'sub_part' => $idx['SUB_PART']
            ];
        }

        // 4. Fetch Foreign Keys
        $sql = "SELECT 
                    kcu.TABLE_NAME,
                    kcu.CONSTRAINT_NAME,
                    kcu.COLUMN_NAME,
                    kcu.ORDINAL_POSITION,
                    kcu.REFERENCED_TABLE_NAME,
                    kcu.REFERENCED_COLUMN_NAME,
                    rc.UPDATE_RULE,
                    rc.DELETE_RULE
                FROM information_schema.KEY_COLUMN_USAGE kcu
                LEFT JOIN information_schema.REFERENTIAL_CONSTRAINTS rc 
                    ON kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA 
                    AND kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME 
                    AND kcu.TABLE_NAME = rc.TABLE_NAME
                WHERE kcu.TABLE_SCHEMA = :schema 
                    AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
                ORDER BY kcu.TABLE_NAME, kcu.CONSTRAINT_NAME, kcu.ORDINAL_POSITION";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':schema' => $dbName]);
        $fkRaw = $stmt->fetchAll();

        foreach ($fkRaw as $fk) {
            $tbl = $fk['TABLE_NAME'];
            if (!isset($schema['tables'][$tbl])) {
                continue;
            }

            $cName = $fk['CONSTRAINT_NAME'];
            if (!isset($schema['tables'][$tbl]['foreign_keys'][$cName])) {
                $schema['tables'][$tbl]['foreign_keys'][$cName] = [
                    'name' => $cName,
                    'columns' => [],
                    'referenced_table' => $fk['REFERENCED_TABLE_NAME'],
                    'referenced_columns' => [],
                    'on_update' => $fk['UPDATE_RULE'] ?? 'RESTRICT',
                    'on_delete' => $fk['DELETE_RULE'] ?? 'RESTRICT'
                ];
            }
            $schema['tables'][$tbl]['foreign_keys'][$cName]['columns'][] = $fk['COLUMN_NAME'];
            $schema['tables'][$tbl]['foreign_keys'][$cName]['referenced_columns'][] = $fk['REFERENCED_COLUMN_NAME'];
        }

        // 5. Get SHOW CREATE TABLE for missing table regeneration
        foreach ($schema['tables'] as $tbl => &$tblMeta) {
            try {
                $createStmt = $pdo->query("SHOW CREATE TABLE `{$dbName}`.`{$tbl}`");
                $createRow = $createStmt->fetch(PDO::FETCH_NUM);
                if (!empty($createRow[1])) {
                    $tblMeta['create_table_sql'] = $createRow[1];
                }
            } catch (Exception $e) {
                // Skip if error
            }
        }
        unset($tblMeta);

        // 6. Optional: Views, Triggers, Routines
        if (!empty($options['compare_views'])) {
            $vSql = "SELECT TABLE_NAME, VIEW_DEFINITION FROM information_schema.VIEWS WHERE TABLE_SCHEMA = :schema";
            $vStmt = $pdo->prepare($vSql);
            $vStmt->execute([':schema' => $dbName]);
            while ($v = $vStmt->fetch()) {
                $schema['views'][$v['TABLE_NAME']] = [
                    'name' => $v['TABLE_NAME'],
                    'definition' => $v['VIEW_DEFINITION']
                ];
            }
        }

        if (!empty($options['compare_triggers'])) {
            $tSql = "SELECT TRIGGER_NAME, EVENT_MANIPULATION, EVENT_OBJECT_TABLE, ACTION_TIMING, ACTION_STATEMENT 
                     FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = :schema";
            $tStmt = $pdo->prepare($tSql);
            $tStmt->execute([':schema' => $dbName]);
            while ($trig = $tStmt->fetch()) {
                $schema['triggers'][$trig['TRIGGER_NAME']] = $trig;
            }
        }

        if (!empty($options['compare_routines'])) {
            $rSql = "SELECT ROUTINE_NAME, ROUTINE_TYPE, ROUTINE_DEFINITION 
                     FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = :schema";
            $rStmt = $pdo->prepare($rSql);
            $rStmt->execute([':schema' => $dbName]);
            while ($rt = $rStmt->fetch()) {
                $schema['routines'][$rt['ROUTINE_NAME']] = $rt;
            }
        }

        return $schema;
    }

    /**
     * Compare Source schema vs Target schema
     */
    public static function compare(array $sourceSchema, array $targetSchema, array $options = []): array {
        $ignoreIntWidth = !empty($options['ignore_int_width']);
        $ignoreCollation = !empty($options['ignore_collation']);
        $ignoreComments = !empty($options['ignore_comments']);

        $sourceTables = array_keys($sourceSchema['tables']);
        $targetTables = array_keys($targetSchema['tables']);

        $missingInTarget = array_values(array_diff($sourceTables, $targetTables));
        $extraInTarget = array_values(array_diff($targetTables, $sourceTables));
        $commonTables = array_values(array_intersect($sourceTables, $targetTables));

        $tablesComparison = [];
        $totalDifferences = 0;
        $identicalCount = 0;
        $mismatchedCount = 0;

        // Process Missing in Target tables
        foreach ($missingInTarget as $tbl) {
            $totalDifferences++;
            $tablesComparison[$tbl] = [
                'name' => $tbl,
                'status' => 'missing_in_target',
                'badge' => 'Missing in Target',
                'source_rows' => $sourceSchema['tables'][$tbl]['rows'] ?? 0,
                'target_rows' => 0,
                'differences_count' => 1,
                'create_sql' => $sourceSchema['tables'][$tbl]['create_table_sql'] ?? '',
                'migration_sql' => ($sourceSchema['tables'][$tbl]['create_table_sql'] ?? '') . ";",
                'columns' => [],
                'indexes' => [],
                'foreign_keys' => [],
                'options' => []
            ];
        }

        // Process Extra in Target tables
        foreach ($extraInTarget as $tbl) {
            $totalDifferences++;
            $tablesComparison[$tbl] = [
                'name' => $tbl,
                'status' => 'extra_in_target',
                'badge' => 'Extra in Target',
                'source_rows' => 0,
                'target_rows' => $targetSchema['tables'][$tbl]['rows'] ?? 0,
                'differences_count' => 1,
                'migration_sql' => "-- Table exists only in Target:\n-- DROP TABLE IF EXISTS `{$tbl}`;",
                'columns' => [],
                'indexes' => [],
                'foreign_keys' => [],
                'options' => []
            ];
        }

        // Deep Comparison for Common Tables
        foreach ($commonTables as $tbl) {
            $srcTable = $sourceSchema['tables'][$tbl];
            $tgtTable = $targetSchema['tables'][$tbl];

            $tblDiffCount = 0;
            $colDiffs = [];
            $idxDiffs = [];
            $fkDiffs = [];
            $optDiffs = [];
            $migrationSqlParts = [];

            // A. Compare Columns
            $srcCols = array_keys($srcTable['columns']);
            $tgtCols = array_keys($tgtTable['columns']);

            $missingColsInTarget = array_values(array_diff($srcCols, $tgtCols));
            $extraColsInTarget = array_values(array_diff($tgtCols, $srcCols));
            $commonCols = array_values(array_intersect($srcCols, $tgtCols));

            // Missing Columns in Target
            foreach ($missingColsInTarget as $colName) {
                $tblDiffCount++;
                $srcCol = $srcTable['columns'][$colName];
                
                // Determine position
                $positionClause = self::getColumnPositionClause($srcTable['columns'], $colName);
                $colDef = self::buildColumnDefinition($srcCol);
                $migrationSqlParts[] = "ALTER TABLE `{$tbl}` ADD COLUMN `{$colName}` {$colDef} {$positionClause};";

                $colDiffs[$colName] = [
                    'name' => $colName,
                    'status' => 'missing_in_target',
                    'message' => 'Missing in Target table',
                    'source' => $srcCol,
                    'target' => null,
                    'diff_details' => ['Column missing in Target database']
                ];
            }

            // Extra Columns in Target
            foreach ($extraColsInTarget as $colName) {
                $tblDiffCount++;
                $tgtCol = $tgtTable['columns'][$colName];
                $migrationSqlParts[] = "-- ALTER TABLE `{$tbl}` DROP COLUMN `{$colName}`; -- (Caution: Extra column in Target)";

                $colDiffs[$colName] = [
                    'name' => $colName,
                    'status' => 'extra_in_target',
                    'message' => 'Extra in Target (not in Source)',
                    'source' => null,
                    'target' => $tgtCol,
                    'diff_details' => ['Column exists only in Target database']
                ];
            }

            // Compare common columns
            foreach ($commonCols as $colName) {
                $srcCol = $srcTable['columns'][$colName];
                $tgtCol = $tgtTable['columns'][$colName];
                $diffDetails = [];

                // Compare column types
                $srcType = strtolower($srcCol['column_type']);
                $tgtType = strtolower($tgtCol['column_type']);

                if ($ignoreIntWidth) {
                    $srcTypeNorm = preg_replace('/^(tinyint|smallint|mediumint|int|bigint)\(\d+\)/', '$1', $srcType);
                    $tgtTypeNorm = preg_replace('/^(tinyint|smallint|mediumint|int|bigint)\(\d+\)/', '$1', $tgtType);
                } else {
                    $srcTypeNorm = $srcType;
                    $tgtTypeNorm = $tgtType;
                }

                if ($srcTypeNorm !== $tgtTypeNorm) {
                    $diffDetails[] = "Type: Source [{$srcCol['column_type']}] vs Target [{$tgtCol['column_type']}]";
                }

                // Nullability
                if ($srcCol['nullable'] !== $tgtCol['nullable']) {
                    $srcNull = $srcCol['nullable'] ? 'NULL' : 'NOT NULL';
                    $tgtNull = $tgtCol['nullable'] ? 'NULL' : 'NOT NULL';
                    $diffDetails[] = "Nullable: Source [{$srcNull}] vs Target [{$tgtNull}]";
                }

                // Default value normalization
                $srcDef = self::normalizeDefaultValue($srcCol['default']);
                $tgtDef = self::normalizeDefaultValue($tgtCol['default']);
                if ($srcDef !== $tgtDef) {
                    $diffDetails[] = "Default: Source [" . ($srcCol['default'] ?? 'NULL') . "] vs Target [" . ($tgtCol['default'] ?? 'NULL') . "]";
                }

                // Extra (auto_increment, on update CURRENT_TIMESTAMP)
                if (strtolower(trim($srcCol['extra'])) !== strtolower(trim($tgtCol['extra']))) {
                    $diffDetails[] = "Extra: Source [{$srcCol['extra']}] vs Target [{$tgtCol['extra']}]";
                }

                // Collation (if not ignored and applicable)
                if (!$ignoreCollation && !empty($srcCol['collation']) && !empty($tgtCol['collation'])) {
                    if ($srcCol['collation'] !== $tgtCol['collation']) {
                        $diffDetails[] = "Collation: Source [{$srcCol['collation']}] vs Target [{$tgtCol['collation']}]";
                    }
                }

                // Comment
                if (!$ignoreComments && ($srcCol['comment'] !== $tgtCol['comment'])) {
                    $diffDetails[] = "Comment: Source [{$srcCol['comment']}] vs Target [{$tgtCol['comment']}]";
                }

                if (!empty($diffDetails)) {
                    $tblDiffCount++;
                    $colDef = self::buildColumnDefinition($srcCol);
                    $migrationSqlParts[] = "ALTER TABLE `{$tbl}` MODIFY COLUMN `{$colName}` {$colDef};";

                    $colDiffs[$colName] = [
                        'name' => $colName,
                        'status' => 'modified',
                        'message' => 'Column specification mismatch',
                        'source' => $srcCol,
                        'target' => $tgtCol,
                        'diff_details' => $diffDetails
                    ];
                } else {
                    $colDiffs[$colName] = [
                        'name' => $colName,
                        'status' => 'identical',
                        'message' => 'Identical',
                        'source' => $srcCol,
                        'target' => $tgtCol,
                        'diff_details' => []
                    ];
                }
            }

            // B. Compare Primary Key
            $srcPk = $srcTable['primary_key'];
            $tgtPk = $tgtTable['primary_key'];

            if ($srcPk !== $tgtPk) {
                $tblDiffCount++;
                $pkDiff = [
                    'source_pk' => $srcPk,
                    'target_pk' => $tgtPk,
                    'message' => 'Primary Key mismatch'
                ];

                if (!empty($tgtPk) && !empty($srcPk)) {
                    $srcPkCols = '`' . implode('`, `', $srcPk) . '`';
                    $migrationSqlParts[] = "ALTER TABLE `{$tbl}` DROP PRIMARY KEY, ADD PRIMARY KEY ({$srcPkCols});";
                } elseif (!empty($srcPk)) {
                    $srcPkCols = '`' . implode('`, `', $srcPk) . '`';
                    $migrationSqlParts[] = "ALTER TABLE `{$tbl}` ADD PRIMARY KEY ({$srcPkCols});";
                } elseif (!empty($tgtPk)) {
                    $migrationSqlParts[] = "-- ALTER TABLE `{$tbl}` DROP PRIMARY KEY;";
                }
                $idxDiffs['PRIMARY'] = [
                    'name' => 'PRIMARY KEY',
                    'status' => 'modified',
                    'source' => $srcPk ? implode(', ', $srcPk) : 'None',
                    'target' => $tgtPk ? implode(', ', $tgtPk) : 'None',
                    'diff_details' => ['Primary key column definition differs']
                ];
            }

            // C. Compare Indexes
            $srcIndexes = $srcTable['indexes'];
            $tgtIndexes = $tgtTable['indexes'];

            $srcIdxNames = array_keys($srcIndexes);
            $tgtIdxNames = array_keys($tgtIndexes);

            // Missing indexes in Target
            foreach (array_diff($srcIdxNames, $tgtIdxNames) as $idxName) {
                $tblDiffCount++;
                $idx = $srcIndexes[$idxName];
                $idxCols = array_map(function($c) {
                    $part = "`{$c['name']}`";
                    if (!empty($c['sub_part'])) {
                        $part .= "({$c['sub_part']})";
                    }
                    return $part;
                }, $idx['columns']);

                $type = $idx['unique'] ? 'UNIQUE INDEX' : 'INDEX';
                $migrationSqlParts[] = "ALTER TABLE `{$tbl}` ADD {$type} `{$idxName}` (" . implode(', ', $idxCols) . ");";

                $idxDiffs[$idxName] = [
                    'name' => $idxName,
                    'status' => 'missing_in_target',
                    'source' => ($idx['unique'] ? 'UNIQUE ' : '') . implode(', ', array_column($idx['columns'], 'name')),
                    'target' => 'Missing',
                    'diff_details' => ['Index missing in Target table']
                ];
            }

            // Extra indexes in Target
            foreach (array_diff($tgtIdxNames, $srcIdxNames) as $idxName) {
                $tblDiffCount++;
                $idx = $tgtIndexes[$idxName];
                $migrationSqlParts[] = "-- ALTER TABLE `{$tbl}` DROP INDEX `{$idxName}`; -- (Extra index in Target)";

                $idxDiffs[$idxName] = [
                    'name' => $idxName,
                    'status' => 'extra_in_target',
                    'source' => 'None',
                    'target' => ($idx['unique'] ? 'UNIQUE ' : '') . implode(', ', array_column($idx['columns'], 'name')),
                    'diff_details' => ['Index exists only in Target table']
                ];
            }

            // Common indexes check
            foreach (array_intersect($srcIdxNames, $tgtIdxNames) as $idxName) {
                $sIdx = $srcIndexes[$idxName];
                $tIdx = $tgtIndexes[$idxName];

                $sCols = array_column($sIdx['columns'], 'name');
                $tCols = array_column($tIdx['columns'], 'name');

                $mismatch = false;
                $details = [];

                if ($sIdx['unique'] !== $tIdx['unique']) {
                    $mismatch = true;
                    $details[] = "Uniqueness: Source [" . ($sIdx['unique'] ? 'UNIQUE' : 'NON-UNIQUE') . "] vs Target [" . ($tIdx['unique'] ? 'UNIQUE' : 'NON-UNIQUE') . "]";
                }

                if ($sCols !== $tCols) {
                    $mismatch = true;
                    $details[] = "Columns: Source [" . implode(', ', $sCols) . "] vs Target [" . implode(', ', $tCols) . "]";
                }

                if ($mismatch) {
                    $tblDiffCount++;
                    $idxCols = array_map(function($c) { return "`{$c['name']}`"; }, $sIdx['columns']);
                    $type = $sIdx['unique'] ? 'UNIQUE INDEX' : 'INDEX';
                    $migrationSqlParts[] = "ALTER TABLE `{$tbl}` DROP INDEX `{$idxName}`, ADD {$type} `{$idxName}` (" . implode(', ', $idxCols) . ");";

                    $idxDiffs[$idxName] = [
                        'name' => $idxName,
                        'status' => 'modified',
                        'source' => ($sIdx['unique'] ? 'UNIQUE ' : '') . implode(', ', $sCols),
                        'target' => ($tIdx['unique'] ? 'UNIQUE ' : '') . implode(', ', $tCols),
                        'diff_details' => $details
                    ];
                }
            }

            // D. Compare Foreign Keys
            $srcFks = $srcTable['foreign_keys'];
            $tgtFks = $tgtTable['foreign_keys'];

            $srcFkNames = array_keys($srcFks);
            $tgtFkNames = array_keys($tgtFks);

            // Missing FKs in target
            foreach (array_diff($srcFkNames, $tgtFkNames) as $fkName) {
                $tblDiffCount++;
                $fk = $srcFks[$fkName];
                $fkCols = '`' . implode('`, `', $fk['columns']) . '`';
                $refCols = '`' . implode('`, `', $fk['referenced_columns']) . '`';
                $migrationSqlParts[] = "ALTER TABLE `{$tbl}` ADD CONSTRAINT `{$fkName}` FOREIGN KEY ({$fkCols}) REFERENCES `{$fk['referenced_table']}` ({$refCols}) ON DELETE {$fk['on_delete']} ON UPDATE {$fk['on_update']};";

                $fkDiffs[$fkName] = [
                    'name' => $fkName,
                    'status' => 'missing_in_target',
                    'source' => "FK ({$fkCols}) -> {$fk['referenced_table']}({$refCols})",
                    'target' => 'Missing',
                    'diff_details' => ['Foreign Key missing in Target']
                ];
            }

            // Extra FKs in target
            foreach (array_diff($tgtFkNames, $srcFkNames) as $fkName) {
                $tblDiffCount++;
                $fk = $tgtFks[$fkName];
                $migrationSqlParts[] = "-- ALTER TABLE `{$tbl}` DROP FOREIGN KEY `{$fkName}`; -- (Extra FK in Target)";

                $fkDiffs[$fkName] = [
                    'name' => $fkName,
                    'status' => 'extra_in_target',
                    'source' => 'None',
                    'target' => "FK on Target",
                    'diff_details' => ['Foreign Key exists only in Target']
                ];
            }

            // E. Table Options (Engine & Collation)
            if (strcasecmp($srcTable['engine'], $tgtTable['engine']) !== 0) {
                $tblDiffCount++;
                $optDiffs['engine'] = [
                    'property' => 'Storage Engine',
                    'source' => $srcTable['engine'],
                    'target' => $tgtTable['engine']
                ];
                $migrationSqlParts[] = "ALTER TABLE `{$tbl}` ENGINE = {$srcTable['engine']};";
            }

            if (!$ignoreCollation && !empty($srcTable['collation']) && !empty($tgtTable['collation'])) {
                if (strcasecmp($srcTable['collation'], $tgtTable['collation']) !== 0) {
                    $tblDiffCount++;
                    $optDiffs['collation'] = [
                        'property' => 'Table Collation',
                        'source' => $srcTable['collation'],
                        'target' => $tgtTable['collation']
                    ];
                    $migrationSqlParts[] = "ALTER TABLE `{$tbl}` COLLATE = '{$srcTable['collation']}';";
                }
            }

            // Determine status
            if ($tblDiffCount > 0) {
                $totalDifferences += $tblDiffCount;
                $mismatchedCount++;
                $status = 'modified';
                $badge = 'Schema Mismatch';
            } else {
                $identicalCount++;
                $status = 'identical';
                $badge = 'Identical';
            }

            $tablesComparison[$tbl] = [
                'name' => $tbl,
                'status' => $status,
                'badge' => $badge,
                'source_rows' => $srcTable['rows'] ?? 0,
                'target_rows' => $tgtTable['rows'] ?? 0,
                'differences_count' => $tblDiffCount,
                'columns' => $colDiffs,
                'indexes' => $idxDiffs,
                'foreign_keys' => $fkDiffs,
                'options' => $optDiffs,
                'migration_sql' => implode("\n", $migrationSqlParts)
            ];
        }

        // Sort tables: missing in target first, then modified, then extra in target, then identical
        uasort($tablesComparison, function($a, $b) {
            $priority = [
                'missing_in_target' => 1,
                'modified' => 2,
                'extra_in_target' => 3,
                'identical' => 4
            ];
            $pA = $priority[$a['status']] ?? 99;
            $pB = $priority[$b['status']] ?? 99;
            if ($pA !== $pB) {
                return $pA <=> $pB;
            }
            return strcasecmp($a['name'], $b['name']);
        });

        // Generate full unified Migration SQL
        $fullMigrationSql = self::generateFullMigrationSql(
            $sourceSchema, 
            $targetSchema, 
            $tablesComparison,
            $options
        );

        return [
            'summary' => [
                'source_database' => $sourceSchema['database'],
                'target_database' => $targetSchema['database'],
                'source_server' => $sourceSchema['server'] . ':' . $sourceSchema['port'],
                'target_server' => $targetSchema['server'] . ':' . $targetSchema['port'],
                'total_source_tables' => count($sourceTables),
                'total_target_tables' => count($targetTables),
                'missing_in_target_count' => count($missingInTarget),
                'extra_in_target_count' => count($extraInTarget),
                'modified_tables_count' => $mismatchedCount,
                'identical_tables_count' => $identicalCount,
                'total_differences_count' => $totalDifferences,
                'compared_at' => date('Y-m-d H:i:s')
            ],
            'tables' => $tablesComparison,
            'migration_sql' => $fullMigrationSql
        ];
    }

    /**
     * Build unified migration script
     */
    private static function generateFullMigrationSql(array $sourceSchema, array $targetSchema, array $tables, array $options): string {
        $sql = [];
        $sql[] = "-- ====================================================================";
        $sql[] = "-- MySQL Schema Synchronization Script";
        $sql[] = "-- Source: " . $sourceSchema['database'] . " (" . $sourceSchema['server'] . ")";
        $sql[] = "-- Target: " . $targetSchema['database'] . " (" . $targetSchema['server'] . ")";
        $sql[] = "-- Generated At: " . date('Y-m-d H:i:s');
        $sql[] = "-- ====================================================================\n";

        $sql[] = "SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0;";
        $sql[] = "SET @OLD_SQL_MODE = @@SQL_MODE, SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";

        // 1. Missing Tables (CREATE TABLE)
        $hasMissingTables = false;
        foreach ($tables as $tName => $tData) {
            if ($tData['status'] === 'missing_in_target') {
                if (!$hasMissingTables) {
                    $sql[] = "-- --------------------------------------------------------------------";
                    $sql[] = "-- 1. CREATE MISSING TABLES IN TARGET";
                    $sql[] = "-- --------------------------------------------------------------------";
                    $hasMissingTables = true;
                }
                if (!empty($tData['create_sql'])) {
                    $sql[] = $tData['create_sql'] . ";\n";
                }
            }
        }

        // 2. Table Modifications (ADD/MODIFY columns, indexes, FKs)
        $hasAlters = false;
        foreach ($tables as $tName => $tData) {
            if ($tData['status'] === 'modified' && !empty($tData['migration_sql'])) {
                if (!$hasAlters) {
                    $sql[] = "-- --------------------------------------------------------------------";
                    $sql[] = "-- 2. ALTER EXISTING TABLES IN TARGET";
                    $sql[] = "-- --------------------------------------------------------------------";
                    $hasAlters = true;
                }
                $sql[] = "-- Table: `{$tName}`";
                $sql[] = $tData['migration_sql'] . "\n";
            }
        }

        // 3. Extra Tables in Target (Commented out safe notice)
        $hasExtra = false;
        foreach ($tables as $tName => $tData) {
            if ($tData['status'] === 'extra_in_target') {
                if (!$hasExtra) {
                    $sql[] = "-- --------------------------------------------------------------------";
                    $sql[] = "-- 3. EXTRA TABLES IN TARGET (COMMENTED OUT FOR SAFETY)";
                    $sql[] = "-- --------------------------------------------------------------------";
                    $hasExtra = true;
                }
                $sql[] = "-- DROP TABLE IF EXISTS `{$tName}`;";
            }
        }

        $sql[] = "\nSET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;";
        $sql[] = "SET SQL_MODE = @OLD_SQL_MODE;";
        $sql[] = "-- End of Sync Script";

        return implode("\n", $sql);
    }

    /**
     * Helper to get column position clause (FIRST or AFTER prev_column)
     */
    private static function getColumnPositionClause(array $sourceColumns, string $colName): string {
        $keys = array_keys($sourceColumns);
        $idx = array_search($colName, $keys);
        if ($idx === 0 || $idx === false) {
            return "FIRST";
        }
        $prevCol = $keys[$idx - 1];
        return "AFTER `{$prevCol}`";
    }

    /**
     * Build standard column definition for ALTER TABLE
     */
    private static function buildColumnDefinition(array $col): string {
        $parts = [$col['column_type']];

        if (!empty($col['charset']) && in_array(strtolower($col['type']), ['char', 'varchar', 'text', 'tinytext', 'mediumtext', 'longtext'])) {
            $parts[] = "CHARACTER SET {$col['charset']}";
        }
        if (!empty($col['collation']) && in_array(strtolower($col['type']), ['char', 'varchar', 'text', 'tinytext', 'mediumtext', 'longtext'])) {
            $parts[] = "COLLATE {$col['collation']}";
        }

        if ($col['nullable']) {
            $parts[] = "NULL";
        } else {
            $parts[] = "NOT NULL";
        }

        if ($col['default'] !== null) {
            $def = $col['default'];
            if (in_array(strtoupper($def), ['CURRENT_TIMESTAMP', 'NOW()', 'CURRENT_TIMESTAMP()'])) {
                $parts[] = "DEFAULT {$def}";
            } elseif (is_numeric($def)) {
                $parts[] = "DEFAULT {$def}";
            } else {
                $parts[] = "DEFAULT '" . addslashes($def) . "'";
            }
        } elseif ($col['nullable']) {
            $parts[] = "DEFAULT NULL";
        }

        if (!empty($col['extra'])) {
            $parts[] = strtoupper($col['extra']);
        }

        if (!empty($col['comment'])) {
            $parts[] = "COMMENT '" . addslashes($col['comment']) . "'";
        }

        return implode(' ', $parts);
    }

    /**
     * Normalize default value for reliable comparison
     */
    private static function normalizeDefaultValue($val): ?string {
        if ($val === null) {
            return null;
        }
        $val = trim((string)$val);
        if (strcasecmp($val, 'NULL') === 0) {
            return null;
        }
        if (strcasecmp($val, 'current_timestamp()') === 0 || strcasecmp($val, 'current_timestamp') === 0) {
            return 'CURRENT_TIMESTAMP';
        }
        return $val;
    }
}
