<?php

namespace Tests\Feature\Locale;

use Tests\TestCase;
use Pointdeb\Translator\Translation;
use Illuminate\Support\Facades\Storage;

class LocaleTest extends TestCase
{

    public function testLocaleFromExcel()
    {
        $excel = storage_path('app/testing/locales.xlsx');
        $outputPath = storage_path('app/public');
        $result = Translation::saveToFileFromExcel($excel, $outputPath);
        $expectedFile = '/en/about.php';
        $this->assertTrue(Storage::disk('public')->exists($expectedFile), $expectedFile);
    }

    public function testLocaleTranslateApi()
    {
        $response = $this->json('GET', route('locale.translate', ['locale' => 'fr', 'key' => 'auth.failed']));
        $this->assertEquals(200, $response->status());
        $this->assertEquals("Ces informations d'identification ne correspondent pas à nos enregistrements.", $response->getContent(), $response->getContent());
    }
}
