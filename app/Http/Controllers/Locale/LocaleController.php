<?php

namespace App\Http\Controllers\Locale;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Pointdeb\Translator\Translation;

class LocaleController extends Controller
{
    public function fromFile(Request $request)
    {
        $request->validate(['locale_file' => 'required|file']);
        $filename = $request->file('locale_file')->store('excels', 'local');
        $outputPath = resource_path('lang');
        $excel = storage_path('app/'. $filename);
        // $outputPath = storage_path('app/public'); // activate on testing
        $result = Translation::saveToFileFromExcel($excel, $outputPath);
        return response(null, 204);
    }

    public function translate(Request $result, string $key, string $locale="en")
    {
        app()->setLocale($locale);
        return response(__($key), 200);
    }
}
