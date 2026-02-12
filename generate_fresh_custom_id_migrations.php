<?php

/**
 * Generate fresh migrations with custom IDs as primary keys
 */

$migrationsPath = __DIR__ . '/database/migrations_custom_id';

// Create directory if not exists
if (!is_dir($migrationsPath)) {
    mkdir($migrationsPath, 0755, true);
}

// Table definitions with custom IDs
$tables = [
    'users' => [
        'pk' => 'user_id',
        'prefix' => 'USR',
        'columns' => [
            'name' => 'string',
            'email' => 'string:unique',
            'password' => 'string',
            'email_verified_at' => 'timestamp:nullable',
            'role' => 'string:default:admin',
            'remember_token' => 'rememberToken',
        ],
    ],
    'plant_types' => [
        'pk' => 'plant_type_id',
        'prefix' => 'PTY',
        'columns' => [
            'name' => 'string',
            'category' => 'string:nullable',
            'variety' => 'string:nullable',
        ],
    ],
    'plants' => [
        'pk' => 'plant_id',
        'prefix' => 'PLT',
        'columns' => [
            'name' => 'string',
            'plant_type_id' => 'string:36:nullable:foreign:plant_types',
            'variety' => 'string:nullable',
            'status' => 'enum:perencanaan,ditanam,dipanen,selesai:default:perencanaan',
            'progress' => 'unsignedTinyInteger:default:0',
            'planting_location_id' => 'string:36:nullable',
        ],
    ],
    'planting_locations' => [
        'pk' => 'planting_location_id',
        'prefix' => 'LOC',
        'columns' => [
            'name' => 'string',
            'location_type' => 'string:nullable',
            'address' => 'text:nullable',
            'area_hectares' => 'decimal:10,2:nullable',
        ],
    ],
    // Add more tables as needed...
];

$timestamp = date('Y_m_d_His');
$counter = 0;

foreach ($tables as $tableName => $config) {
    $counter++;
    $migrationName = sprintf(
        '%s_%06d_create_%s_table_with_custom_id.php',
        $timestamp,
        $counter,
        $tableName
    );
    
    $className = 'Create' . str_replace('_', '', ucwords($tableName, '_')) . 'TableWithCustomId';
    
    $content = generateMigrationContent($tableName, $config, $className);
    
    file_put_contents($migrationsPath . '/' . $migrationName, $content);
    echo "✓ Created: $migrationName\n";
}

function generateMigrationContent($tableName, $config, $className) {
    $pk = $config['pk'];
    $prefix = $config['prefix'];
    
    $columnsCode = '';
    foreach ($config['columns'] as $columnName => $columnDef) {
        $parts = explode(':', $columnDef);
        $type = $parts[0];
        
        if ($type === 'string') {
            $length = isset($parts[1]) && is_numeric($parts[1]) ? $parts[1] : 255;
            $columnsCode .= "            \$table->string('$columnName', $length)";
        } elseif ($type === 'enum') {
            $values = explode(',', $parts[1]);
            $valuesStr = "'" . implode("','", $values) . "'";
            $columnsCode .= "            \$table->enum('$columnName', [$valuesStr])";
        } elseif ($type === 'foreign') {
            $refTable = $parts[2];
            $columnsCode .= "            \$table->string('$columnName', 36)->nullable()";
        } else {
            $columnsCode .= "            \$table->$type('$columnName')";
        }
        
        // Add modifiers
        for ($i = 1; $i < count($parts); $i++) {
            if ($parts[$i] === 'nullable') {
                $columnsCode .= "->nullable()";
            } elseif ($parts[$i] === 'unique') {
                $columnsCode .= "->unique()";
            } elseif (strpos($parts[$i], 'default:') === 0) {
                $default = str_replace('default:', '', $parts[$i]);
                $columnsCode .= "->default('$default')";
            }
        }
        
        $columnsCode .= ";\n";
    }
    
    return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('$tableName', function (Blueprint \$table) {
            \$table->string('$pk', 36)->primary();
$columnsCode            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('$tableName');
    }
};
PHP;
}

echo "\n========================================\n";
echo "Fresh migrations with custom IDs created!\n";
echo "Location: $migrationsPath\n";
echo "========================================\n";
