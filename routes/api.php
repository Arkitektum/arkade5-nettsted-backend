<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


use App\Organization;
use App\ArkadeDownloader;
use App\ArkadeDownload;
use App\ArkadeRelease;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('arkade-downloads', function (Request $request) {

    $arkadeUI = $request->input('arkadeUI');
    $latestReleaseForUI = ArkadeRelease::whereUserInterface($arkadeUI)->orderByDesc('released_at')->first();
    $release = ArkadeRelease::find($latestReleaseForUI->id);

    $filename = $release->package_filename;
    $headers = ['Filename' => $filename, 'Access-Control-Expose-Headers' => 'Filename'];

    if(!$arkadePackageFile = Storage::download($filename, $filename, $headers))
        abort(500, 'Download failed');

    $arkadeDownload = new ArkadeDownload();
    $arkadeDownload->arkadeRelease()->associate($release);
    $arkadeDownloader = ArkadeDownloader::updateOrCreate(
        ['email' => $request->input('downloaderEmail')],
        [
            'has_arkade_v1_experience' => $request->input('downloaderA1Xp'),
            'wants_news' => $request->input('downloaderNews'),
        ]);
    $arkadeDownload->arkadeDownloader()->associate($arkadeDownloader);

    if ($orgNumber = $request->input('orgNumber')) {
        $organization = Organization::updateOrCreate(
            ['org_number' => $orgNumber],
            [
                'name' => $request->input('orgName'),
                'org_form' => $request->input('orgForm'),
                'address' => $request->input('orgAddress'),
                'latitude' => $request->input('orgLatitude'),
                'longitude' => $request->input('orgLongitude')
            ]
        );
        $arkadeDownload->organization()->associate($organization);
    }
    $arkadeDownload->save();

    return $arkadePackageFile;

})->name('download.store');
