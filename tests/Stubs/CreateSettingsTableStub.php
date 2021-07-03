<?php

namespace Gokure\Settings\Tests\Stubs;

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateSettingsTableStub extends Migration
{
    protected $table;

    protected $keyColumn;

    protected $valueColumn;

    public function __construct($table = null, $keyColumn = null, $valueColumn = null)
    {
        $this->table = $table;
        $this->keyColumn = $keyColumn;
        $this->valueColumn = $valueColumn;
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
