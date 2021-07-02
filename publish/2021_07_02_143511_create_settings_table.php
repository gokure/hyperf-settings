<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    protected $table;

    protected $keyColumn;

    protected $valueColumn;

    public function __construct()
    {
        $this->table = config('settings.default.database.table', 'settings');
        $this->keyColumn = config('settings.default.database.key_column', 'key');
        $this->valueColumn = config('settings.default.database.value_column', 'value');
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string($this->keyColumn)->unique();
            $table->text($this->valueColumn);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
}
