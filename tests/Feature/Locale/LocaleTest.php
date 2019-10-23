<?php

namespace Tests\Feature\Locale;

use App\Dictionary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Pointdeb\Translator\Translation;
use Illuminate\Support\Facades\Storage;
use Tests\ActingAs;
use Tests\SeedDatabase;

class LocaleTest extends TestCase
{
    use RefreshDatabase, SeedDatabase, ActingAs;

    public function testLocaleFromExcel()
    {
        $this->getActingAs(true);
        $excel = storage_path('app/testing/locales.xlsx');
        $outputPath = storage_path('app/testing/lang');
        is_dir($outputPath) || mkdir($outputPath);
        $file = new UploadedFile($excel,'locales.xlsx', null, null, true);
        $response = $this->json('POST', route('locale.from-file'), ['locale_file' => $file]);
        $this->assertEquals(204, $response->status(), $response->getContent());
        $expectedFile = realpath("{$outputPath}/en/about.php");
        $this->assertTrue(is_file($expectedFile), $expectedFile);
        $this->assertDatabaseHas('dictionaries', [
            'key' => 'about.contact',
            'value' => 'Contact',
            'locale' => 'en'
        ]);
        exec("rm -rf {$outputPath}");
    }

    public function testLocaleTranslateApi()
    {
        $response = $this->json('GET', route('locale.translate', ['locale' => 'fr', 'key' => 'auth.failed']));
        $this->assertEquals(200, $response->status());
        $this->assertEquals("Ces informations d'identification ne correspondent pas à nos enregistrements.", $response->getContent(), $response->getContent());
    }

    public function testLocaleUpdate()
    {
        $this->getActingAs(true);
        $dictionary = factory(Dictionary::class)->create()->toArray();
        $dictionary['value'] .= '_modified';
        $response = $this->json('PUT', route('locale.dictionaries.update', ['dictionary_id' => $dictionary['dictionary_id']]), $dictionary);
        $this->assertEquals(200, $response->status(), $response->getContent());
        $responseObj = json_decode($response->getContent(), true);
        $this->assertEquals($dictionary['value'], $responseObj['value']);
    }

    public function testLocaleDelete()
    {
        $this->getActingAs(true);
        $dictionary = factory(Dictionary::class)->create();
        $response = $this->json('DELETE', route('locale.dictionaries.destroy', ['dictionary_id' => $dictionary['dictionary_id']]));
        $this->assertEquals(200, $response->status(), $response->getContent());
        $this->assertNull(Dictionary::find($dictionary->dictionary_id));
    }

    public function testLocaleGetAll()
    {
        factory(Dictionary::class, 120)->create();
        $this->getActingAs(true);
        $response = $this->json('GET', route('locale.dictionaries.index'));
        $this->assertEquals(200, $response->status(), $response->getContent());
        $responseObj = json_decode($response->getContent());
        $this->assertEquals(10, count($responseObj->data));
        $this->assertNotContains('http', $responseObj->first_page_url);
    }
}
