<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        $locale = app()->getFallbackLocale();

        DB::statement("
CREATE OR REPLACE VIEW dictionaries_view AS
SELECT
    d.id,
    d.slug,
    JSON_UNQUOTE(JSON_EXTRACT(d.name, '$.$locale')) AS name,
    d.type,
    COALESCE(entries.entries_count, 0) AS entries_count
FROM dictionnaries d
LEFT JOIN (
    SELECT
        dictionnary_id,
        COUNT(*) AS entries_count
    FROM dictionnary_entries
    WHERE parent IS NULL AND deleted_at IS NULL
    GROUP BY dictionnary_id
) AS entries ON d.id = entries.dictionnary_id
");


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS dictionnaries_view");
    }
};
