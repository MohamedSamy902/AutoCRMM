<?php

namespace App\Http\Controllers;

use ZipArchive;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class CRMMContoller extends Controller
{
    function makeTable()
    {
        // Session::forget('sessionn');
        return view('welcome');
    }

    function makeTableRequest(Request $request)
    {
        if (Session::has('sessionn')) {
            Session::forget('sessionn');
        }

        $tableNameDuble =  Str::plural($request->tableNameSingel);
        Session::put('sessionn', $tableNameDuble . strtotime(now()));
        $folderName = $tableNameDuble . strtotime(now());
        $nameFillablesName = $this->migration($request, $tableNameDuble, $folderName);


        // Model
        $this->model($request, $nameFillablesName, $folderName);

        // Validation

        $this->validation($request, $tableNameDuble, $folderName);

        // Controller
        $this->controller($request, $tableNameDuble, $folderName);



        $filename = storage_path('app/folder/' . Session::get('sessionn'));

        $zip = new ZipArchive;

        $fileName = 'zip/' . Session::get('sessionn') . '.zip';

        if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE) {

            $files = File::files($filename);

            foreach ($files as $key => $value) {
                $file = basename($value);
                $zip->addFile($value, $file);
            }

            $zip->close();
        }
        Session::forget('sessionn');
        return response()->download(public_path($fileName));
    }

    public function replaceText($testReplacr, $replaceTo, $path)
    {
        $fileModelNew = File::get(storage_path('app/' . $path));
        $nameTable = str_replace($testReplacr, $replaceTo, $fileModelNew);
        Storage::put($path, $nameTable);
    }


    public function migration($request, $tableNameDuble, $folderName)
    {

        $replace = '$table->increments("id");' . "\n";
        $nameFillable = '';
        for ($i = 0; $i < count($request->name); $i++) {
            $nameFillable .= '\'' . $request->name[$i] . '\',';
            $nullable = '';
            $unique = '';
            $default = '';
            if (isset($request->unique[$i])) {
                $unique = '->unique()';
            }

            if (isset($request->null[$i])) {
                $nullable = '->nullable()';
            }

            if (isset($request->default[$i])) {
                $default = '->default("' . $request->default[$i] . '")';
            }
            $replace .= '$table->' . $request->type[$i] . '("' . $request->name[$i] . '")' . $nullable . $unique . $default . ';';
        }

        if ($request->RememberToken) {
            $replace .= '$table->rememberToken();' . "\n";
        }

        if ($request->Timestamps) {
            $replace .= '$table->timestamps();' . "\n";
        }


        $search = 'hear;'; // the content after which you want to insert new stuff

        // This Master File
        $fileMigateMaster = File::get(storage_path('app/master/migration.php'));

        // Cheng Content
        $table = str_replace("'hear';", $replace, $fileMigateMaster);

        $fileMigrationName = date('Y_m_d', strtotime(now())) . '_000000_create_' . $tableNameDuble . '_table.php';
        $path = 'folder/' . $folderName . '/' . $fileMigrationName;


        Storage::put($path, $table);


        $fileMigateNew = File::get(storage_path('app/' . $path));
        $nameTable = str_replace("users", $tableNameDuble, $fileMigateNew);

        Storage::put($path, $nameTable);
        return  $nameFillable;
    }

    public function model($request, $nameFillablesName, $folderName)
    {
        $fileModelMaster = File::get(storage_path('app/master/model.php'));
        $model = str_replace("fillableName", $nameFillablesName, $fileModelMaster);
        $pathModel = 'folder/' . $folderName . '/' . Str::ucfirst($request->tableNameSingel) . '.php';
        Storage::put($pathModel, $model);

        $this->replaceText('modelName', Str::ucfirst($request->tableNameSingel), $pathModel);
    }

    public function validation($request, $tableNameDuble, $folderName)
    {
        $validation = '';
        $validationUpdate = '';
        for ($i = 0; $i < count($request->name); $i++) {
            $test = '\'' . $request->name[$i] . '\'' . '=>' . '\'';

            if (isset($request->null[$i])) {
                $nullable = 'nullable';
            } else {
                $nullable = 'required';
            }

            if ($request->type[$i] == 'string') {
                $type = '|string';
            }

            if (isset($request->unique[$i])) {
                $unique = '|unique:' . $tableNameDuble . ',' . $request->name[$i];
                $uniqueUpdate = '|unique:' . $tableNameDuble . ',' . $request->name[$i] . ',\'' . '.' . '$this->' . $request->tableNameSingel . '->id';
            } else {
                $unique = '';
                $uniqueUpdate = '';
            }


            if ($request->type[$i] == 'integer') {
                $type = '|integer';
            }

            if ($request->type[$i] == 'image') {
                $type = '|image|mimes:jpeg,png,jpg,gif,svg|max:20408';
            }

            $validation .= $test . $nullable . $type . $unique . '\',';
            $validationUpdate .= $test . $nullable . $type .  $uniqueUpdate . '\',';
        }

        $fileValidationStoreMaster = File::get(storage_path('app/master/validationStore.php'));
        $validationStore = str_replace("hear", $validation, $fileValidationStoreMaster);
        $pathValidationStore = 'folder/' . $folderName .  '/' . 'Store' . Str::ucfirst($request->tableNameSingel) . 'Request.php';
        Storage::put($pathValidationStore, $validationStore);

        $this->replaceText('category', Str::ucfirst($request->tableNameSingel), $pathValidationStore);
        $this->replaceText('categories', $tableNameDuble, $pathValidationStore);

        $fileValidationUpdateMaster = File::get(storage_path('app/master/validationUpdate.php'));
        $validationUpdate = str_replace("hear", $validationUpdate, $fileValidationUpdateMaster);
        $pathValidationUpdate = 'folder/' . $folderName .  '/' . 'Update' . Str::ucfirst($request->tableNameSingel) . 'Request.php';
        Storage::put($pathValidationUpdate, $validationUpdate);


        $this->replaceText("->id'", "->id",  $pathValidationUpdate);
        $this->replaceText('category', Str::ucfirst($request->tableNameSingel),  $pathValidationUpdate);
        $this->replaceText('categories', $tableNameDuble,  $pathValidationUpdate);
    }


    public function controller($request, $tableNameDuble, $folderName)
    {
        // Cheng Name Model And NameSpase
        $fileControllerMaster = File::get(storage_path('app/master/controller.php'));
        $validationStore = str_replace("Category", Str::ucfirst($request->tableNameSingel), $fileControllerMaster);
        $pathController = 'folder/'  . $folderName . '/' . Str::ucfirst($request->tableNameSingel) . 'Controller.php';
        Storage::put($pathController, $validationStore);

        // cheng All Varabile Dubll
        $this->replaceText('categories', $tableNameDuble,  $pathController);
        // $fileModelNew = File::get(storage_path('app/' . $pathController));
        // $nameTable = str_replace("categories", $tableNameDuble, $fileModelNew);
        // Storage::put($pathController, $nameTable);

        // Cheng All Varabile Singel
        $this->replaceText('category', $request->tableNameSingel,  $pathController);

        // $fileModelNew = File::get(storage_path('app/' . $pathController));
        // $mnameTablee = str_replace("category", $request->tableNameSingel, $fileModelNew);
        // Storage::put($pathController, $mnameTablee);
    }
}
