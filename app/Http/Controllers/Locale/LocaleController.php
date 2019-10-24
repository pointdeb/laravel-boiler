<?php

namespace App\Http\Controllers\Locale;

use App\Dictionary;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Pointdeb\Translator\Translation;

class LocaleController extends Controller
{
    public function fromFile(Request $request)
    {
        $request->validate(['locale_file' => 'required|file']);
        $filename = $request->file('locale_file')->store('excels', 'local');
        $excel = storage_path('app/' . $filename);
        if (env('APP_ENV', 'testing')) {
            $outputPath = storage_path('app/testing/lang');
        } else {
            $outputPath = resource_path('lang');
        }
        // $outputPath = storage_path('app/public'); // activate on testing
        // $result = Translation::saveToFileFromExcel($excel, $outputPath);
        $contents = Translation::getFromExcel($excel);
        foreach ($contents as $key => $values) {
            foreach ($values as $locale => $value) {
                $dictionary = Dictionary::where(['key' => $key, 'locale' => $locale])->first();
                $data = ['key' => $key, 'value' => $value, 'locale' => $locale];
                if ($dictionary) {
                    $dictionary->update($data);
                    $dictionary->save();
                } else {
                    $dictionary = Dictionary::create($data);
                }
            }
        }
        return response(null, 204);
    }

    public function translate(Request $request, string $key, string $locale = "en")
    {
        app()->setLocale($locale);
        return response(__($key, $request->input()), 200);
    }


    public function index(Request $request)
    {
        if (count(Input::all()) == 0) {
            return Dictionary::paginate($request->input('per_page') ?? 10)->withPath('');
        }

        $query = null;
        $this->ignoreSearchFields = array_merge($this->ignoreSearchFields, []);

        foreach ($request->input() as $key => $value) {
            if (in_array($key, $this->ignoreSearchFields)) {
                continue;
            }
            if (!$query) {
                $query = Dictionary::where($key, 'like', '%' . $value . '%');
                continue;
            }
            $query = $query->orWhere($key, 'like', '%' . $value . '%');
        }

        if ($query == null) {
            $dictionaries = Dictionary::paginate($request->input('per_page') ?? 10)->withPath('');
        } else {
            $dictionaries = $query->paginate($request->input('per_page') ?? 10)->withPath('');
        }
        return response($dictionaries);
    }

    public function show(Request $request, int $dictionary_id)
    {
        return Dictionary::withUsers()->findOrFail($dictionary_id);
    }

    public function store(Request $request)
    {
        Dictionary::validator($request->input())->validate();
        $dictionary = Dictionary::create($request->input());
        return response($dictionary, 201);
    }

    public function update(Request $request, int $dictionary_id)
    {
        Dictionary::validator($request->input())->validate();
        $dictionary = Dictionary::findOrFail($dictionary_id);
        $dictionary->update($request->input());
        $dictionary->save();
        return response($dictionary, 200);
    }

    public function destroy(Request $request, int $dictionary_id)
    {
        $dictionary = Dictionary::findOrFail($dictionary_id);
        $dictionary->delete();
        return response($dictionary, 200);
    }
}
