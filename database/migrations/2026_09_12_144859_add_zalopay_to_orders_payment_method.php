<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $column = DB::selectOne(
            "SHOW COLUMNS FROM `orders` WHERE Field = 'payment_method'"
        );

        $type = $column->Type ?? null;

        if (!$type || !str_starts_with(strtolower($type), 'enum(')) {
            return;
        }

        preg_match_all("/'([^']+)'/", $type, $matches);

        $values = $matches[1] ?? [];

        if (!in_array('zalopay', $values, true)) {
            $values[] = 'zalopay';
        }

        $enum = implode(
            ',',
            array_map(
                fn ($value) => "'" . str_replace("'", "''", $value) . "'",
                $values
            )
        );

        DB::statement(
            "ALTER TABLE `orders`
             MODIFY `payment_method`
             ENUM($enum)
             NOT NULL DEFAULT 'cod'"
        );
    }

    public function down(): void
    {
        DB::table('orders')
            ->where('payment_method', 'zalopay')
            ->update(['payment_method' => 'cod']);

        $column = DB::selectOne(
            "SHOW COLUMNS FROM `orders` WHERE Field = 'payment_method'"
        );

        $type = $column->Type ?? null;

        if (!$type || !str_starts_with(strtolower($type), 'enum(')) {
            return;
        }

        preg_match_all("/'([^']+)'/", $type, $matches);

        $values = array_values(
            array_filter(
                $matches[1] ?? [],
                fn ($value) => $value !== 'zalopay'
            )
        );

        $enum = implode(
            ',',
            array_map(fn ($value) => "'$value'", $values)
        );

        DB::statement(
            "ALTER TABLE `orders`
             MODIFY `payment_method`
             ENUM($enum)
             NOT NULL DEFAULT 'cod'"
        );
    }
};