<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class StorageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // public function store_image1($file, $folder, $size = 1200){

    //     try {
    //         Storage::makeDirectory($folder.'/'.date('F').date('Y'));
    //         $base_name = Str::random(20).'day'.date('d').date('a');

    //         // imagen normal
    //         $extension = 'avif'/* $file->getClientOriginalExtension()*/;
    //         $filename = $base_name.'.'.$extension;
    //         $path =  $folder.'/'.date('F').date('Y').'/'.$filename;
    //         $image_resize = Image::make($file->getRealPath())->orientate();
    //         $image_resize->resize($size, null, function ($constraint) {
    //             $constraint->aspectRatio();
    //         });
    //         Storage::put($path, $image_resize->encode('avif', 80));

    //         $original = Image::make($file->getRealPath())->orientate();
    //         // imagen banner
    //         $filename_banner = $base_name.'-banner.'.$extension;
    //         $image_resize = $original;
    //         $image_resize->resize(900, null, function ($constraint) {
    //             $constraint->aspectRatio();
    //         });
    //         $path_banner = "$folder/".date('F').date('Y').'/'.$filename_banner;
    //         Storage::put($path_banner, $image_resize->encode('avif', 80));

    //         // imagen medium
    //         $filename_medium = $base_name.'-medium.'.$extension;
    //         $image_resize = $original;
    //         $image_resize->resize(600, null, function ($constraint) {
    //             $constraint->aspectRatio();
    //         });
    //         $path_medium = "$folder/".date('F').date('Y').'/'.$filename_medium;
    //         Storage::put($path_medium, $image_resize->encode('avif', 80));

    //         // imagen small
    //         $filename_small = $base_name.'-small.'.$extension;
    //         $image_resize = $original;
    //         $image_resize->resize(256, null, function ($constraint) {
    //             $constraint->aspectRatio();
    //         });
    //         $path_small = "$folder/".date('F').date('Y').'/'.$filename_small;
    //         Storage::put($path_small, $image_resize->encode('avif', 80));

    //         // imagen cropped
    //         $filename_cropped = $base_name.'-cropped.'.$extension;
    //         $image_resize = $original;
    //         $image_resize->resize(null, 300, function ($constraint) {
    //             $constraint->aspectRatio();
    //         });
    //         $image_resize->resizeCanvas(300, 300);
    //         $path_cropped = "$folder/".date('F').date('Y').'/'.$filename_cropped;
    //         Storage::put($path_cropped, $image_resize->encode('avif', 80));

    //         if(env('FILESYSTEM_DRIVER') == 's3'){
    //             return env('AWS_ENDPOINT').'/'.env('AWS_BUCKET').'/'.env('AWS_ROOT').'/'.$path;    
    //         }
    //         return $path;
    //     } catch (\Throwable $th) {
    //         \Log::error('Error al guardar la imagen: ' . $th->getMessage());
    //         return null;
    //     }
    // }

    public function store_image($file, $folder, $size = 1200)
    {
        try {
            if (!$file || !$file->isValid()) {
                throw new \Exception("Archivo no válido");
            }

            $monthYear = date('FY');
            $directory = "{$folder}/{$monthYear}/";
            $baseName = Str::random(20).'day'.date('d').date('a');

            $extension = 'avif';

            Storage::makeDirectory($directory);

            // Cargar la imagen una sola vez
            $originalImage = Image::make($file->getRealPath())->orientate();

            // Configuraciones para cada versión
            $versions = [
                '' => ['size' => $size, 'quality' => 80], // versión normal
                '-banner' => ['width' => 900, 'quality' => 80],
                '-medium' => ['width' => 600, 'quality' => 80],
                '-small' => ['width' => 256, 'quality' => 80],
                '-cropped' => ['width' => 300, 'height' => 300, 'crop' => true, 'quality' => 80]
            ];

            $filename = $baseName.'.'.$extension;
            $original =  $directory.$filename;

            foreach ($versions as $suffix => $config) {
                $filename = $baseName . $suffix . '.' . $extension;
                $path = "{$directory}/{$filename}";
                
                $image = clone $originalImage;
                
                if (isset($config['crop']) && $config['crop']) {
                    $image->resize(null, $config['height'], function ($constraint) {
                        $constraint->aspectRatio();
                    })->resizeCanvas($config['width'], $config['height']);
                } else {
                    $width = $config['width'] ?? $config['size'] ?? null;
                    $image->resize($width, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                }

                Storage::put($path, $image->encode($extension, $config['quality']));
                // $original[$suffix ? substr($suffix, 1) : 'original'] = $path;
            }

            // if (env('FILESYSTEM_DRIVER') == 's3') {
            //     $original = array_map(function ($path) {
            //         return env('AWS_ENDPOINT') . '/' . env('AWS_BUCKET') . '/' . env('AWS_ROOT') . '/' . $path;
            //     }, $original);
                
            //     return $original;
            // }

            return $original;

        } catch (\Throwable $th) {
            Log::error('Error al guardar la imagen: ' . $th->getMessage(), [
                'file' => $file ? $file->getClientOriginalName() : 'null',
                'folder' => $folder,
                'trace' => $th->getTraceAsString()
            ]);
            return null;
        }
    }

    
}
