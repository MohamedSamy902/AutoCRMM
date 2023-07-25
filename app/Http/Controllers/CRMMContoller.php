<?php

namespace App\Http\Controllers;

use ZipArchive;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class CRMMContoller extends Controller
{
    function makeTable()
    {
        $file = [];
        if (Session::has('sessionn')) {
            $directories = Storage::disk('local')->directories('/folder/' . Session::get('sessionn'));
            // return $directories;
            foreach ($directories as $key => $value) {
                $file[] = basename($value);
            }
        }else {
            $directories = [];
        }

        return view('me.form', compact('directories'));
    }

    function handelFolserZip()
    {

        // return Session::get('sessionn');
        // Get real path for our folder
        $rootPath = realpath(storage_path('app/folder/' . Session::get('sessionn')));

        $fileName = public_path('zip/' . Session::get('sessionn') . '.zip');
        // Initialize archive object
        $zip = new ZipArchive();

        $zip->open($fileName, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        // return $fileName;


        // Create recursive directory iterator

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        return redirect()->route('downloadFolders');
        // return response()->download($fileName);
    }

    public function downloadFolders()
    {
        $fileName = public_path('zip/' . Session::get('sessionn') . '.zip');

        return response()->download($fileName);
    }

    public function deleteZipFile()
    {
        Session::forget('sessionn');

        return redirect()->back();
    }

    function makeTableRequest(Request $request)
    {
        if (!Session::has('sessionn')) {
            Session::put('sessionn', strtotime(now()));
        }

        $tableNameDuble =  Str::plural($request->tableNameSingel);
        $folderName = Session::get('sessionn') . '/' . $tableNameDuble;


        $nameFillablesName = $this->migration($request, $tableNameDuble, $folderName);


        // Model
        $this->model($request, $nameFillablesName, $folderName);

        // Validation

        $this->validation($request, $tableNameDuble, $folderName);

        // Controller
        $this->controller($request, $tableNameDuble, $folderName);

        return redirect()->back();
    }

    public function replaceText($testReplacr, $replaceTo, $path)
    {
        $fileModelNew = File::get(storage_path('app/' . $path));
        $nameTable = str_replace($testReplacr, $replaceTo, $fileModelNew);
        Storage::put($path, $nameTable);
    }


    public function migration($request, $tableNameDuble, $folderName)
    {
        // return 'good';

        $replace = '$table->increments("id");' . "\n";
        $nameFillable = '';
        $replace2 = '';
        for ($i = 0; $i < count($request->name); $i++) {
            $nameFillable .= '\'' . $request->name[$i] . '\',';
            $nullable = '';
            $unique = '';
            $relation = '';
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

            if (Str::containsAll($request->name[$i], ['_']) == 1) {
                $m = explode('_', $request->name[$i]);
                $relation = '->unsigned()';
                $replace2 =  '$table->foreign("' . $request->name[$i] . '")->references("id")->on("' . Str::plural($m[0]) . '")->onDelete("cascade")->onUpdate("cascade");';
            }
            $replace .= '$table->' . $request->type[$i] . '("' . $request->name[$i] . '")' . $nullable . $unique . $default . $relation . ';';
            $replace .= $replace2;
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
        $nameTable = str_replace("nametablemigration", $tableNameDuble, $fileMigateNew);

        Storage::put($path, $nameTable);
        return  $nameFillable;
    }

    public function model($request, $nameFillablesName, $folderName)
    {
        $replase1 = '];';
        $replase2 = '];';
        $fileModelMaster = File::get(storage_path('app/master/model.php'));
        $model = str_replace("fillableName", $nameFillablesName, $fileModelMaster);
        $pathModel = 'folder/' . $folderName . '/' . Str::ucfirst($request->tableNameSingel) . '.php';
        Storage::put($pathModel, $model);


        $pathModelRelation = '';

        $this->replaceText('modelName', Str::ucfirst($request->tableNameSingel), $pathModel);

        for ($i = 0; $i < count($request->name); $i++) {
            if (Str::containsAll($request->name[$i], ['_']) == 1) {
                $m = explode('_', $request->name[$i]);

                // $this->replaceText('];', $replase1, $pathModel);







                $pathModelRelation = 'folder/' . Session::get('sessionn') . '/' . Str::plural($m[0]) . '/' . Str::ucfirst($m[0]) . '.php';
                $replase2 = '];public function ' . $request->tableNameSingel . '()
                    {
                        return $this->hasMany(' . Str::ucfirst($request->tableNameSingel) . '::class, "' . $request->name[$i] . '");
                    }';

                $this->replaceText('];', $replase2, $pathModelRelation);


                $replase1 = '];public function ' . $m[0] . '()
                    {
                        return $this->belongsTo(' . Str::ucfirst($m[0]) . '::class);
                    }';
                $this->replaceText('];', $replase1, $pathModel);
            }
        }
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
