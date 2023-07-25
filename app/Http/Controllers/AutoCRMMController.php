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

class AutoCRMMController extends Controller
{
    function viewBlade()
    {
        $file = [];
        if (Session::has('sessionn')) {
            $directories = Storage::disk('local')->directories('/folder/' . Session::get('sessionn'));
            foreach ($directories as $key => $value) {
                $file[] = basename($value);
            }
        } else {
            $directories = [];
        }
        return view('me.form', compact('directories'));
    }

    function handelFolserZip()
    {
        // Get real path for our folder
        $rootPath = realpath($this->storagePathBySession());

        $fileName = $this->publicPathBySession();
        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($fileName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

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
    }


    public function makeCRMM(Request $request)
    {
        if (!Session::has('sessionn')) {
            Session::put('sessionn', strtotime(now()));
        }

        $tableNameDuble =   $this->wordToPlural($request->tableNameSingel);
        $folderName = $this->getSession() . '/' . $tableNameDuble;


        $nameFillablesName = $this->migration($request, $tableNameDuble, $folderName);


        // Model
        $this->model($request, $nameFillablesName, $folderName);

        // Validation

        $this->validation($request, $tableNameDuble, $folderName);

        // Controller
        $this->controller($request, $tableNameDuble, $folderName);

        return redirect()->back();
    }


    public function migration($request, $tableNameDuble, $folderName)
    {
        $replace = '';
        $nameFillable = '';
        $replace2 = '';
        for ($i = 0; $i < count($request->name); $i++) {
            if ($request->name[$i] != 'id') {
                $replace .= '$table->increments("id");' . "\n";
            }

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
                if ($m[1] == 'id') {
                    $relation = '->unsigned()';
                    $replace2 =  '$table->foreign("' . $request->name[$i] . '")->references("id")->on("' . $this->wordToPlural($m[0]) . '")->onDelete("cascade")->onUpdate("cascade");';
                }
            }
            $replace .= '$table->' . $request->type[$i] . '("' . $request->name[$i] . '")' . $nullable . $unique . $default . $relation . ';' . "\n";
            $replace .= $replace2 . "\n";
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

        $this->replaceText("nametablemigration", $tableNameDuble, $path);
        return  $nameFillable;
    }


    public function model($request, $nameFillablesName, $folderName)
    {
        $replase1 = '];';
        $replase2 = '];';
        $fileModelMaster = File::get(storage_path('app/master/model.php'));
        $model = str_replace("fillableName", $nameFillablesName, $fileModelMaster);
        $pathModel = 'folder/' . $folderName . '/' . $this->firstCharacterCapitalized($request->tableNameSingel) . '.php';
        Storage::put($pathModel, $model);


        $pathModelRelation = '';

        $this->replaceText('modelName', $this->firstCharacterCapitalized($request->tableNameSingel), $pathModel);

        for ($i = 0; $i < count($request->name); $i++) {
            if (Str::containsAll($request->name[$i], ['_']) == 1) {
                $m = explode('_', $request->name[$i]);

                $pathModelRelation = 'folder/' . Session::get('sessionn') . '/' . $this->wordToPlural($m[0]) . '/' . $this->firstCharacterCapitalized($m[0])  . '.php';
                $replase2 = '];public function ' . $request->tableNameSingel . '()
                    {
                        return $this->hasMany(' . $this->firstCharacterCapitalized($request->tableNameSingel) . '::class, "' . $request->name[$i] . '");
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
        $pathValidationStore = 'folder/' . $folderName .  '/' . 'Store' . $this->firstCharacterCapitalized($request->tableNameSingel) . 'Request.php';
        Storage::put($pathValidationStore, $validationStore);

        $this->replaceText('category',  $this->firstCharacterCapitalized($request->tableNameSingel), $pathValidationStore);
        $this->replaceText('categories', $tableNameDuble, $pathValidationStore);

        $fileValidationUpdateMaster = File::get(storage_path('app/master/validationUpdate.php'));
        $validationUpdate = str_replace("hear", $validationUpdate, $fileValidationUpdateMaster);
        $pathValidationUpdate = 'folder/' . $folderName .  '/' . 'Update' . $this->firstCharacterCapitalized($request->tableNameSingel) . 'Request.php';
        Storage::put($pathValidationUpdate, $validationUpdate);


        $this->replaceText("->id'", "->id",  $pathValidationUpdate);
        $this->replaceText('category', $this->firstCharacterCapitalized($request->tableNameSingel),  $pathValidationUpdate);
        $this->replaceText('categories', $tableNameDuble,  $pathValidationUpdate);
    }

    public function controller($request, $tableNameDuble, $folderName)
    {
        // Cheng Name Model And NameSpase
        $fileControllerMaster = File::get(storage_path('app/master/controller.php'));
        $validationStore = str_replace("Category", $this->firstCharacterCapitalized($request->tableNameSingel), $fileControllerMaster);
        $pathController = 'folder/'  . $folderName . '/' . $this->firstCharacterCapitalized($request->tableNameSingel) . 'Controller.php';
        Storage::put($pathController, $validationStore);

        // cheng All Varabile Dubll
        $this->replaceText('categories', $tableNameDuble,  $pathController);
        // Cheng All Varabile Singel
        $this->replaceText('category', $this->characterLower($request->tableNameSingel) ,  $pathController);
    }

    public function replaceText($testReplacr, $replaceTo, $path)
    {
        $fileModelNew = File::get(storage_path('app/' . $path));
        $nameTable = str_replace($testReplacr, $replaceTo, $fileModelNew);
        Storage::put($path, $nameTable);
    }

    public function deleteZipFile()
    {
        Session::forget('sessionn');

        return redirect()->back();
    }

    public function downloadFolders()
    {
        $fileName = $this->publicPathBySession();

        return response()->download($fileName);
    }
    function storagePathBySession()
    {
        return  storage_path('app/folder/' . $this->getSession());
    }

    function getSession()
    {
        return  Session::get('sessionn');
    }

    function publicPathBySession()
    {
        return  public_path('zip/' . $this->getSession() . '.zip');
    }

    // اول حرف كابتل
    function  firstCharacterCapitalized($name)
    {
        return Str::ucfirst($name);
    }


    // تصغير كل الحروف
    function  characterLower($name)
    {
        return Str::lower($name);
    }

    // اسم جمع
    function  wordToPlural($name)
    {
        $name = $this->characterLower($name);
        return Str::plural($name);
    }
}
