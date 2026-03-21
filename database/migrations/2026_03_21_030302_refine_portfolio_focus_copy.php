<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_HERO_KICKER = 'Currently focused on in-house product work';

    private const NEW_HERO_KICKER = 'Focused on long-term product systems';

    private const OLD_FOCUS_TEXT = 'Not available for freelance or contract projects at this time.';

    private const NEW_FOCUS_TEXT = 'Laravel architecture, operational software, and durable internal tools.';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('portfolio_settings')
            ->where('hero_kicker', self::OLD_HERO_KICKER)
            ->update([
                'hero_kicker' => self::NEW_HERO_KICKER,
                'updated_at' => now(),
            ]);

        DB::table('portfolio_settings')
            ->where('availability_text', self::OLD_FOCUS_TEXT)
            ->update([
                'availability_text' => self::NEW_FOCUS_TEXT,
                'updated_at' => now(),
            ]);

        Schema::table('portfolio_settings', function (Blueprint $table) {
            $table->string('hero_kicker')->default(self::NEW_HERO_KICKER)->change();
            $table->string('availability_text')->default(self::NEW_FOCUS_TEXT)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('portfolio_settings')
            ->where('hero_kicker', self::NEW_HERO_KICKER)
            ->update([
                'hero_kicker' => self::OLD_HERO_KICKER,
                'updated_at' => now(),
            ]);

        DB::table('portfolio_settings')
            ->where('availability_text', self::NEW_FOCUS_TEXT)
            ->update([
                'availability_text' => self::OLD_FOCUS_TEXT,
                'updated_at' => now(),
            ]);

        Schema::table('portfolio_settings', function (Blueprint $table) {
            $table->string('hero_kicker')->default(self::OLD_HERO_KICKER)->change();
            $table->string('availability_text')->default(self::OLD_FOCUS_TEXT)->change();
        });
    }
};
