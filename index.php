<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DbDiff — Fast MySQL & MariaDB Schema Diff & Migration Tool</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        },
                        source: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        },
                        target: {
                            50: '#faf5ff',
                            100: '#f3e8ff',
                            500: '#a855f7',
                            600: '#9333ea',
                            700: '#7e22ce',
                        }
                    },
                    fontFamily: {
                        mono: ['JetBrains Mono', 'Fira Code', 'monospace']
                    }
                }
            }
        }
    </script>
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .badge-missing {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .badge-extra {
            background-color: #f3e8ff;
            color: #6b21a8;
            border: 1px solid #e9d5ff;
        }

        .badge-mismatch {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .badge-identical {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .tab-active {
            border-bottom: 2px solid #2563eb;
            color: #2563eb;
            font-weight: 600;
        }

        pre code {
            font-family: 'JetBrains Mono', Consolas, monospace;
        }

        .spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 min-h-screen">

    <!-- Top Navigation Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div
                    class="w-10 h-10 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-md">
                    <i class="fa-solid fa-code-compare text-lg"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg text-slate-900 leading-tight flex items-center space-x-2">
                        <span>DbDiff</span>
                        <span class="text-xs bg-blue-100 text-blue-700 font-semibold px-2 py-0.5 rounded-full">v2.0</span>
                    </h1>
                    <p class="text-xs text-slate-500">Fast MySQL/MariaDB schema diff & safe DDL migration generator</p>
                </div>
            </div>

            <!-- Header Quick Actions -->
            <div class="flex items-center space-x-2">
                <button type="button" onclick="loadDemoMode()"
                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition">
                    <i class="fa-solid fa-wand-magic-sparkles text-emerald-600"></i>
                    <span>Try Demo Mode</span>
                </button>
                <button type="button" onclick="swapDatabases()" title="Swap Source and Target"
                    class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200 transition">
                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                    <span>Swap</span>
                </button>
                <button type="button" onclick="clearForm()" title="Clear Inputs"
                    class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-red-600 hover:bg-red-50 border border-slate-200 transition">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Reset</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <!-- Form Card Container matching user's layout -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <form id="compareForm" onsubmit="event.preventDefault(); runComparison();">
                <div class="p-6 md:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 relative">

                        <!-- Divider Line with Swap Button in Center for Desktop -->
                        <div
                            class="hidden md:flex absolute inset-y-0 left-1/2 -ml-3 items-center justify-center pointer-events-none">
                            <div class="h-full border-r border-slate-200"></div>
                            <button type="button" onclick="swapDatabases()"
                                class="pointer-events-auto absolute bg-white text-slate-500 hover:text-blue-600 border border-slate-300 w-8 h-8 rounded-full shadow-sm flex items-center justify-center hover:shadow transition"
                                title="Swap Source and Target">
                                <i class="fa-solid fa-arrow-right-arrow-left text-xs"></i>
                            </button>
                        </div>

                        <!-- Source Database Column -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center space-x-2">
                                    <span class="w-3 h-3 rounded-full bg-blue-600"></span>
                                    <h2 class="text-base font-semibold text-slate-900">Source Database</h2>
                                    <span
                                        class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded border border-blue-200">Reference
                                        / Master</span>
                                </div>
                                <button type="button" onclick="testConnection('source')" id="btnTestSource"
                                    class="text-xs font-medium text-blue-600 hover:text-blue-800 flex items-center space-x-1 transition">
                                    <i class="fa-solid fa-plug text-[11px]"></i>
                                    <span>Test Connection</span>
                                </button>
                            </div>

                            <div id="sourceStatusMsg" class="hidden text-xs p-2.5 rounded-lg border"></div>

                            <!-- Server -->
                            <div>
                                <label for="source_server" class="block text-xs font-medium text-slate-700 mb-1">Server
                                    (Host / IP)</label>
                                <div class="relative rounded-md shadow-sm">
                                    <input type="text" id="source_server" name="source_server" required
                                        class="w-full text-sm rounded-lg border-slate-300 border px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                        placeholder="IP Address or localhost" value="">
                                </div>
                            </div>

                            <!-- Username -->
                            <div>
                                <label for="source_username"
                                    class="block text-xs font-medium text-slate-700 mb-1">Username</label>
                                <input type="text" id="source_username" name="source_username" required
                                    class="w-full text-sm rounded-lg border-slate-300 border px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                    placeholder="Username" value="">
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="source_password"
                                    class="block text-xs font-medium text-slate-700 mb-1">Password</label>
                                <div class="relative">
                                    <input type="password" id="source_password" name="source_password"
                                        class="w-full text-sm rounded-lg border-slate-300 border px-3 py-2 pr-10 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                        placeholder="Enter password">
                                    <button type="button" onclick="togglePasswordVisibility('source_password')"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Database Name with dynamic dropdown option -->
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label for="source_database"
                                        class="block text-xs font-medium text-slate-700">Database</label>
                                    <button type="button" onclick="fetchDatabases('source')"
                                        class="text-[11px] text-blue-600 hover:underline">
                                        <i class="fa-solid fa-list-ul mr-0.5"></i> Select from server
                                    </button>
                                </div>
                                <div class="flex space-x-2">
                                    <input type="text" id="source_database" name="source_database" required
                                        class="w-full text-sm rounded-lg border-slate-300 border px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                        placeholder="database_name">
                                    <select id="source_database_select"
                                        onchange="document.getElementById('source_database').value=this.value"
                                        class="hidden text-xs rounded-lg border-slate-300 border px-2 py-2 max-w-[150px]">
                                        <option value="">Choose DB</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Port -->
                            <div>
                                <label for="source_port"
                                    class="block text-xs font-medium text-slate-700 mb-1">Port</label>
                                <input type="number" id="source_port" name="source_port" required
                                    class="w-full text-sm rounded-lg border-slate-300 border px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                    value="3306" placeholder="3306">
                            </div>
                        </div>

                        <!-- Target Database Column -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center space-x-2">
                                    <span class="w-3 h-3 rounded-full bg-purple-600"></span>
                                    <h2 class="text-base font-semibold text-slate-900">Target Database</h2>
                                    <span
                                        class="text-xs bg-purple-50 text-purple-700 px-2 py-0.5 rounded border border-purple-200">Destination
                                        / To Sync</span>
                                </div>
                                <button type="button" onclick="testConnection('target')" id="btnTestTarget"
                                    class="text-xs font-medium text-purple-600 hover:text-purple-800 flex items-center space-x-1 transition">
                                    <i class="fa-solid fa-plug text-[11px]"></i>
                                    <span>Test Connection</span>
                                </button>
                            </div>

                            <div id="targetStatusMsg" class="hidden text-xs p-2.5 rounded-lg border"></div>

                            <!-- Server -->
                            <div>
                                <label for="target_server" class="block text-xs font-medium text-slate-700 mb-1">Server
                                    (Host / IP)</label>
                                <input type="text" id="target_server" name="target_server" required
                                    class="w-full text-sm rounded-lg border-slate-300 border px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition"
                                    placeholder="IP Address or remote host" value="">
                            </div>

                            <!-- Username -->
                            <div>
                                <label for="target_username"
                                    class="block text-xs font-medium text-slate-700 mb-1">Username</label>
                                <input type="text" id="target_username" name="target_username" required
                                    class="w-full text-sm rounded-lg border-slate-300 border px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition"
                                    placeholder="Username" value="">
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="target_password"
                                    class="block text-xs font-medium text-slate-700 mb-1">Password</label>
                                <div class="relative">
                                    <input type="password" id="target_password" name="target_password"
                                        class="w-full text-sm rounded-lg border-slate-300 border px-3 py-2 pr-10 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition"
                                        placeholder="Enter password">
                                    <button type="button" onclick="togglePasswordVisibility('target_password')"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Database Name with dynamic dropdown option -->
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label for="target_database"
                                        class="block text-xs font-medium text-slate-700">Database</label>
                                    <button type="button" onclick="fetchDatabases('target')"
                                        class="text-[11px] text-purple-600 hover:underline">
                                        <i class="fa-solid fa-list-ul mr-0.5"></i> Select from server
                                    </button>
                                </div>
                                <div class="flex space-x-2">
                                    <input type="text" id="target_database" name="target_database" required
                                        class="w-full text-sm rounded-lg border-slate-300 border px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition"
                                        placeholder="database_name">
                                    <select id="target_database_select"
                                        onchange="document.getElementById('target_database').value=this.value"
                                        class="hidden text-xs rounded-lg border-slate-300 border px-2 py-2 max-w-[150px]">
                                        <option value="">Choose DB</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Port -->
                            <div>
                                <label for="target_port"
                                    class="block text-xs font-medium text-slate-700 mb-1">Port</label>
                                <input type="number" id="target_port" name="target_port" required
                                    class="w-full text-sm rounded-lg border-slate-300 border px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition"
                                    value="3306" placeholder="3306">
                            </div>
                        </div>
                    </div>

                    <!-- Comparison Options Accordion / Toggle -->
                    <div class="mt-6 pt-5 border-t border-slate-100">
                        <div class="flex items-center justify-between cursor-pointer"
                            onclick="toggleElement('optionsPanel')">
                            <span
                                class="text-xs font-semibold uppercase tracking-wider text-slate-500 flex items-center space-x-2">
                                <i class="fa-solid fa-sliders text-slate-400"></i>
                                <span>Advanced Comparison Options</span>
                            </span>
                            <span class="text-xs text-blue-600 hover:underline flex items-center space-x-1">
                                <span id="optionsToggleText">Configure</span>
                                <i class="fa-solid fa-chevron-down text-[10px]" id="optionsToggleIcon"></i>
                            </span>
                        </div>

                        <div id="optionsPanel"
                            class="hidden mt-4 pt-4 border-t border-dashed border-slate-200 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                            <label
                                class="flex items-center space-x-2 cursor-pointer bg-slate-50 p-2.5 rounded-lg border border-slate-200 hover:bg-slate-100 transition">
                                <input type="checkbox" id="opt_ignore_int_width" checked
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <div class="font-medium text-slate-800">Ignore Int Display Width</div>
                                    <div class="text-[11px] text-slate-500">Treats int(11) and int as identical (MySQL
                                        8+ compatible)</div>
                                </div>
                            </label>

                            <label
                                class="flex items-center space-x-2 cursor-pointer bg-slate-50 p-2.5 rounded-lg border border-slate-200 hover:bg-slate-100 transition">
                                <input type="checkbox" id="opt_ignore_collation"
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <div class="font-medium text-slate-800">Ignore Collation Differences</div>
                                    <div class="text-[11px] text-slate-500">Ignore utf8mb4_unicode_ci vs
                                        utf8mb4_general_ci</div>
                                </div>
                            </label>

                            <label
                                class="flex items-center space-x-2 cursor-pointer bg-slate-50 p-2.5 rounded-lg border border-slate-200 hover:bg-slate-100 transition">
                                <input type="checkbox" id="opt_ignore_comments" checked
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <div class="font-medium text-slate-800">Ignore Column Comments</div>
                                    <div class="text-[11px] text-slate-500">Do not flag differences if only comments
                                        differ</div>
                                </div>
                            </label>

                            <label
                                class="flex items-center space-x-2 cursor-pointer bg-slate-50 p-2.5 rounded-lg border border-slate-200 hover:bg-slate-100 transition">
                                <input type="checkbox" id="opt_compare_views"
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <div class="font-medium text-slate-800">Include SQL Views</div>
                                    <div class="text-[11px] text-slate-500">Compare view schemas and SQL definitions
                                    </div>
                                </div>
                            </label>

                            <label
                                class="flex items-center space-x-2 cursor-pointer bg-slate-50 p-2.5 rounded-lg border border-slate-200 hover:bg-slate-100 transition">
                                <input type="checkbox" id="opt_compare_triggers"
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <div class="font-medium text-slate-800">Include Triggers</div>
                                    <div class="text-[11px] text-slate-500">Compare table trigger definitions</div>
                                </div>
                            </label>

                            <label
                                class="flex items-center space-x-2 cursor-pointer bg-slate-50 p-2.5 rounded-lg border border-slate-200 hover:bg-slate-100 transition">
                                <input type="checkbox" id="opt_compare_routines"
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <div class="font-medium text-slate-800">Include Stored Procedures</div>
                                    <div class="text-[11px] text-slate-500">Compare procedures and custom functions
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Compare Button Bar -->
                    <div class="mt-6 pt-5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center space-x-2 text-xs text-slate-500">
                            <input type="checkbox" id="saveCredentialsCheck"
                                onchange="toggleSaveCredentials(this.checked)"
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <label for="saveCredentialsCheck" class="cursor-pointer">Remember connection settings in
                                browser</label>
                        </div>

                        <div class="flex items-center space-x-3">
                            <button type="submit" id="btnCompare"
                                class="inline-flex items-center space-x-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium text-sm rounded-lg shadow-sm hover:shadow transition">
                                <i class="fa-solid fa-code-compare"></i>
                                <span>Compare</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Global Alert Message -->
        <div id="errorAlert"
            class="hidden p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-start space-x-3 shadow-sm">
            <i class="fa-solid fa-triangle-exclamation text-red-500 mt-0.5 text-base"></i>
            <div class="flex-1">
                <div class="font-semibold text-red-900" id="errorAlertTitle">Comparison Failed</div>
                <div id="errorAlertMessage" class="mt-1 text-xs"></div>
            </div>
            <button onclick="document.getElementById('errorAlert').classList.add('hidden')"
                class="text-red-400 hover:text-red-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Loading State Indicator -->
        <div id="loadingIndicator" class="hidden py-16 text-center space-y-4">
            <div class="inline-block relative w-16 h-16">
                <div class="w-16 h-16 rounded-full border-4 border-blue-100 border-t-blue-600 spinner"></div>
            </div>
            <div class="space-y-1">
                <div class="font-semibold text-slate-800 text-base">Comparing Databases...</div>
                <div class="text-xs text-slate-500">Fetching information_schema tables, columns, indexes and foreign
                    keys</div>
            </div>
        </div>

        <!-- Comparison Results Section -->
        <div id="resultsSection" class="hidden space-y-6">

            <!-- KPI Metric Cards Banner -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="text-xs font-medium text-slate-500">Source Tables</div>
                    <div class="text-2xl font-bold text-slate-900 mt-1" id="kpiSourceTables">0</div>
                    <div class="text-[11px] text-blue-600 mt-1 truncate" id="kpiSourceDb">db</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="text-xs font-medium text-slate-500">Target Tables</div>
                    <div class="text-2xl font-bold text-slate-900 mt-1" id="kpiTargetTables">0</div>
                    <div class="text-[11px] text-purple-600 mt-1 truncate" id="kpiTargetDb">db</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-red-200 bg-red-50/30 shadow-sm flex flex-col justify-between cursor-pointer hover:border-red-400 transition"
                    onclick="setFilter('missing_in_target')">
                    <div class="text-xs font-medium text-red-600 flex items-center justify-between">
                        <span>Missing in Target</span>
                        <i class="fa-solid fa-circle-minus text-red-400"></i>
                    </div>
                    <div class="text-2xl font-bold text-red-700 mt-1" id="kpiMissingInTarget">0</div>
                    <div class="text-[11px] text-red-500 mt-1">Need creation</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-amber-200 bg-amber-50/30 shadow-sm flex flex-col justify-between cursor-pointer hover:border-amber-400 transition"
                    onclick="setFilter('modified')">
                    <div class="text-xs font-medium text-amber-700 flex items-center justify-between">
                        <span>Schema Mismatch</span>
                        <i class="fa-solid fa-circle-exclamation text-amber-500"></i>
                    </div>
                    <div class="text-2xl font-bold text-amber-800 mt-1" id="kpiModifiedTables">0</div>
                    <div class="text-[11px] text-amber-600 mt-1">Columns / Keys differ</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-purple-200 bg-purple-50/30 shadow-sm flex flex-col justify-between cursor-pointer hover:border-purple-400 transition"
                    onclick="setFilter('extra_in_target')">
                    <div class="text-xs font-medium text-purple-700 flex items-center justify-between">
                        <span>Extra in Target</span>
                        <i class="fa-solid fa-circle-plus text-purple-400"></i>
                    </div>
                    <div class="text-2xl font-bold text-purple-800 mt-1" id="kpiExtraInTarget">0</div>
                    <div class="text-[11px] text-purple-600 mt-1">Not in Source</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-emerald-200 bg-emerald-50/30 shadow-sm flex flex-col justify-between cursor-pointer hover:border-emerald-400 transition"
                    onclick="setFilter('identical')">
                    <div class="text-xs font-medium text-emerald-700 flex items-center justify-between">
                        <span>Identical Tables</span>
                        <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    </div>
                    <div class="text-2xl font-bold text-emerald-800 mt-1" id="kpiIdenticalTables">0</div>
                    <div class="text-[11px] text-emerald-600 mt-1">Perfect match</div>
                </div>
            </div>

            <!-- Filter & Action Toolbar -->
            <div
                class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-4">

                <!-- Filter Pills -->
                <div class="flex flex-wrap items-center gap-1.5" id="filterPillsContainer">
                    <button type="button" onclick="setFilter('all')" data-filter="all"
                        class="filter-pill px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-600 text-white shadow-sm transition">
                        All Tables (<span id="countAll">0</span>)
                    </button>
                    <button type="button" onclick="setFilter('diffs')" data-filter="diffs"
                        class="filter-pill px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                        Differences Only (<span id="countDiffs">0</span>)
                    </button>
                    <button type="button" onclick="setFilter('missing_in_target')" data-filter="missing_in_target"
                        class="filter-pill px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                        Missing (<span id="countMissing">0</span>)
                    </button>
                    <button type="button" onclick="setFilter('modified')" data-filter="modified"
                        class="filter-pill px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                        Mismatches (<span id="countModified">0</span>)
                    </button>
                    <button type="button" onclick="setFilter('extra_in_target')" data-filter="extra_in_target"
                        class="filter-pill px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                        Extra (<span id="countExtra">0</span>)
                    </button>
                    <button type="button" onclick="setFilter('identical')" data-filter="identical"
                        class="filter-pill px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                        Identical (<span id="countIdentical">0</span>)
                    </button>
                </div>

                <!-- Search and Tools -->
                <div class="flex items-center space-x-2 flex-grow sm:flex-grow-0 justify-end">
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-56">
                        <input type="text" id="tableSearchInput" oninput="applyTableSearch()"
                            placeholder="Search tables or columns..."
                            class="w-full text-xs rounded-lg border-slate-300 border pl-8 pr-3 py-1.5 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                    </div>

                    <!-- Expand / Collapse All -->
                    <button type="button" onclick="toggleAllAccordions(true)" title="Expand All"
                        class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 transition">
                        <i class="fa-solid fa-angles-down"></i>
                    </button>
                    <button type="button" onclick="toggleAllAccordions(false)" title="Collapse All"
                        class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 transition">
                        <i class="fa-solid fa-angles-up"></i>
                    </button>

                    <!-- Migration SQL Button -->
                    <button type="button" onclick="openMigrationModal()"
                        class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                        <i class="fa-solid fa-code"></i>
                        <span>Generate Sync SQL</span>
                    </button>
                </div>
            </div>

            <!-- Tables Accordion List Container -->
            <div id="tablesContainer" class="space-y-3">
                <!-- Rendered dynamically via JavaScript -->
            </div>

            <!-- Empty State when filter yields 0 -->
            <div id="noResultsState"
                class="hidden bg-white p-12 text-center rounded-xl border border-slate-200 shadow-sm space-y-2">
                <i class="fa-solid fa-table-list text-3xl text-slate-300"></i>
                <div class="font-semibold text-slate-700 text-sm">No tables match the current filter</div>
                <div class="text-xs text-slate-400">Try changing your search term or filter selection.</div>
            </div>
        </div>

    </main>

    <!-- Full Migration SQL Modal -->
    <div id="migrationModal"
        class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div
            class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">

            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <div class="flex items-center space-x-2">
                    <div
                        class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-database text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Target Synchronization SQL Script</h3>
                        <p class="text-[11px] text-slate-500">Run this SQL in your Target database to synchronize with
                            Source</p>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <button type="button" onclick="copyMigrationSql()" id="btnCopySql"
                        class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition shadow-sm">
                        <i class="fa-regular fa-copy"></i>
                        <span id="copySqlText">Copy SQL</span>
                    </button>
                    <button type="button" onclick="downloadMigrationSql()"
                        class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white transition shadow-sm">
                        <i class="fa-solid fa-download"></i>
                        <span>Download .sql</span>
                    </button>
                    <button type="button" onclick="closeMigrationModal()"
                        class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 flex items-center justify-center">
                        <i class="fa-solid fa-xmark text-base"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body (SQL Preview) -->
            <div class="p-6 overflow-y-auto flex-1 bg-slate-900 text-slate-100">
                <pre
                    class="text-xs leading-relaxed overflow-x-auto"><code id="modalSqlCode">-- Loading SQL...</code></pre>
            </div>

            <!-- Modal Footer -->
            <div
                class="px-6 py-3 border-t border-slate-200 bg-slate-50 flex items-center justify-between text-xs text-slate-500">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                    <span>Foreign key checks are disabled during execution for safe alteration.</span>
                </div>
                <button type="button" onclick="closeMigrationModal()"
                    class="px-4 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 font-medium">Close</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toastNotification"
        class="fixed bottom-5 right-5 z-50 transform translate-y-20 opacity-0 transition duration-300 pointer-events-none">
        <div class="bg-slate-900 text-white text-xs px-4 py-2.5 rounded-lg shadow-xl flex items-center space-x-2">
            <i class="fa-solid fa-circle-check text-emerald-400"></i>
            <span id="toastMessage">Action completed!</span>
        </div>
    </div>

    <!-- Frontend Application Logic -->
    <script>
        // State variables
        let comparisonResult = null;
        let currentFilter = 'all';
        let currentSearchQuery = '';

        // Load saved credentials on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadSavedCredentials();
        });

        // Toggle element visibility
        function toggleElement(id) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.toggle('hidden');
            }
        }

        // Toggle password show/hide
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;
            input.type = (input.type === 'password') ? 'text' : 'password';
        }

        // Toast notification
        function showToast(message) {
            const toast = document.getElementById('toastNotification');
            const toastMsg = document.getElementById('toastMessage');
            toastMsg.textContent = message;
            toast.classList.remove('translate-y-20', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3000);
        }

        // Test connection endpoint
        async function testConnection(type) {
            const server = document.getElementById(`${type}_server`).value.trim();
            const port = document.getElementById(`${type}_port`).value.trim();
            const username = document.getElementById(`${type}_username`).value.trim();
            const password = document.getElementById(`${type}_password`).value;
            const database = document.getElementById(`${type}_database`).value.trim();

            const statusEl = document.getElementById(`${type}StatusMsg`);
            const btn = document.getElementById(`btnTest${type.charAt(0).toUpperCase() + type.slice(1)}`);
            const originalBtnHtml = btn.innerHTML;

            btn.innerHTML = `<i class="fa-solid fa-spinner spinner"></i> Testing...`;
            btn.disabled = true;

            statusEl.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'border-red-200', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-200');

            try {
                const response = await fetch('api.php?action=test_connection', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ server, port, username, password, database })
                });
                const res = await response.json();

                if (res.success) {
                    statusEl.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
                    statusEl.innerHTML = `
                        <div class="flex items-center space-x-1.5 font-semibold">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i>
                            <span>Connected successfully! (${res.latency_ms} ms)</span>
                        </div>
                        <div class="text-[11px] mt-0.5 text-emerald-800">MySQL ${res.version} | Found ${res.databases ? res.databases.length : 0} accessible database(s)</div>
                    `;

                    // Populate database select dropdown if databases are found
                    if (res.databases && res.databases.length > 0) {
                        populateDbDropdown(type, res.databases);
                    }
                } else {
                    statusEl.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
                    statusEl.innerHTML = `
                        <div class="flex items-center space-x-1.5 font-semibold">
                            <i class="fa-solid fa-circle-xmark text-red-600"></i>
                            <span>Connection Failed</span>
                        </div>
                        <div class="text-[11px] mt-0.5 text-red-800">${escapeHtml(res.message)}</div>
                    `;
                }
            } catch (err) {
                statusEl.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
                statusEl.innerHTML = `<i class="fa-solid fa-circle-xmark"></i> Connection error: ${escapeHtml(err.message)}`;
            } finally {
                btn.innerHTML = originalBtnHtml;
                btn.disabled = false;
            }
        }

        // Fetch list of databases on the server
        async function fetchDatabases(type) {
            const server = document.getElementById(`${type}_server`).value.trim();
            const port = document.getElementById(`${type}_port`).value.trim();
            const username = document.getElementById(`${type}_username`).value.trim();
            const password = document.getElementById(`${type}_password`).value;

            try {
                const response = await fetch('api.php?action=get_databases', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ server, port, username, password })
                });
                const res = await response.json();
                if (res.success && res.databases && res.databases.length > 0) {
                    populateDbDropdown(type, res.databases);
                    showToast(`Loaded ${res.databases.length} databases from ${type} server`);
                } else {
                    alert(res.message || 'Could not fetch databases. Check credentials.');
                }
            } catch (e) {
                alert('Error fetching databases: ' + e.message);
            }
        }

        function populateDbDropdown(type, databases) {
            const selectEl = document.getElementById(`${type}_database_select`);
            const inputEl = document.getElementById(`${type}_database`);
            selectEl.innerHTML = '<option value="">-- Choose Database --</option>';
            databases.forEach(db => {
                const opt = document.createElement('option');
                opt.value = db;
                opt.textContent = db;
                if (db === inputEl.value) opt.selected = true;
                selectEl.appendChild(opt);
            });
            selectEl.classList.remove('hidden');
        }

        // Swap source and target inputs
        function swapDatabases() {
            const fields = ['server', 'port', 'username', 'password', 'database'];
            fields.forEach(f => {
                const srcEl = document.getElementById(`source_${f}`);
                const tgtEl = document.getElementById(`target_${f}`);
                const tmp = srcEl.value;
                srcEl.value = tgtEl.value;
                tgtEl.value = tmp;
            });
            showToast('Swapped Source and Target databases');
        }

        // Reset form
        function clearForm() {
            document.getElementById('compareForm').reset();
            document.getElementById('sourceStatusMsg').classList.add('hidden');
            document.getElementById('targetStatusMsg').classList.add('hidden');
            document.getElementById('resultsSection').classList.add('hidden');
            comparisonResult = null;
            showToast('Form reset');
        }

        // Load demo mode
        async function loadDemoMode() {
            document.getElementById('errorAlert').classList.add('hidden');
            document.getElementById('resultsSection').classList.add('hidden');
            document.getElementById('loadingIndicator').classList.remove('hidden');

            try {
                const response = await fetch('api.php?action=demo');
                const res = await response.json();

                if (res.success) {
                    comparisonResult = res.data;
                    renderComparisonResults(comparisonResult, true);
                    showToast('Demo schemas loaded successfully!');
                } else {
                    showError('Demo Failed', res.message);
                }
            } catch (err) {
                showError('Demo Request Error', err.message);
            } finally {
                document.getElementById('loadingIndicator').classList.add('hidden');
            }
        }

        // Run full database comparison
        async function runComparison() {
            const source_server = document.getElementById('source_server').value.trim();
            const source_port = document.getElementById('source_port').value.trim();
            const source_username = document.getElementById('source_username').value.trim();
            const source_password = document.getElementById('source_password').value;
            const source_database = document.getElementById('source_database').value.trim();

            const target_server = document.getElementById('target_server').value.trim();
            const target_port = document.getElementById('target_port').value.trim();
            const target_username = document.getElementById('target_username').value.trim();
            const target_password = document.getElementById('target_password').value;
            const target_database = document.getElementById('target_database').value.trim();

            if (!source_server || !source_database) {
                showError('Validation Error', 'Please specify Source Server and Database name.');
                return;
            }
            if (!target_server || !target_database) {
                showError('Validation Error', 'Please specify Target Server and Database name.');
                return;
            }

            // Save credentials if checked
            if (document.getElementById('saveCredentialsCheck').checked) {
                saveCredentialsToStorage();
            }

            // Collect options
            const payload = {
                source_server, source_port, source_username, source_password, source_database,
                target_server, target_port, target_username, target_password, target_database,
                ignore_int_width: document.getElementById('opt_ignore_int_width').checked ? 1 : 0,
                ignore_collation: document.getElementById('opt_ignore_collation').checked ? 1 : 0,
                ignore_comments: document.getElementById('opt_ignore_comments').checked ? 1 : 0,
                compare_views: document.getElementById('opt_compare_views').checked ? 1 : 0,
                compare_triggers: document.getElementById('opt_compare_triggers').checked ? 1 : 0,
                compare_routines: document.getElementById('opt_compare_routines').checked ? 1 : 0
            };

            document.getElementById('errorAlert').classList.add('hidden');
            document.getElementById('resultsSection').classList.add('hidden');
            document.getElementById('loadingIndicator').classList.remove('hidden');

            const btn = document.getElementById('btnCompare');
            btn.disabled = true;

            try {
                const response = await fetch('api.php?action=compare', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const res = await response.json();

                if (res.success) {
                    comparisonResult = res.data;
                    renderComparisonResults(comparisonResult, false);
                } else {
                    showError('Comparison Failed', res.message);
                }
            } catch (err) {
                showError('Network / Server Error', err.message);
            } finally {
                document.getElementById('loadingIndicator').classList.add('hidden');
                btn.disabled = false;
            }
        }

        // Show error message
        function showError(title, message) {
            document.getElementById('errorAlertTitle').textContent = title;
            document.getElementById('errorAlertMessage').textContent = message;
            document.getElementById('errorAlert').classList.remove('hidden');
            window.scrollTo({ top: document.getElementById('errorAlert').offsetTop - 80, behavior: 'smooth' });
        }

        // Render complete comparison results
        function renderComparisonResults(data, isDemo = false) {
            const summary = data.summary;

            // Populate KPI metrics
            document.getElementById('kpiSourceTables').textContent = summary.total_source_tables;
            document.getElementById('kpiSourceDb').textContent = summary.source_database + (isDemo ? ' (Demo)' : '');

            document.getElementById('kpiTargetTables').textContent = summary.total_target_tables;
            document.getElementById('kpiTargetDb').textContent = summary.target_database + (isDemo ? ' (Demo)' : '');

            document.getElementById('kpiMissingInTarget').textContent = summary.missing_in_target_count;
            document.getElementById('kpiModifiedTables').textContent = summary.modified_tables_count;
            document.getElementById('kpiExtraInTarget').textContent = summary.extra_in_target_count;
            document.getElementById('kpiIdenticalTables').textContent = summary.identical_tables_count;

            // Populate Filter counts
            const totalTables = Object.keys(data.tables).length;
            const diffsCount = summary.missing_in_target_count + summary.modified_tables_count + summary.extra_in_target_count;
            document.getElementById('countAll').textContent = totalTables;
            document.getElementById('countDiffs').textContent = diffsCount;
            document.getElementById('countMissing').textContent = summary.missing_in_target_count;
            document.getElementById('countModified').textContent = summary.modified_tables_count;
            document.getElementById('countExtra').textContent = summary.extra_in_target_count;
            document.getElementById('countIdentical').textContent = summary.identical_tables_count;

            // Auto-select filter: if differences exist, show "diffs", else show "all"
            if (diffsCount > 0) {
                setFilter('diffs');
            } else {
                setFilter('all');
            }

            document.getElementById('resultsSection').classList.remove('hidden');

            // Scroll down smoothly to results
            setTimeout(() => {
                document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
            }, 100);
        }

        // Set active filter
        function setFilter(filter) {
            currentFilter = filter;

            // Update filter pill UI
            document.querySelectorAll('.filter-pill').forEach(btn => {
                const btnFilter = btn.getAttribute('data-filter');
                if (btnFilter === filter) {
                    btn.className = 'filter-pill px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-600 text-white shadow-sm transition';
                } else {
                    btn.className = 'filter-pill px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 transition';
                }
            });

            renderFilteredTables();
        }

        // Apply table search input
        function applyTableSearch() {
            currentSearchQuery = document.getElementById('tableSearchInput').value.trim().toLowerCase();
            renderFilteredTables();
        }

        // Render filtered tables list
        function renderFilteredTables() {
            if (!comparisonResult || !comparisonResult.tables) return;

            const container = document.getElementById('tablesContainer');
            container.innerHTML = '';
            let visibleCount = 0;

            const tables = comparisonResult.tables;

            for (const [tName, tData] of Object.entries(tables)) {
                // Check filter status
                let matchesFilter = true;
                if (currentFilter === 'diffs') {
                    matchesFilter = (tData.status !== 'identical');
                } else if (currentFilter === 'missing_in_target') {
                    matchesFilter = (tData.status === 'missing_in_target');
                } else if (currentFilter === 'modified') {
                    matchesFilter = (tData.status === 'modified');
                } else if (currentFilter === 'extra_in_target') {
                    matchesFilter = (tData.status === 'extra_in_target');
                } else if (currentFilter === 'identical') {
                    matchesFilter = (tData.status === 'identical');
                }

                if (!matchesFilter) continue;

                // Check search query
                if (currentSearchQuery) {
                    let matchesSearch = tName.toLowerCase().includes(currentSearchQuery);
                    if (!matchesSearch && tData.columns) {
                        for (const colName of Object.keys(tData.columns)) {
                            if (colName.toLowerCase().includes(currentSearchQuery)) {
                                matchesSearch = true;
                                break;
                            }
                        }
                    }
                    if (!matchesSearch) continue;
                }

                visibleCount++;
                container.appendChild(createTableAccordionItem(tName, tData));
            }

            const noResults = document.getElementById('noResultsState');
            if (visibleCount === 0) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }

        // Create individual table accordion card
        function createTableAccordionItem(tName, tData) {
            const card = document.createElement('div');
            card.className = 'bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden transition';
            card.id = `tableCard_${tName}`;

            // Status styling
            let statusBadge = '';
            let borderAccent = '';
            if (tData.status === 'missing_in_target') {
                statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-xs font-semibold badge-missing"><i class="fa-solid fa-circle-minus mr-1"></i> Missing in Target</span>';
                borderAccent = 'border-l-4 border-l-red-500';
            } else if (tData.status === 'extra_in_target') {
                statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-xs font-semibold badge-extra"><i class="fa-solid fa-circle-plus mr-1"></i> Extra in Target</span>';
                borderAccent = 'border-l-4 border-l-purple-500';
            } else if (tData.status === 'modified') {
                statusBadge = `<span class="px-2.5 py-0.5 rounded-full text-xs font-semibold badge-mismatch"><i class="fa-solid fa-triangle-exclamation mr-1"></i> ${tData.differences_count} Diff(s)</span>`;
                borderAccent = 'border-l-4 border-l-amber-500';
            } else {
                statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-xs font-semibold badge-identical"><i class="fa-solid fa-circle-check mr-1"></i> Identical</span>';
                borderAccent = 'border-l-4 border-l-emerald-500';
            }

            card.classList.add(...borderAccent.split(' '));

            // Summary metrics
            const rowsInfo = `Source: <strong>${(tData.source_rows || 0).toLocaleString()}</strong> rows | Target: <strong>${(tData.target_rows || 0).toLocaleString()}</strong> rows`;

            // Differences breakdown
            let diffTags = [];
            if (tData.status === 'missing_in_target') {
                diffTags.push('<span class="text-[11px] bg-red-100 text-red-700 px-1.5 py-0.5 rounded">Table must be created in Target</span>');
            } else if (tData.status === 'extra_in_target') {
                diffTags.push('<span class="text-[11px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded">Table exists only in Target</span>');
            } else if (tData.status === 'modified') {
                const missingCols = Object.values(tData.columns || {}).filter(c => c.status === 'missing_in_target').length;
                const modifiedCols = Object.values(tData.columns || {}).filter(c => c.status === 'modified').length;
                const extraCols = Object.values(tData.columns || {}).filter(c => c.status === 'extra_in_target').length;
                const idxDiffs = Object.keys(tData.indexes || {}).length;
                const fkDiffs = Object.keys(tData.foreign_keys || {}).length;

                if (missingCols > 0) diffTags.push(`<span class="text-[11px] bg-red-100 text-red-700 px-1.5 py-0.5 rounded font-medium">+${missingCols} Missing Col(s)</span>`);
                if (modifiedCols > 0) diffTags.push(`<span class="text-[11px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded font-medium">${modifiedCols} Modified Col(s)</span>`);
                if (extraCols > 0) diffTags.push(`<span class="text-[11px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded font-medium">${extraCols} Extra Col(s)</span>`);
                if (idxDiffs > 0) diffTags.push(`<span class="text-[11px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded font-medium">${idxDiffs} Index Diff(s)</span>`);
                if (fkDiffs > 0) diffTags.push(`<span class="text-[11px] bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded font-medium">${fkDiffs} FK Diff(s)</span>`);
            }

            // Accordion Header
            const headerHtml = `
                <div class="p-4 flex flex-wrap items-center justify-between gap-3 cursor-pointer hover:bg-slate-50 transition" onclick="toggleTableAccordion('${tName}')">
                    <div class="flex items-center space-x-3">
                        <span id="arrow_${tName}" class="text-slate-400 transform transition-transform duration-200">
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </span>
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="font-mono font-bold text-sm text-slate-900">${tName}</span>
                                ${statusBadge}
                            </div>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="text-[11px] text-slate-500">${rowsInfo}</span>
                                <div class="flex items-center space-x-1">${diffTags.join(' ')}</div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2" onclick="event.stopPropagation()">
                        ${tData.migration_sql ? `
                            <button type="button" onclick="copyTableSql('${tName}')" title="Copy SQL for this table" class="text-xs px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center space-x-1 transition">
                                <i class="fa-regular fa-copy text-[11px]"></i>
                                <span>Table SQL</span>
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;

            // Accordion Body
            let bodyContentHtml = '';

            if (tData.status === 'missing_in_target') {
                bodyContentHtml = `
                    <div class="p-4 border-t border-slate-100 bg-red-50/20 space-y-3">
                        <div class="text-xs text-red-700 font-medium">This table exists in the Source database but does not exist in the Target database.</div>
                        <div class="bg-slate-900 text-slate-100 p-3 rounded-lg text-xs font-mono overflow-x-auto">
                            <code>${escapeHtml(tData.create_sql || 'CREATE TABLE `' + tName + '` ...')}</code>
                        </div>
                    </div>
                `;
            } else if (tData.status === 'extra_in_target') {
                bodyContentHtml = `
                    <div class="p-4 border-t border-slate-100 bg-purple-50/20 space-y-2">
                        <div class="text-xs text-purple-700 font-medium">This table exists in the Target database, but not in Source.</div>
                        <div class="text-[11px] text-slate-500">If you wish to drop it on the target, you can run:</div>
                        <div class="bg-slate-900 text-slate-100 p-2.5 rounded-lg text-xs font-mono">
                            <code>DROP TABLE IF EXISTS \`${tName}\`;</code>
                        </div>
                    </div>
                `;
            } else {
                // Table Columns Comparison Table
                let columnsRows = '';
                if (tData.columns) {
                    for (const [colName, col] of Object.entries(tData.columns)) {
                        let rowBg = 'bg-white';
                        let statusTag = '';

                        if (col.status === 'missing_in_target') {
                            rowBg = 'bg-red-50/40';
                            statusTag = '<span class="text-[10px] bg-red-100 text-red-700 px-1.5 py-0.5 rounded font-bold">Missing in Target</span>';
                        } else if (col.status === 'extra_in_target') {
                            rowBg = 'bg-purple-50/40';
                            statusTag = '<span class="text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded font-bold">Extra in Target</span>';
                        } else if (col.status === 'modified') {
                            rowBg = 'bg-amber-50/40';
                            statusTag = '<span class="text-[10px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded font-bold">Modified</span>';
                        } else {
                            statusTag = '<span class="text-[10px] text-slate-400">Match</span>';
                        }

                        const srcDef = col.source ? `
                            <div class="font-mono text-xs font-medium text-slate-800">${escapeHtml(col.source.column_type)}</div>
                            <div class="text-[11px] text-slate-500">
                                ${col.source.nullable ? 'NULL' : 'NOT NULL'} | Default: ${col.source.default !== null ? escapeHtml(col.source.default) : 'NULL'} ${col.source.extra ? '| ' + escapeHtml(col.source.extra) : ''}
                            </div>
                        ` : '<span class="text-slate-400 italic text-xs">—</span>';

                        const tgtDef = col.target ? `
                            <div class="font-mono text-xs font-medium text-slate-800">${escapeHtml(col.target.column_type)}</div>
                            <div class="text-[11px] text-slate-500">
                                ${col.target.nullable ? 'NULL' : 'NOT NULL'} | Default: ${col.target.default !== null ? escapeHtml(col.target.default) : 'NULL'} ${col.target.extra ? '| ' + escapeHtml(col.target.extra) : ''}
                            </div>
                        ` : '<span class="text-red-500 font-semibold text-xs">Missing</span>';

                        let diffDesc = '';
                        if (col.diff_details && col.diff_details.length > 0) {
                            diffDesc = `<ul class="list-disc list-inside text-[11px] text-amber-900 font-medium space-y-0.5">
                                ${col.diff_details.map(d => `<li>${escapeHtml(d)}</li>`).join('')}
                            </ul>`;
                        }

                        columnsRows += `
                            <tr class="${rowBg} border-b border-slate-100 text-xs">
                                <td class="py-2.5 px-3 font-mono font-semibold text-slate-800">${escapeHtml(colName)}</td>
                                <td class="py-2.5 px-3">${srcDef}</td>
                                <td class="py-2.5 px-3">${tgtDef}</td>
                                <td class="py-2.5 px-3">${statusTag} ${diffDesc}</td>
                            </tr>
                        `;
                    }
                }

                // Indexes Rows
                let indexSection = '';
                if (tData.indexes && Object.keys(tData.indexes).length > 0) {
                    let idxRows = '';
                    for (const [idxName, idx] of Object.entries(tData.indexes)) {
                        idxRows += `
                            <tr class="border-b border-slate-100 text-xs">
                                <td class="py-2 px-3 font-mono font-medium">${escapeHtml(idxName)}</td>
                                <td class="py-2 px-3 font-mono text-slate-700">${escapeHtml(idx.source || '—')}</td>
                                <td class="py-2 px-3 font-mono text-slate-700">${escapeHtml(idx.target || '—')}</td>
                                <td class="py-2 px-3">
                                    <span class="text-[10px] px-1.5 py-0.5 rounded font-semibold ${idx.status === 'missing_in_target' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800'}">
                                        ${idx.status === 'missing_in_target' ? 'Missing in Target' : 'Mismatch'}
                                    </span>
                                </td>
                            </tr>
                        `;
                    }
                    indexSection = `
                        <div class="mt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2 flex items-center space-x-1.5">
                                <i class="fa-solid fa-key text-slate-400"></i>
                                <span>Index & Key Differences</span>
                            </h4>
                            <div class="overflow-x-auto rounded-lg border border-slate-200">
                                <table class="w-full text-left bg-white">
                                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-600 border-b border-slate-200">
                                        <tr>
                                            <th class="py-2 px-3">Index Name</th>
                                            <th class="py-2 px-3 text-blue-700">Source Definition</th>
                                            <th class="py-2 px-3 text-purple-700">Target Definition</th>
                                            <th class="py-2 px-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>${idxRows}</tbody>
                                </table>
                            </div>
                        </div>
                    `;
                }

                // Foreign Keys Section
                let fkSection = '';
                if (tData.foreign_keys && Object.keys(tData.foreign_keys).length > 0) {
                    let fkRows = '';
                    for (const [fkName, fk] of Object.entries(tData.foreign_keys)) {
                        fkRows += `
                            <tr class="border-b border-slate-100 text-xs">
                                <td class="py-2 px-3 font-mono font-medium">${escapeHtml(fkName)}</td>
                                <td class="py-2 px-3 font-mono text-slate-700">${escapeHtml(fk.source || '—')}</td>
                                <td class="py-2 px-3 font-mono text-slate-700">${escapeHtml(fk.target || '—')}</td>
                                <td class="py-2 px-3">
                                    <span class="text-[10px] px-1.5 py-0.5 rounded font-semibold bg-indigo-100 text-indigo-700">
                                        ${escapeHtml(fk.status || 'Difference')}
                                    </span>
                                </td>
                            </tr>
                        `;
                    }
                    fkSection = `
                        <div class="mt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2 flex items-center space-x-1.5">
                                <i class="fa-solid fa-diagram-project text-slate-400"></i>
                                <span>Foreign Key Differences</span>
                            </h4>
                            <div class="overflow-x-auto rounded-lg border border-slate-200">
                                <table class="w-full text-left bg-white">
                                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-600 border-b border-slate-200">
                                        <tr>
                                            <th class="py-2 px-3">Constraint Name</th>
                                            <th class="py-2 px-3 text-blue-700">Source Definition</th>
                                            <th class="py-2 px-3 text-purple-700">Target Definition</th>
                                            <th class="py-2 px-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>${fkRows}</tbody>
                                </table>
                            </div>
                        </div>
                    `;
                }

                // Table options (Engine/Collation)
                let optionsSection = '';
                if (tData.options && Object.keys(tData.options).length > 0) {
                    let optRows = '';
                    for (const [optKey, opt] of Object.entries(tData.options)) {
                        optRows += `
                            <div class="flex items-center space-x-4 text-xs bg-amber-50 p-2 rounded-lg border border-amber-200 text-amber-900">
                                <div class="font-semibold">${escapeHtml(opt.property)}:</div>
                                <div>Source: <strong class="font-mono">${escapeHtml(opt.source)}</strong></div>
                                <div>Target: <strong class="font-mono">${escapeHtml(opt.target)}</strong></div>
                            </div>
                        `;
                    }
                    optionsSection = `<div class="mt-4 space-y-2">${optRows}</div>`;
                }

                // Table SQL Snippet
                let tableSqlSection = '';
                if (tData.migration_sql) {
                    tableSqlSection = `
                        <div class="mt-4">
                            <div class="flex items-center justify-between mb-1.5">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center space-x-1.5">
                                    <i class="fa-solid fa-code text-slate-400"></i>
                                    <span>Sync SQL for \`${tName}\`</span>
                                </h4>
                                <button type="button" onclick="copyTableSql('${tName}')" class="text-[11px] text-blue-600 hover:underline">
                                    <i class="fa-regular fa-copy mr-0.5"></i> Copy SQL
                                </button>
                            </div>
                            <div class="bg-slate-900 text-slate-100 p-3 rounded-lg text-xs font-mono overflow-x-auto">
                                <code id="sqlSnippet_${tName}">${escapeHtml(tData.migration_sql)}</code>
                            </div>
                        </div>
                    `;
                }

                bodyContentHtml = `
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50 space-y-4">
                        <!-- Columns Diff Table -->
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2 flex items-center space-x-1.5">
                                <i class="fa-solid fa-table-columns text-slate-400"></i>
                                <span>Columns Schema Comparison</span>
                            </h4>
                            <div class="overflow-x-auto rounded-lg border border-slate-200">
                                <table class="w-full text-left bg-white">
                                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-600 border-b border-slate-200">
                                        <tr>
                                            <th class="py-2.5 px-3">Column Name</th>
                                            <th class="py-2.5 px-3 text-blue-700">Source Database</th>
                                            <th class="py-2.5 px-3 text-purple-700">Target Database</th>
                                            <th class="py-2.5 px-3">Differences</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${columnsRows || '<tr><td colspan="4" class="py-3 px-4 text-center text-slate-400 text-xs">No column data available.</td></tr>'}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        ${indexSection}
                        ${fkSection}
                        ${optionsSection}
                        ${tableSqlSection}
                    </div>
                `;
            }

            const bodyContainer = `
                <div id="body_${tName}" class="hidden">
                    ${bodyContentHtml}
                </div>
            `;

            card.innerHTML = headerHtml + bodyContainer;
            return card;
        }

        // Toggle individual table accordion
        function toggleTableAccordion(tName) {
            const body = document.getElementById(`body_${tName}`);
            const arrow = document.getElementById(`arrow_${tName}`);
            if (!body) return;

            const isHidden = body.classList.contains('hidden');
            if (isHidden) {
                body.classList.remove('hidden');
                if (arrow) arrow.classList.add('rotate-90');
            } else {
                body.classList.add('hidden');
                if (arrow) arrow.classList.remove('rotate-90');
            }
        }

        // Expand or collapse all accordions
        function toggleAllAccordions(expand) {
            if (!comparisonResult || !comparisonResult.tables) return;
            for (const tName of Object.keys(comparisonResult.tables)) {
                const body = document.getElementById(`body_${tName}`);
                const arrow = document.getElementById(`arrow_${tName}`);
                if (!body) continue;

                if (expand) {
                    body.classList.remove('hidden');
                    if (arrow) arrow.classList.add('rotate-90');
                } else {
                    body.classList.add('hidden');
                    if (arrow) arrow.classList.remove('rotate-90');
                }
            }
        }

        // Copy single table SQL
        function copyTableSql(tName) {
            if (!comparisonResult || !comparisonResult.tables || !comparisonResult.tables[tName]) return;
            const sql = comparisonResult.tables[tName].migration_sql;
            navigator.clipboard.writeText(sql).then(() => {
                showToast(`Copied SQL for \`${tName}\` to clipboard!`);
            }).catch(() => {
                showToast('Failed to copy. Please manually select and copy.');
            });
        }

        // Open full migration modal
        function openMigrationModal() {
            if (!comparisonResult || !comparisonResult.migration_sql) {
                alert('No migration SQL script available.');
                return;
            }
            document.getElementById('modalSqlCode').textContent = comparisonResult.migration_sql;
            document.getElementById('migrationModal').classList.remove('hidden');
        }

        function closeMigrationModal() {
            document.getElementById('migrationModal').classList.add('hidden');
        }

        // Copy migration SQL to clipboard
        function copyMigrationSql() {
            if (!comparisonResult || !comparisonResult.migration_sql) return;
            navigator.clipboard.writeText(comparisonResult.migration_sql).then(() => {
                const btn = document.getElementById('copySqlText');
                btn.textContent = 'Copied!';
                setTimeout(() => { btn.textContent = 'Copy SQL'; }, 2000);
                showToast('Full sync SQL copied to clipboard!');
            });
        }

        // Download migration SQL as a .sql file
        function downloadMigrationSql() {
            if (!comparisonResult || !comparisonResult.migration_sql) return;
            const sql = comparisonResult.migration_sql;
            const blob = new Blob([sql], { type: 'application/sql' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            const timestamp = new Date().toISOString().replace(/[-:T]/g, '').slice(0, 14);
            a.href = url;
            a.download = `mysql_diff_sync_${timestamp}.sql`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showToast('Downloaded migration SQL script');
        }

        // LocalStorage helper for remembering credentials
        function toggleSaveCredentials(checked) {
            if (!checked) {
                localStorage.removeItem('mysql_comparator_credentials');
                showToast('Saved credentials cleared from browser storage');
            } else {
                saveCredentialsToStorage();
                showToast('Connection settings remembered in browser');
            }
        }

        function saveCredentialsToStorage() {
            const data = {
                source_server: document.getElementById('source_server').value,
                source_port: document.getElementById('source_port').value,
                source_username: document.getElementById('source_username').value,
                source_database: document.getElementById('source_database').value,
                target_server: document.getElementById('target_server').value,
                target_port: document.getElementById('target_port').value,
                target_username: document.getElementById('target_username').value,
                target_database: document.getElementById('target_database').value
            };
            localStorage.setItem('mysql_comparator_credentials', JSON.stringify(data));
        }

        function loadSavedCredentials() {
            const saved = localStorage.getItem('mysql_comparator_credentials');
            if (saved) {
                try {
                    const data = JSON.parse(saved);
                    for (const [k, v] of Object.entries(data)) {
                        const el = document.getElementById(k);
                        if (el && v) el.value = v;
                    }
                    document.getElementById('saveCredentialsCheck').checked = true;
                } catch (e) {
                    // Ignore parse error
                }
            }
        }

        // Escape HTML for safe rendering
        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    </script>
</body>

</html>