<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Models\ArkadeDownload;
use App\Http\Resources\ArkadeDownload as ArkadeDownloadResource;
use App\Http\Resources\ArkadeDownloadCollection;

use App\Models\ArkadeRelease;
use App\Http\Resources\ArkadeRelease as ArkadeReleaseResource;
use App\Http\Resources\ArkadeReleaseCollection;

use App\Models\Organization;
use App\Http\Resources\OrganizationCollection;
use App\Http\Resources\Organization as OrganizationResource;

use App\Models\ArkadeDownloader;
use App\Http\Resources\ArkadeDownloader as DownloaderResource;
use App\Http\Resources\ArkadeDownloaderCollection;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::domain('backend.' . env('APP_URL'))->group(function () {
    
    Route::get('/', function () {
        return view('dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    Route::middleware('auth')->prefix('statistikk')->name('statistics.')->group(function () {

        Route::get('/', function () {
            return view('statistics.index',
                ['links' => [
                    'arkadenedlastinger' => route('statistics.downloads'),
                    'arkadenedlastere' => route('statistics.downloaders'),
                    'arkadenedlaster-organisasjoner' => route('statistics.organizations'),
                    'arkadeutgivelser' => route('statistics.releases'),
                    //'self' => route('statistics.index'),
                ]]);
        })->name('index');

        Route::get('arkade-nedlastinger', function (Request $request) {
            $downloads = ($releaseId = $request->input('utgivelse'))
                ? ArkadeDownload::orderByDesc('downloaded_at')->whereArkadeReleaseId($releaseId)->paginate()
                : ArkadeDownload::orderByDesc('downloaded_at')->paginate();
            return view('statistics.arkade-downloads.index', [
                'downloads' => new ArkadeDownloadCollection($downloads),
                'totalCount' => ArkadeDownload::count()
            ]);
        })->name('downloads');

        Route::get('arkade-nedlastinger/{download}', function (ArkadeDownload $download) {
            return new ArkadeDownloadResource(ArkadeDownload::find($download->id));
        })->name('download');

        Route::get('arkade-utgivelser', function () {
            return view('statistics.arkade-releases.index', [
                'releases' => new ArkadeReleaseCollection(ArkadeRelease::withoutGlobalScope('public')->orderByDesc('released_at')->paginate()),
                'totalCount' => ArkadeRelease::withoutGlobalScope('public')->count()
            ]);
        })->name('releases');

        Route::get('arkade-utgivelser/{release}', function (ArkadeRelease $release) {
            return new ArkadeReleaseResource(ArkadeRelease::find($release->id)); // TODO: Disable global scope 'public'
        })->name('release');

        Route::get('arkade-nedlastere', function () {
            return view('statistics.arkade-downloaders.index', [
                'downloaders' => new ArkadeDownloaderCollection(ArkadeDownloader::orderByDesc('created_at')->paginate()),
                'totalCount' => ArkadeDownloader::count()
            ]);
        })->name('downloaders');

        Route::get('arkade-nedlastere/{downloader}', function (ArkadeDownloader $downloader) {
            return new DownloaderResource(ArkadeDownloader::find($downloader->id));
        })->name('downloader');

        Route::get('organisasjoner', function () {
            return view('statistics.arkade-organizations.index', [
                'organizations' => new OrganizationCollection(Organization::orderByDesc('created_at')->paginate()),
                'totalCount' => Organization::count()
            ]);
        })->name('organizations');

        Route::get('organisasjoner/{organization}', function (Organization $organization) {
            return new OrganizationResource(Organization::find($organization->id));
        })->name('organization');
    });

    Route::middleware('auth')->prefix('builds')->name('builds.')->group(function () {

        Route::get('/', function () {
            return view('builds.index', ['buildTypes' => array_map('basename', Storage::directories('builds'))]);
        })->name('index');

        Route::get('/{buildType}', function ($buildType) {
            return view('builds.build-list', [
                'buildType' => $buildType,
                'builds' => Storage::files('builds/' . $buildType)
            ]);
        })->name('buildList');

        Route::get('/{buildType}/{build}', function ($buildType, $build) {
            return Storage::download('builds/' . $buildType . '/' . $build);
        })->name('buildDownload');
    });

    Route::middleware('auth')->get('news-receivers', function () {

        $newsReceiverEmails = ArkadeDownloader::whereWantsNews(true)->pluck('email');
        $latestArkadeVersionNumber = ArkadeRelease::orderBy('released_at', 'desc')->get()
            ->unique('version_number')->pluck('version_number')->first();

        return view('news-receivers.index', [
            'newsReceiverEmails' => $newsReceiverEmails->toArray(),
            'numberOfNewsReceivers' => $newsReceiverEmails->count(),
            'latestArkadeVersionNumber' => $latestArkadeVersionNumber
        ]);
    })->name('newsReceivers');

    require __DIR__.'/auth.php';
});

Route::get('/', function () {
   return response()->file('frontend/index.html');
});
