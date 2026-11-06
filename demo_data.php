<?php
/**
 * Demo sample schemas for testing DbDiff
 */

class DemoData {
    public static function getDemoSchemas(): array {
        $sourceSchema = [
            'database' => 'ecommerce_production',
            'server' => '192.168.30.109',
            'port' => 3306,
            'tables' => [
                'users' => [
                    'name' => 'users',
                    'engine' => 'InnoDB',
                    'collation' => 'utf8mb4_unicode_ci',
                    'rows' => 45280,
                    'comment' => 'Customer and staff master',
                    'columns' => [
                        'id' => [
                            'name' => 'id',
                            'position' => 1,
                            'type' => 'bigint',
                            'column_type' => 'bigint unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => 'auto_increment',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'PRI',
                            'comment' => ''
                        ],
                        'name' => [
                            'name' => 'name',
                            'position' => 2,
                            'type' => 'varchar',
                            'column_type' => 'varchar(120)',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => 'utf8mb4_unicode_ci',
                            'charset' => 'utf8mb4',
                            'key' => '',
                            'comment' => 'Full name'
                        ],
                        'email' => [
                            'name' => 'email',
                            'position' => 3,
                            'type' => 'varchar',
                            'column_type' => 'varchar(191)',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => 'utf8mb4_unicode_ci',
                            'charset' => 'utf8mb4',
                            'key' => 'UNI',
                            'comment' => 'Primary email'
                        ],
                        'phone' => [
                            'name' => 'phone',
                            'position' => 4,
                            'type' => 'varchar',
                            'column_type' => 'varchar(20)',
                            'nullable' => true,
                            'default' => null,
                            'extra' => '',
                            'collation' => 'utf8mb4_unicode_ci',
                            'charset' => 'utf8mb4',
                            'key' => '',
                            'comment' => 'Contact phone'
                        ],
                        'role' => [
                            'name' => 'role',
                            'position' => 5,
                            'type' => 'enum',
                            'column_type' => "enum('admin','manager','customer')",
                            'nullable' => false,
                            'default' => 'customer',
                            'extra' => '',
                            'collation' => 'utf8mb4_unicode_ci',
                            'charset' => 'utf8mb4',
                            'key' => '',
                            'comment' => 'User access level'
                        ],
                        'email_verified_at' => [
                            'name' => 'email_verified_at',
                            'position' => 6,
                            'type' => 'timestamp',
                            'column_type' => 'timestamp',
                            'nullable' => true,
                            'default' => null,
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => '',
                            'comment' => ''
                        ],
                        'created_at' => [
                            'name' => 'created_at',
                            'position' => 7,
                            'type' => 'timestamp',
                            'column_type' => 'timestamp',
                            'nullable' => false,
                            'default' => 'CURRENT_TIMESTAMP',
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => '',
                            'comment' => ''
                        ]
                    ],
                    'primary_key' => ['id'],
                    'indexes' => [
                        'uk_users_email' => [
                            'name' => 'uk_users_email',
                            'unique' => true,
                            'type' => 'BTREE',
                            'columns' => [['name' => 'email', 'sub_part' => null]]
                        ],
                        'idx_users_role' => [
                            'name' => 'idx_users_role',
                            'unique' => false,
                            'type' => 'BTREE',
                            'columns' => [['name' => 'role', 'sub_part' => null]]
                        ]
                    ],
                    'foreign_keys' => [],
                    'create_table_sql' => "CREATE TABLE `users` (\n  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Full name',\n  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Primary email',\n  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Contact phone',\n  `role` enum('admin','manager','customer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',\n  `email_verified_at` timestamp NULL DEFAULT NULL,\n  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n  PRIMARY KEY (`id`),\n  UNIQUE KEY `uk_users_email` (`email`),\n  KEY `idx_users_role` (`role`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
                ],
                'products' => [
                    'name' => 'products',
                    'engine' => 'InnoDB',
                    'collation' => 'utf8mb4_unicode_ci',
                    'rows' => 8420,
                    'comment' => 'Products catalog',
                    'columns' => [
                        'id' => [
                            'name' => 'id',
                            'position' => 1,
                            'type' => 'bigint',
                            'column_type' => 'bigint unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => 'auto_increment',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'PRI',
                            'comment' => ''
                        ],
                        'sku' => [
                            'name' => 'sku',
                            'position' => 2,
                            'type' => 'varchar',
                            'column_type' => 'varchar(64)',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => 'utf8mb4_unicode_ci',
                            'charset' => 'utf8mb4',
                            'key' => 'UNI',
                            'comment' => 'Stock Keeping Unit'
                        ],
                        'title' => [
                            'name' => 'title',
                            'position' => 3,
                            'type' => 'varchar',
                            'column_type' => 'varchar(255)',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => 'utf8mb4_unicode_ci',
                            'charset' => 'utf8mb4',
                            'key' => '',
                            'comment' => ''
                        ],
                        'price' => [
                            'name' => 'price',
                            'position' => 4,
                            'type' => 'decimal',
                            'column_type' => 'decimal(12,2)',
                            'nullable' => false,
                            'default' => '0.00',
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => '',
                            'comment' => 'Unit sale price'
                        ],
                        'stock_quantity' => [
                            'name' => 'stock_quantity',
                            'position' => 5,
                            'type' => 'int',
                            'column_type' => 'int',
                            'nullable' => false,
                            'default' => '0',
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => '',
                            'comment' => 'Available inventory'
                        ],
                        'category_id' => [
                            'name' => 'category_id',
                            'position' => 6,
                            'type' => 'int',
                            'column_type' => 'int unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'MUL',
                            'comment' => ''
                        ]
                    ],
                    'primary_key' => ['id'],
                    'indexes' => [
                        'uk_products_sku' => [
                            'name' => 'uk_products_sku',
                            'unique' => true,
                            'type' => 'BTREE',
                            'columns' => [['name' => 'sku', 'sub_part' => null]]
                        ],
                        'idx_products_category' => [
                            'name' => 'idx_products_category',
                            'unique' => false,
                            'type' => 'BTREE',
                            'columns' => [['name' => 'category_id', 'sub_part' => null]]
                        ]
                    ],
                    'foreign_keys' => [],
                    'create_table_sql' => "CREATE TABLE `products` (\n  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n  `sku` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Stock Keeping Unit',\n  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,\n  `price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Unit sale price',\n  `stock_quantity` int NOT NULL DEFAULT '0' COMMENT 'Available inventory',\n  `category_id` int unsigned NOT NULL,\n  PRIMARY KEY (`id`),\n  UNIQUE KEY `uk_products_sku` (`sku`),\n  KEY `idx_products_category` (`category_id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
                ],
                'orders' => [
                    'name' => 'orders',
                    'engine' => 'InnoDB',
                    'collation' => 'utf8mb4_unicode_ci',
                    'rows' => 31200,
                    'comment' => 'Customer orders',
                    'columns' => [
                        'id' => [
                            'name' => 'id',
                            'position' => 1,
                            'type' => 'bigint',
                            'column_type' => 'bigint unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => 'auto_increment',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'PRI',
                            'comment' => ''
                        ],
                        'user_id' => [
                            'name' => 'user_id',
                            'position' => 2,
                            'type' => 'bigint',
                            'column_type' => 'bigint unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'MUL',
                            'comment' => ''
                        ],
                        'order_number' => [
                            'name' => 'order_number',
                            'position' => 3,
                            'type' => 'varchar',
                            'column_type' => 'varchar(32)',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => 'utf8mb4_unicode_ci',
                            'charset' => 'utf8mb4',
                            'key' => 'UNI',
                            'comment' => ''
                        ],
                        'total_amount' => [
                            'name' => 'total_amount',
                            'position' => 4,
                            'type' => 'decimal',
                            'column_type' => 'decimal(12,2)',
                            'nullable' => false,
                            'default' => '0.00',
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => '',
                            'comment' => ''
                        ],
                        'status' => [
                            'name' => 'status',
                            'position' => 5,
                            'type' => 'enum',
                            'column_type' => "enum('pending','processing','completed','cancelled')",
                            'nullable' => false,
                            'default' => 'pending',
                            'extra' => '',
                            'collation' => 'utf8mb4_unicode_ci',
                            'charset' => 'utf8mb4',
                            'key' => '',
                            'comment' => ''
                        ]
                    ],
                    'primary_key' => ['id'],
                    'indexes' => [
                        'uk_orders_num' => [
                            'name' => 'uk_orders_num',
                            'unique' => true,
                            'type' => 'BTREE',
                            'columns' => [['name' => 'order_number', 'sub_part' => null]]
                        ],
                        'idx_orders_user' => [
                            'name' => 'idx_orders_user',
                            'unique' => false,
                            'type' => 'BTREE',
                            'columns' => [['name' => 'user_id', 'sub_part' => null]]
                        ]
                    ],
                    'foreign_keys' => [
                        'fk_orders_user' => [
                            'name' => 'fk_orders_user',
                            'columns' => ['user_id'],
                            'referenced_table' => 'users',
                            'referenced_columns' => ['id'],
                            'on_update' => 'CASCADE',
                            'on_delete' => 'RESTRICT'
                        ]
                    ],
                    'create_table_sql' => "CREATE TABLE `orders` (\n  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n  `user_id` bigint unsigned NOT NULL,\n  `order_number` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,\n  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',\n  `status` enum('pending','processing','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',\n  PRIMARY KEY (`id`),\n  UNIQUE KEY `uk_orders_num` (`order_number`),\n  KEY `idx_orders_user` (`user_id`),\n  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
                ],
                'order_items' => [
                    'name' => 'order_items',
                    'engine' => 'InnoDB',
                    'collation' => 'utf8mb4_unicode_ci',
                    'rows' => 64000,
                    'comment' => 'Order line items',
                    'columns' => [
                        'id' => [
                            'name' => 'id',
                            'position' => 1,
                            'type' => 'bigint',
                            'column_type' => 'bigint unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => 'auto_increment',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'PRI',
                            'comment' => ''
                        ],
                        'order_id' => [
                            'name' => 'order_id',
                            'position' => 2,
                            'type' => 'bigint',
                            'column_type' => 'bigint unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'MUL',
                            'comment' => ''
                        ],
                        'product_id' => [
                            'name' => 'product_id',
                            'position' => 3,
                            'type' => 'bigint',
                            'column_type' => 'bigint unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'MUL',
                            'comment' => ''
                        ],
                        'quantity' => [
                            'name' => 'quantity',
                            'position' => 4,
                            'type' => 'int',
                            'column_type' => 'int',
                            'nullable' => false,
                            'default' => '1',
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => '',
                            'comment' => ''
                        ]
                    ],
                    'primary_key' => ['id'],
                    'indexes' => [
                        'idx_items_order' => [
                            'name' => 'idx_items_order',
                            'unique' => false,
                            'type' => 'BTREE',
                            'columns' => [['name' => 'order_id', 'sub_part' => null]]
                        ]
                    ],
                    'foreign_keys' => [],
                    'create_table_sql' => "CREATE TABLE `order_items` (\n  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n  `order_id` bigint unsigned NOT NULL,\n  `product_id` bigint unsigned NOT NULL,\n  `quantity` int NOT NULL DEFAULT '1',\n  PRIMARY KEY (`id`),\n  KEY `idx_items_order` (`order_id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
                ],
                'invoices' => [
                    'name' => 'invoices',
                    'engine' => 'InnoDB',
                    'collation' => 'utf8mb4_unicode_ci',
                    'rows' => 28400,
                    'comment' => 'Tax invoices',
                    'columns' => [
                        'id' => [
                            'name' => 'id',
                            'position' => 1,
                            'type' => 'bigint',
                            'column_type' => 'bigint unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => 'auto_increment',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'PRI',
                            'comment' => ''
                        ],
                        'invoice_code' => [
                            'name' => 'invoice_code',
                            'position' => 2,
                            'type' => 'varchar',
                            'column_type' => 'varchar(30)',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => 'utf8mb4_unicode_ci',
                            'charset' => 'utf8mb4',
                            'key' => 'UNI',
                            'comment' => ''
                        ],
                        'order_id' => [
                            'name' => 'order_id',
                            'position' => 3,
                            'type' => 'bigint',
                            'column_type' => 'bigint unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => '',
                            'comment' => ''
                        ],
                        'amount_due' => [
                            'name' => 'amount_due',
                            'position' => 4,
                            'type' => 'decimal',
                            'column_type' => 'decimal(12,2)',
                            'nullable' => false,
                            'default' => '0.00',
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => '',
                            'comment' => ''
                        ]
                    ],
                    'primary_key' => ['id'],
                    'indexes' => [
                        'uk_invoices_code' => [
                            'name' => 'uk_invoices_code',
                            'unique' => true,
                            'type' => 'BTREE',
                            'columns' => [['name' => 'invoice_code', 'sub_part' => null]]
                        ]
                    ],
                    'foreign_keys' => [],
                    'create_table_sql' => "CREATE TABLE `invoices` (\n  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n  `invoice_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,\n  `order_id` bigint unsigned NOT NULL,\n  `amount_due` decimal(12,2) NOT NULL DEFAULT '0.00',\n  PRIMARY KEY (`id`),\n  UNIQUE KEY `uk_invoices_code` (`invoice_code`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
                ],
                'categories' => [
                    'name' => 'categories',
                    'engine' => 'InnoDB',
                    'collation' => 'utf8mb4_unicode_ci',
                    'rows' => 120,
                    'comment' => 'Category taxonomy',
                    'columns' => [
                        'id' => [
                            'name' => 'id',
                            'position' => 1,
                            'type' => 'int',
                            'column_type' => 'int unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => 'auto_increment',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'PRI',
                            'comment' => ''
                        ],
                        'slug' => [
                            'name' => 'slug',
                            'position' => 2,
                            'type' => 'varchar',
                            'column_type' => 'varchar(80)',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => 'utf8mb4_unicode_ci',
                            'charset' => 'utf8mb4',
                            'key' => 'UNI',
                            'comment' => ''
                        ]
                    ],
                    'primary_key' => ['id'],
                    'indexes' => [],
                    'foreign_keys' => [],
                    'create_table_sql' => "CREATE TABLE `categories` (\n  `id` int unsigned NOT NULL AUTO_INCREMENT,\n  `slug` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,\n  PRIMARY KEY (`id`),\n  UNIQUE KEY `uk_cat_slug` (`slug`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
                ]
            ]
        ];

        // Target Schema (Staging - Has intentional differences, missing tables, extra columns)
        $targetSchema = [
            'database' => 'ecommerce_staging',
            'server' => '192.168.30.181',
            'port' => 3306,
            'tables' => [
                'users' => [
                    'name' => 'users',
                    'engine' => 'InnoDB',
                    'collation' => 'utf8mb4_unicode_ci',
                    'rows' => 1250,
                    'comment' => 'Customer and staff master',
                    'columns' => [
                        'id' => [
                            'name' => 'id',
                            'position' => 1,
                            'type' => 'bigint',
                            'column_type' => 'bigint unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => 'auto_increment',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'PRI',
                            'comment' => ''
                        ],
                        'name' => [
                            'name' => 'name',
                            'position' => 2,
                            'type' => 'varchar',
                            'column_type' => 'varchar(120)',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => 'utf8mb4_unicode_ci',
                            'charset' => 'utf8mb4',
                            'key' => '',
                            'comment' => 'Full name'
                        ],
                        'email' => [
                            'name' => 'email',
                            'position' => 3,
                            'type' => 'varchar',
                            'column_type' => 'varchar(191)',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => 'utf8mb4_unicode_ci',
                            'charset' => 'utf8mb4',
                            'key' => '',
                            'comment' => 'Primary email'
                        ],
                        // Note: 'phone' is missing in target
                        'role' => [
                            'name' => 'role',
                            'position' => 4,
                            'type' => 'varchar',
                            'column_type' => 'varchar(20)', // Diff: varchar(20) vs enum
                            'nullable' => false,
                            'default' => 'customer',
                            'extra' => '',
                            'collation' => 'utf8mb4_unicode_ci',
                            'charset' => 'utf8mb4',
                            'key' => '',
                            'comment' => 'User access level'
                        ],
                        // Note: 'email_verified_at' is missing in target
                        'temp_token' => [ // Extra column in target
                            'name' => 'temp_token',
                            'position' => 5,
                            'type' => 'varchar',
                            'column_type' => 'varchar(100)',
                            'nullable' => true,
                            'default' => null,
                            'extra' => '',
                            'collation' => 'utf8mb4_unicode_ci',
                            'charset' => 'utf8mb4',
                            'key' => '',
                            'comment' => 'Temporary test token'
                        ],
                        'created_at' => [
                            'name' => 'created_at',
                            'position' => 6,
                            'type' => 'timestamp',
                            'column_type' => 'timestamp',
                            'nullable' => false,
                            'default' => 'CURRENT_TIMESTAMP',
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => '',
                            'comment' => ''
                        ]
                    ],
                    'primary_key' => ['id'],
                    'indexes' => [
                        'uk_users_email' => [
                            'name' => 'uk_users_email',
                            'unique' => false, // Diff: Non-unique in target
                            'type' => 'BTREE',
                            'columns' => [['name' => 'email', 'sub_part' => null]]
                        ]
                        // Note: idx_users_role is missing in target
                    ],
                    'foreign_keys' => [],
                    'create_table_sql' => ''
                ],
                'products' => [
                    'name' => 'products',
                    'engine' => 'InnoDB',
                    'collation' => 'utf8mb4_unicode_ci',
                    'rows' => 200,
                    'comment' => 'Products catalog',
                    'columns' => [
                        'id' => [
                            'name' => 'id',
                            'position' => 1,
                            'type' => 'bigint',
                            'column_type' => 'bigint unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => 'auto_increment',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'PRI',
                            'comment' => ''
                        ],
                        // Note: 'sku' missing in target
                        'title' => [
                            'name' => 'title',
                            'position' => 2,
                            'type' => 'varchar',
                            'column_type' => 'varchar(255)',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => 'utf8mb4_unicode_ci',
                            'charset' => 'utf8mb4',
                            'key' => '',
                            'comment' => ''
                        ],
                        'price' => [
                            'name' => 'price',
                            'position' => 3,
                            'type' => 'decimal',
                            'column_type' => 'decimal(10,2)', // Diff: decimal(10,2) vs decimal(12,2)
                            'nullable' => false,
                            'default' => '0.00',
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => '',
                            'comment' => 'Unit sale price'
                        ],
                        'stock_quantity' => [
                            'name' => 'stock_quantity',
                            'position' => 4,
                            'type' => 'smallint',
                            'column_type' => 'smallint', // Diff: smallint vs int
                            'nullable' => false,
                            'default' => '0',
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => '',
                            'comment' => 'Available inventory'
                        ],
                        'category_id' => [
                            'name' => 'category_id',
                            'position' => 5,
                            'type' => 'int',
                            'column_type' => 'int unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => '',
                            'comment' => ''
                        ]
                    ],
                    'primary_key' => ['id'],
                    'indexes' => [], // Note: missing idx_products_category
                    'foreign_keys' => [],
                    'create_table_sql' => ''
                ],
                'orders' => [
                    'name' => 'orders',
                    'engine' => 'InnoDB',
                    'collation' => 'utf8mb4_unicode_ci',
                    'rows' => 540,
                    'comment' => 'Customer orders',
                    'columns' => [
                        'id' => [
                            'name' => 'id',
                            'position' => 1,
                            'type' => 'bigint',
                            'column_type' => 'bigint unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => 'auto_increment',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'PRI',
                            'comment' => ''
                        ],
                        'user_id' => [
                            'name' => 'user_id',
                            'position' => 2,
                            'type' => 'bigint',
                            'column_type' => 'bigint unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'MUL',
                            'comment' => ''
                        ],
                        'order_number' => [
                            'name' => 'order_number',
                            'position' => 3,
                            'type' => 'varchar',
                            'column_type' => 'varchar(32)',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => 'utf8mb4_unicode_ci',
                            'charset' => 'utf8mb4',
                            'key' => 'UNI',
                            'comment' => ''
                        ],
                        'total_amount' => [
                            'name' => 'total_amount',
                            'position' => 4,
                            'type' => 'decimal',
                            'column_type' => 'decimal(12,2)',
                            'nullable' => false,
                            'default' => '0.00',
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => '',
                            'comment' => ''
                        ],
                        'status' => [
                            'name' => 'status',
                            'position' => 5,
                            'type' => 'varchar',
                            'column_type' => 'varchar(32)', // Diff: varchar(32) vs enum
                            'nullable' => false,
                            'default' => 'pending',
                            'extra' => '',
                            'collation' => 'utf8mb4_unicode_ci',
                            'charset' => 'utf8mb4',
                            'key' => '',
                            'comment' => ''
                        ]
                    ],
                    'primary_key' => ['id'],
                    'indexes' => [
                        'uk_orders_num' => [
                            'name' => 'uk_orders_num',
                            'unique' => true,
                            'type' => 'BTREE',
                            'columns' => [['name' => 'order_number', 'sub_part' => null]]
                        ],
                        'idx_orders_user' => [
                            'name' => 'idx_orders_user',
                            'unique' => false,
                            'type' => 'BTREE',
                            'columns' => [['name' => 'user_id', 'sub_part' => null]]
                        ]
                    ],
                    'foreign_keys' => [], // Note: Missing foreign key fk_orders_user!
                    'create_table_sql' => ''
                ],
                'order_items' => [ // Exactly Identical
                    'name' => 'order_items',
                    'engine' => 'InnoDB',
                    'collation' => 'utf8mb4_unicode_ci',
                    'rows' => 1100,
                    'comment' => 'Order line items',
                    'columns' => [
                        'id' => [
                            'name' => 'id',
                            'position' => 1,
                            'type' => 'bigint',
                            'column_type' => 'bigint unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => 'auto_increment',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'PRI',
                            'comment' => ''
                        ],
                        'order_id' => [
                            'name' => 'order_id',
                            'position' => 2,
                            'type' => 'bigint',
                            'column_type' => 'bigint unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'MUL',
                            'comment' => ''
                        ],
                        'product_id' => [
                            'name' => 'product_id',
                            'position' => 3,
                            'type' => 'bigint',
                            'column_type' => 'bigint unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'MUL',
                            'comment' => ''
                        ],
                        'quantity' => [
                            'name' => 'quantity',
                            'position' => 4,
                            'type' => 'int',
                            'column_type' => 'int',
                            'nullable' => false,
                            'default' => '1',
                            'extra' => '',
                            'collation' => null,
                            'charset' => null,
                            'key' => '',
                            'comment' => ''
                        ]
                    ],
                    'primary_key' => ['id'],
                    'indexes' => [
                        'idx_items_order' => [
                            'name' => 'idx_items_order',
                            'unique' => false,
                            'type' => 'BTREE',
                            'columns' => [['name' => 'order_id', 'sub_part' => null]]
                        ]
                    ],
                    'foreign_keys' => [],
                    'create_table_sql' => ''
                ],
                'categories' => [
                    'name' => 'categories',
                    'engine' => 'MyISAM', // Diff: MyISAM vs InnoDB
                    'collation' => 'utf8mb4_general_ci', // Diff: general_ci vs unicode_ci
                    'rows' => 45,
                    'comment' => 'Category taxonomy',
                    'columns' => [
                        'id' => [
                            'name' => 'id',
                            'position' => 1,
                            'type' => 'int',
                            'column_type' => 'int unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => 'auto_increment',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'PRI',
                            'comment' => ''
                        ],
                        'slug' => [
                            'name' => 'slug',
                            'position' => 2,
                            'type' => 'varchar',
                            'column_type' => 'varchar(80)',
                            'nullable' => false,
                            'default' => null,
                            'extra' => '',
                            'collation' => 'utf8mb4_general_ci',
                            'charset' => 'utf8mb4',
                            'key' => 'UNI',
                            'comment' => ''
                        ]
                    ],
                    'primary_key' => ['id'],
                    'indexes' => [],
                    'foreign_keys' => [],
                    'create_table_sql' => ''
                ],
                // Extra table in Target:
                'staging_debug_logs' => [
                    'name' => 'staging_debug_logs',
                    'engine' => 'InnoDB',
                    'collation' => 'utf8mb4_unicode_ci',
                    'rows' => 9500,
                    'comment' => 'Staging debug temporary log table',
                    'columns' => [
                        'id' => [
                            'name' => 'id',
                            'position' => 1,
                            'type' => 'bigint',
                            'column_type' => 'bigint unsigned',
                            'nullable' => false,
                            'default' => null,
                            'extra' => 'auto_increment',
                            'collation' => null,
                            'charset' => null,
                            'key' => 'PRI',
                            'comment' => ''
                        ],
                        'message' => [
                            'name' => 'message',
                            'position' => 2,
                            'type' => 'text',
                            'column_type' => 'text',
                            'nullable' => true,
                            'default' => null,
                            'extra' => '',
                            'collation' => 'utf8mb4_unicode_ci',
                            'charset' => 'utf8mb4',
                            'key' => '',
                            'comment' => ''
                        ]
                    ],
                    'primary_key' => ['id'],
                    'indexes' => [],
                    'foreign_keys' => [],
                    'create_table_sql' => ''
                ]
            ]
        ];

        return [
            'source' => $sourceSchema,
            'target' => $targetSchema
        ];
    }
}

