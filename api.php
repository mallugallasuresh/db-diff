<?php
/**
 * DbDiff - Fast MySQL Schema Diff & Migration API Router
 */

header('Content-Type: application/json; charset=utf-8');

// Error handling: catch fatal errors and return clean JSON
ini_set('display_errors', '0');
error_reporting(E_ALL);

set_exception_handler(function($e) {
    echo json_encode([
        'success' => false,
        'message' => 'Exception: ' . $e->getMessage()
    ]);
    exit;
});

require_once __DIR__ . '/comparator.class.php';
require_once __DIR__ . '/demo_data.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Support JSON input payloads
$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true) ?? [];
$params = array_merge($_REQUEST, $jsonInput);

switch ($action) {

    // 1. Test single connection
    case 'test_connection':
        $config = [
            'server' => trim($params['server'] ?? ''),
            'port' => intval($params['port'] ?? 3306),
            'username' => trim($params['username'] ?? ''),
            'password' => $params['password'] ?? '',
            'database' => trim($params['database'] ?? '')
        ];

        if (empty($config['server'])) {
            echo json_encode(['success' => false, 'message' => 'Server address is required.']);
            exit;
        }

        $res = MySQLComparator::testConnection($config);
        echo json_encode($res);
        exit;

    // 2. Fetch list of databases on a server
    case 'get_databases':
        $config = [
            'server' => trim($params['server'] ?? ''),
            'port' => intval($params['port'] ?? 3306),
            'username' => trim($params['username'] ?? ''),
            'password' => $params['password'] ?? ''
        ];

        $res = MySQLComparator::testConnection($config);
        if ($res['success']) {
            echo json_encode([
                'success' => true,
                'databases' => $res['databases'] ?? []
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $res['message']
            ]);
        }
        exit;

    // 3. Compare two databases
    case 'compare':
        $source = [
            'server' => trim($params['source_server'] ?? ''),
            'port' => intval($params['source_port'] ?? 3306),
            'username' => trim($params['source_username'] ?? ''),
            'password' => $params['source_password'] ?? '',
            'database' => trim($params['source_database'] ?? '')
        ];

        $target = [
            'server' => trim($params['target_server'] ?? ''),
            'port' => intval($params['target_port'] ?? 3306),
            'username' => trim($params['target_username'] ?? ''),
            'password' => $params['target_password'] ?? '',
            'database' => trim($params['target_database'] ?? '')
        ];

        // Validations
        if (empty($source['server']) || empty($source['database'])) {
            echo json_encode(['success' => false, 'message' => 'Source server and database name are required.']);
            exit;
        }
        if (empty($target['server']) || empty($target['database'])) {
            echo json_encode(['success' => false, 'message' => 'Target server and database name are required.']);
            exit;
        }

        $options = [
            'ignore_int_width' => !empty($params['ignore_int_width']),
            'ignore_collation' => !empty($params['ignore_collation']),
            'ignore_comments' => !empty($params['ignore_comments']),
            'compare_views' => !empty($params['compare_views']),
            'compare_triggers' => !empty($params['compare_triggers']),
            'compare_routines' => !empty($params['compare_routines'])
        ];

        try {
            // Extract schemas
            $sourceSchema = MySQLComparator::extractSchema($source, $options);
            $targetSchema = MySQLComparator::extractSchema($target, $options);

            // Compare
            $result = MySQLComparator::compare($sourceSchema, $targetSchema, $options);

            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Comparison error: ' . $e->getMessage()
            ]);
        }
        exit;

    // 4. Demo Comparison (Simulated mode for instant test)
    case 'demo':
        $options = [
            'ignore_int_width' => !empty($params['ignore_int_width']),
            'ignore_collation' => !empty($params['ignore_collation']),
            'ignore_comments' => !empty($params['ignore_comments'])
        ];

        $demo = DemoData::getDemoSchemas();
        $result = MySQLComparator::compare($demo['source'], $demo['target'], $options);

        echo json_encode([
            'success' => true,
            'is_demo' => true,
            'data' => $result
        ]);
        exit;

    // 5. Download generated migration SQL file
    case 'download_sql':
        header('Content-Type: application/sql; charset=utf-8');
        $filename = 'migration_sync_' . date('Ymd_His') . '.sql';
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $sql = $params['sql'] ?? '-- No SQL generated';
        echo $sql;
        exit;

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action requested.'
        ]);
        exit;
}

