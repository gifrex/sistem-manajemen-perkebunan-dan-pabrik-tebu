<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Arr;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use App\Models\usematerialhdr;
use App\Models\usemateriallst;
use proj4php\Proj4php;
use proj4php\Proj;
use proj4php\Point;

class MapsController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Dashboard',
        ]);
    }

    public function convertUtmToGps($easting, $northing, $zone = '48M')
    {
        $proj4 = new Proj4php();

        // UTM Zone 48S untuk Indonesia (sesuaikan zone-nya)
        $proj4->addDef('EPSG:32748', '+proj=utm +zone=48 +south +datum=WGS84 +units=m +no_defs');
        $proj4->addDef('EPSG:4326', '+proj=longlat +datum=WGS84 +no_defs');

        $utmProj = new Proj('EPSG:32748', $proj4);
        $wgsProj = new Proj('EPSG:4326', $proj4);

        $pointSrc = new Point($easting, $northing, $utmProj);
        $pointDest = $proj4->transform($utmProj, $wgsProj, $pointSrc);

        return [
            'latitude' => $pointDest->y,
            'longitude' => $pointDest->x
        ];
    }

    public function index()
    {
        $title = "Dashboard Agronomi";
        $nav   = "Agronomi";
        $company = session('companycode');

        // 1) Ambil LIST = sumber kebenaran (harus lengkap + 1 company)
        $list = DB::table('testgpslst as a')
            ->join('testgpshdr as d', function ($join) {
                $join->on('a.plot', '=', 'd.plot')
                    ->on('a.companycode', '=', 'd.companycode');
            })
            ->join('masterlist as m', function ($join) {
                $join->on('a.plot', '=', 'm.plot')
                    ->on('a.companycode', '=', 'm.companycode');
            })
            ->join('batch as c', function ($join) {
                $join->on('m.activebatchno', '=', 'c.batchno')
                    ->on('m.companycode', '=', 'c.companycode')
                    ->where('c.isactive', '=', 1);
            })
            ->where('a.companycode', $company) // ✅ kunci utama: filter company di root
            ->select(
                'a.companycode',
                'a.plot',
                'a.latitude',
                'a.longitude',
                'd.centerlatitude',
                'd.centerlongitude',
                'c.batchno',
                'c.batchdate',
                'c.batcharea',
                'c.lifecyclestatus',
                'c.kodevarietas',
                'c.isactive',
                DB::raw('DATEDIFF(CURDATE(), c.batchdate) as umur_hari')
            )
            ->get();

        // 2) Header dibuat dari LIST (jadi marker hanya untuk plot yang lolos join)
        $header = $list->map(function ($x) {
            return (object)[
                'companycode' => $x->companycode,
                'plot' => $x->plot,
                'centerlatitude' => $x->centerlatitude,
                'centerlongitude' => $x->centerlongitude,
            ];
        })->unique('plot')->values();

        // 3) Debug dd jika perlu: /dashboard/maps?debug=1
        if (request()->get('debug') == 1) {
            dd([
                'session_companycode' => $company,
                'list_companycodes'   => $list->pluck('companycode')->unique()->values(),
                'list_rows'           => $list->count(),
                'header_plots'        => $header->count(),
                'sample_plots'        => $header->take(20)->pluck('plot'),
            ]);
        }

        return view('dashboard.maps.mapsfilter')->with([
            'title'  => $title,
            'nav'    => $nav,
            'header' => $header, // ✅ marker pakai ini
            'list'   => $list,   // ✅ polygon points pakai ini
        ]);
    }


    public function indexapi(Request $request)
    {
        $usematerialhdr = new usematerialhdr;
        $title = "Dashboard Agronomi";
        $nav = "Agronomi";

        $rkhno = $request->rkhno ?? 'RKH21050234';
        $details = $usematerialhdr->selectuse(session('companycode'), $rkhno, 1)->get();
        $detailsPlots = Arr::pluck($details, 'plot');

        $list = DB::table('testgpslst as a')
            ->join('testgpshdr as d', function ($join) {
                $join->on('a.plot', '=', 'd.plot')
                    ->on('a.companycode', '=', 'd.companycode');
            })
            ->join('masterlist as m', function ($join) {
                $join->on('a.plot', '=', 'm.plot')
                    ->on('a.companycode', '=', 'm.companycode');
            })
            ->join('batch as c', function ($join) {
                $join->on('m.activebatchno', '=', 'c.batchno')
                    ->on('m.companycode', '=', 'c.companycode')
                    ->where('c.isactive', '=', 1);
            })
            ->where('a.companycode', session('companycode'))
            ->whereIn('a.plot', $detailsPlots)
            ->select(
                'a.companycode',
                'a.plot',
                'a.latitude',
                'a.longitude',
                'd.centerlatitude',
                'd.centerlongitude',
                'c.batchno',
                'c.batchdate',
                'c.batcharea',
                'c.lifecyclestatus',
                'c.kodevarietas',
                'c.isactive',
                DB::raw('DATEDIFF(CURDATE(), c.batchdate) as umur_hari')
            )
            ->get();



        $header = $list->map(function ($item) {
            return (object)[
                'companycode' => $item->companycode,
                'plot' => $item->plot,
                'centerlatitude' => $item->centerlatitude,
                'centerlongitude' => $item->centerlongitude
            ];
        })->unique('plot')->values();

        $data = [
            'title' => $title,
            'nav' => $nav,
            'header' => $header,
            'list' => $list,
            'rkhno' => $rkhno
        ];

        // Always return the HTML view (whether accessed directly or via API)
        return view('dashboard.maps.index', $data);
    }

    public function callmapsapi(Request $request)
    {
        $rkhno = $request->rkhno ?? 'RKH21050234';

        try {
            // Generate the URL with the rkhno parameter
            $mapUrl = env('MAPS_API_URL', 'http://localhost/tebu/public/dashboard/mapsapi') . '?rkhno=' . $rkhno;

            return response()->json([
                'success' => true,
                'message' => 'Map URL generated successfully',
                'url' => $mapUrl,
                'rkhno' => $rkhno
            ], 200, [], JSON_UNESCAPED_SLASHES);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating map URL',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function upload(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        try {
            $gpxFile = simplexml_load_file($request->file('gpxFile')->getRealPath());
            $namespaces = $gpxFile->getNamespaces(true);
            $gpxFile->registerXPathNamespace('ogr', $namespaces['ogr']);

            foreach ($gpxFile->rte as $rte) {
                $extensions = $rte->extensions;
                $divisi = (string)$extensions->children($namespaces['ogr'])->DIVISI;
                $plot_baru = (string)$extensions->children($namespaces['ogr'])->PLOT_BARU;

                $batchData = [];
                $latitudes = [];
                $longitudes = [];

                foreach ($rte->rtept as $rtept) {
                    $lat = (float)$rtept['lat'];
                    $lon = (float)$rtept['lon'];

                    // Convert UTM to GPS if needed
                    if ($this->isUtmCoordinate($lat, $lon)) {
                        $converted = $this->convertUtmToGps($lat, $lon);
                        $lat = $converted['latitude'];
                        $lon = $converted['longitude'];
                    }

                    $latitudes[] = $lat;
                    $longitudes[] = $lon;

                    $batchData[] = [
                        'companycode' => session('companycode'),
                        'plot' => $plot_baru,
                        'latitude' => $lat,
                        'longitude' => $lon
                    ];
                }

                // Batch insert
                DB::table('testgpslst')->insert($batchData);

                // Calculate center coordinates (udah dalam GPS format)
                $centerLat = array_sum($latitudes) / count($latitudes);
                $centerLon = array_sum($longitudes) / count($longitudes);

                // PENTING: Cek lagi kalau center coordinates juga UTM
                if ($this->isUtmCoordinate($centerLat, $centerLon)) {
                    $convertedCenter = $this->convertUtmToGps($centerLat, $centerLon);
                    $centerLat = $convertedCenter['latitude'];
                    $centerLon = $convertedCenter['longitude'];
                }

                DB::table('testgpshdr')->insert([
                    'companycode' => session('companycode'),
                    'plot' => $plot_baru,
                    'centerlatitude' => $centerLat,
                    'centerlongitude' => $centerLon
                ]);
            }

            return redirect()->back()->with('success1', 'Data Imported Successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    private function isUtmCoordinate($lat, $lon)
    {
        return abs($lat) > 90 || abs($lon) > 180;
    }
}
