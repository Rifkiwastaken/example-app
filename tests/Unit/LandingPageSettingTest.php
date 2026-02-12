<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\LandingPageSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit Test untuk Model LandingPageSetting
 *
 * Menguji semua method dan perilaku model LandingPageSetting:
 * - create dengan fillable (key, value)
 * - getValue(key, default): mengambil nilai by key, fallback default
 * - setValue(key, value): update atau create by key
 * - getAllSettings(): pluck value by key sebagai array
 */
class LandingPageSettingTest extends TestCase
{
    use RefreshDatabase;

    /** Test: Model dapat dibuat dengan field key dan value */
    public function test_can_create_landing_page_setting_with_key_and_value(): void
    {
        $setting = LandingPageSetting::create([
            'key' => 'hero_title',
            'value' => 'Judul Hero Test',
        ]);

        $this->assertDatabaseHas('landing_page_settings', [
            'key' => 'hero_title',
            'value' => 'Judul Hero Test',
        ]);
        $this->assertSame('hero_title', $setting->key);
        $this->assertSame('Judul Hero Test', $setting->value);
    }

    /** Test: getValue() mengembalikan value untuk key yang ada */
    public function test_get_value_returns_value_when_key_exists(): void
    {
        LandingPageSetting::create([
            'key' => 'office_phone',
            'value' => '(0751) 999888',
        ]);

        $result = LandingPageSetting::getValue('office_phone', '');

        $this->assertSame('(0751) 999888', $result);
    }

    /** Test: getValue() mengembalikan default bila key tidak ada */
    public function test_get_value_returns_default_when_key_does_not_exist(): void
    {
        $result = LandingPageSetting::getValue('nonexistent_key', 'default_value');

        $this->assertSame('default_value', $result);
    }

    /** Test: setValue() membuat record baru bila key belum ada */
    public function test_set_value_creates_record_when_key_does_not_exist(): void
    {
        LandingPageSetting::setValue('new_key', 'new_value');

        $this->assertDatabaseHas('landing_page_settings', [
            'key' => 'new_key',
            'value' => 'new_value',
        ]);
        $this->assertSame('new_value', LandingPageSetting::getValue('new_key', ''));
    }

    /** Test: setValue() mengupdate value bila key sudah ada */
    public function test_set_value_updates_record_when_key_exists(): void
    {
        LandingPageSetting::create(['key' => 'hero_title', 'value' => 'Lama']);
        LandingPageSetting::setValue('hero_title', 'Baru');

        $this->assertDatabaseHas('landing_page_settings', [
            'key' => 'hero_title',
            'value' => 'Baru',
        ]);
        $this->assertSame('Baru', LandingPageSetting::getValue('hero_title', ''));
    }

    /** Test: getAllSettings() mengembalikan array key => value untuk semua setting */
    public function test_get_all_settings_returns_key_value_array(): void
    {
        LandingPageSetting::create(['key' => 'a', 'value' => '1']);
        LandingPageSetting::create(['key' => 'b', 'value' => '2']);

        $all = LandingPageSetting::getAllSettings();

        $this->assertIsArray($all);
        $this->assertArrayHasKey('a', $all);
        $this->assertArrayHasKey('b', $all);
        $this->assertSame('1', $all['a']);
        $this->assertSame('2', $all['b']);
    }

    /** Test: getAllSettings() mengembalikan array kosong bila tidak ada data */
    public function test_get_all_settings_returns_empty_array_when_no_settings(): void
    {
        $all = LandingPageSetting::getAllSettings();

        $this->assertIsArray($all);
        $this->assertEmpty($all);
    }
}
