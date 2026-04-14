<?php

namespace App\Http\Controllers\Process;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class GPXController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Process',
        ]);
    }

    public function indexUpload()
    {
        $title = 'Upload GPX File';

        return view('process.upload.index', compact('title'));
    }

    public function indexExport()
    {
        $title = 'Export KML File';

        return view('process.export.index', compact('title'));
    }

    public function upload(Request $request)
    {
        if (!$request->hasFile('gpxFile') || !$request->file('gpxFile')->isValid()) {
            return response()->json(['message' => 'Error: File upload failed.'], 400);
        }

        $file = $request->file('gpxFile');
        $allowedTypes = ['application/gpx+xml', 'text/xml'];
        $maxFileSize = 20 * 1024 * 1024;

        if (!in_array($file->getMimeType(), $allowedTypes) && $file->getClientOriginalExtension() !== 'gpx') {
            return response()->json(['message' => 'Error: Invalid file type. Only GPX files are allowed.'], 400);
        }

        if ($file->getSize() > $maxFileSize) {
            return response()->json(['message' => 'Error: File size exceeds the maximum limit of 20MB.'], 400);
        }

        $file->move(public_path('uploads'), $file->getClientOriginalName());
        $xmlFile = public_path('uploads/' . $file->getClientOriginalName());


        if (!file_exists($xmlFile)) {
            return response()->json(['message' => 'The XML file does not exist.'], 400);
        }

        $xmlContent = simplexml_load_file($xmlFile);
        if ($xmlContent === false) {
            return response()->json(['message' => 'Failed to load XML file.'], 400);
        }

        $xmlContent->registerXPathNamespace('ogr', 'http://osgeo.org/gdal');
        $xmlContent->registerXPathNamespace('gpx', 'http://www.topografix.com/GPX/1/1');

        function safeGet($value)
        {
            return isset($value) ? htmlspecialchars($value) : 'N/A';
        }

        DB::beginTransaction();
        try {
            $hdrData = [];
            $lstData = [];

            foreach ($xmlContent->trk as $track) {
                $extensions = $track->extensions;
                $fid = safeGet($extensions->children('ogr', true)->FID_1);

                $exists = DB::table('gpshdr')->where('fid', $fid)->where('companycode', session('companycode'))->exists();
                if (!$exists) {
                    $hdrData[] = [
                        'fid' => $fid,
                        'divisi' => safeGet($extensions->children('ogr', true)->KEBUN),
                        'blok' => safeGet($extensions->children('ogr', true)->BLOK),
                        'plot' => safeGet($extensions->children('ogr', true)->PLOT),
                        'luas' => safeGet($extensions->children('ogr', true)->LUAS),
                        'companycode' => session('companycode'),
                    ];

                    foreach ($track->trkseg->trkpt as $trkpt) {
                        $lstData[] = [
                            'fid' => $fid,
                            'longitude' => safeGet($trkpt['lon']),
                            'latitude' => safeGet($trkpt['lat']),
                            'companycode' => session('companycode'),
                        ];
                    }
                }
            }

            if (!empty($hdrData)) {
                DB::table('gpshdr')->insert($hdrData);
            }
            if (!empty($lstData)) {
                $chunks = array_chunk($lstData, 1000);
                foreach ($chunks as $chunk) {
                    DB::table('gpslst')->insert($chunk);
                }
            }

            DB::commit();
            return redirect()->back()->with('success1', 'Data Imported Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function export(Request $request)
    {
        $comp = session('companycode');

        $observe = $request->input('observe');
        $variable = $request->input('variable');
        $par2 = $request->input('par2');
        $par3 = $request->input('par3');
        $par4 = $request->input('par4');

        $labels = [
            'per_germinasi' => '% Germinasi',
            'per_gap' => '% GAP',
            'populasi' => 'Populasi',
            'per_gulma' => '% Penutupan Gulma',
            'ph_tanah' => 'pH Tanah',
            'per_ppt' => '% PPT',
            'per_ppt_aktif' => '% PPT Aktif',
            'per_pbt' => '% PBT',
            'per_pbt_aktif' => '% PBT Aktif',
            'int_rusak' => '% Intensitas Kerusakan',
            'dh' => 'Dead Heart',
            'dt' => 'Dead Top',
            'kbp' => 'Kutu Bulu Putih',
            'kbb' => 'Kutu Bulu Babi',
            'kp' => 'Kutu Perisai',
            'cabuk' => 'Cabuk',
            'belalang' => 'Belalang',
            'serang_grayak' => 'BTG Terserang Ulat Grayak',
            'jum_grayak' => 'Jumlah Ulat Grayak',
            'serang_smut' => 'BTG Terserang SMUT',
            'jum_larva_ppt' => 'Jumlah Larva PPT',
            'jum_larva_pbt' => 'Jumlah Larva PBT',
        ];
        $label = $labels[$variable] ?? ucfirst($variable);

        if ($request->isMethod('post')) {
            $results = DB::table('gpslst')
                ->leftJoin('gpshdr', function ($join) {
                    $join->on('gpslst.fid', '=', 'gpshdr.fid')
                        ->whereColumn('gpslst.companycode', '=', 'gpshdr.companycode');
                })
                ->select('gpslst.fid', 'gpslst.longitude', 'gpslst.latitude', 'gpshdr.plot')
                ->where('gpslst.companycode', session('companycode'))
                ->orderBy('gpslst.fid')
                ->get();

            if ($results->isEmpty()) {
                return response()->json(['message' => 'No data found in the database.'], 404);
            }

            $tables = [
                'Agronomi' => [
                    'hdr' => 'agrohdr',
                    'lst' => 'agrolst',
                    'columns' => [
                        'AVG(agrolst.per_germinasi) as per_germinasi',
                        'AVG(agrolst.per_gap) as per_gap',
                        'AVG(agrolst.populasi) as populasi',
                        'AVG(agrolst.per_gulma) as per_gulma',
                        'AVG(agrolst.ph_tanah) as ph_tanah',
                    ],
                ],
                'HPT' => [
                    'hdr' => 'hpthdr',
                    'lst' => 'hptlst',
                    'columns' => [
                        'AVG(hptlst.per_ppt) as per_ppt',
                        'AVG(hptlst.per_ppt_aktif) as per_ppt_aktif',
                        'AVG(hptlst.per_pbt) as per_pbt',
                        'AVG(hptlst.per_pbt_aktif) as per_pbt_aktif',
                        'AVG(hptlst.int_rusak) as int_rusak',
                        'AVG(hptlst.dh) as dh',
                        'AVG(hptlst.dt) as dt',
                        'AVG(hptlst.kbp) as kbp',
                        'AVG(hptlst.kbb) as kbb',
                        'AVG(hptlst.kp) as kp',
                        'AVG(hptlst.cabuk) as cabuk',
                        'AVG(hptlst.belalang) as belalang',
                        'AVG(hptlst.serang_grayak) as serang_grayak',
                        'AVG(hptlst.jum_grayak) as jum_grayak',
                        'AVG(hptlst.serang_smut) as serang_smut',
                        'AVG(hptlst.jum_larva_ppt) as jum_larva_ppt',
                        'AVG(hptlst.jum_larva_pbt) as jum_larva_pbt',
                    ],
                ],
            ];

            if (isset($tables[$observe])) {
                $hdr = $tables[$observe]['hdr'];
                $lst = $tables[$observe]['lst'];
                $columns = $tables[$observe]['columns'];

                $data = DB::table('gpshdr')
                    ->join($hdr, function ($join) use ($hdr) {
                        $join->on("$hdr.plot", '=', 'gpshdr.plot')
                            ->whereColumn("$hdr.blok", '=', 'gpshdr.blok')
                            ->whereColumn("$hdr.companycode", '=', 'gpshdr.companycode');
                    })
                    ->join($lst, function ($join) use ($lst, $hdr) {
                        $join->on("$lst.nosample", '=', "$hdr.nosample")
                            ->whereColumn("$lst.companycode", '=', "$hdr.companycode")
                            ->whereColumn("$lst.tanggalpengamatan", '=', "$hdr.tanggalpengamatan");
                    })
                    ->select(
                        'gpshdr.fid',
                        "$hdr.nosample",
                        ...array_map(fn($col) => DB::raw($col), $columns)
                    )
                    ->where('gpshdr.companycode', $comp)
                    ->where("$hdr.closingperiode", '=', 'F')
                    ->where("$lst.closingperiode", '=', 'F')
                    ->groupBy('gpshdr.fid', "$hdr.nosample")
                    ->get()
                    ->keyBy('fid');
            }

            $kmlContent = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $kmlContent .= "<kml xmlns=\"http://www.opengis.net/kml/2.2\">\n";
            $kmlContent .= "  <Document>\n";

            // Generate dynamic legend - save as PNG file
            $legendPath = $this->generateDynamicLegendPNG($label, $par2, $par3, $par4, $comp);

            $kmlContent .= "    <ScreenOverlay>\n";
            $kmlContent .= "      <name>Legenda Warna {$label}</name>\n";
            $kmlContent .= "      <Icon>\n";
            $kmlContent .= "        <href>{$legendPath}</href>\n";
            $kmlContent .= "      </Icon>\n";
            $kmlContent .= "      <overlayXY x=\"0\" y=\"1\" xunits=\"fraction\" yunits=\"fraction\"/>\n";
            $kmlContent .= "      <screenXY x=\"0.02\" y=\"0.98\" xunits=\"fraction\" yunits=\"fraction\"/>\n";
            $kmlContent .= "      <rotationXY x=\"0\" y=\"0\" xunits=\"fraction\" yunits=\"fraction\"/>\n";
            $kmlContent .= "      <size x=\"0\" y=\"0\" xunits=\"pixels\" yunits=\"pixels\"/>\n";
            $kmlContent .= "    </ScreenOverlay>\n";

            $currentFid = null;
            $coordinates = [];
            $currentCaption = '';
            $currentData = null;

            foreach ($results as $row) {
                $fid = $row->fid;
                $latitude = $row->latitude;
                $longitude = $row->longitude;
                $caption = $row->plot;

                if ($fid !== $currentFid) {
                    if ($currentFid !== null) {
                        $color = $this->colorPlacing($currentData->{$variable} ?? null, $par2, $par3, $par4);
                        $kmlContent .= $this->generatePlacemark($currentCaption, $coordinates);
                        $kmlContent .= $this->generatePolygon($currentCaption, $coordinates, $color);
                        $kmlContent .= $this->generatePoint($currentCaption, $coordinates, $currentData, $observe);
                    }

                    $currentFid = $fid;
                    $currentCaption = $caption;
                    $currentData = $data[$fid] ?? null;
                    $coordinates = [];
                }

                $coordinates[] = "$longitude,$latitude";
            }

            if ($currentFid !== null) {
                $color = $this->colorPlacing($currentData->{$variable} ?? null, $par2, $par3, $par4);
                $kmlContent .= $this->generatePlacemark($currentCaption, $coordinates);
                $kmlContent .= $this->generatePolygon($currentCaption, $coordinates, $color);
                $kmlContent .= $this->generatePoint($currentCaption, $coordinates, $currentData, $observe);
            }

            $kmlContent .= "  </Document>\n";
            $kmlContent .= "</kml>\n";

            $filePath = "{$comp}_{$observe}_{$label}.kml";
            Storage::disk('local')->put($filePath, $kmlContent);

            return response()->download(storage_path("app/private/" . $filePath));
        }

        return response()->json(['message' => 'Invalid request method.'], 405);
    }

    private function generateDynamicLegendPNG($label, $par2, $par3, $par4, $comp)
    {
        // Create image
        $width = 220;
        $height = 200;
        $image = imagecreate($width, $height);

        // Allocate colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 255, 0, 0);
        $yellow = imagecolorallocate($image, 255, 255, 0);
        $lightGreen = imagecolorallocate($image, 0, 255, 0);
        $darkGreen = imagecolorallocate($image, 0, 160, 0);

        // Background
        imagefilledrectangle($image, 0, 0, $width, $height, $white);
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $black);

        // Title - Cari font Inter atau fallback ke system font
        $fontPaths = [
            public_path('asset/fonts/Inter-Bold.ttf'),
            public_path('asset/fonts/Inter-Regular.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf', // Linux
            '/System/Library/Fonts/Helvetica.ttc', // macOS
            'C:\Windows\Fonts\arial.ttf', // Windows
        ];

        $fontPath = null;
        $fontPathBold = null;

        foreach ($fontPaths as $path) {
            if (file_exists($path)) {
                if (strpos($path, 'Bold') !== false || strpos($path, 'bold') !== false) {
                    $fontPathBold = $path;
                } else {
                    $fontPath = $path;
                }
                if ($fontPath && $fontPathBold)
                    break;
            }
        }

        // Gunakan font yang tersedia atau fallback ke built-in
        if ($fontPathBold && file_exists($fontPathBold)) {
            imagettftext($image, 12, 0, 30, 25, $black, $fontPathBold, "Legenda Warna");
        } elseif ($fontPath && file_exists($fontPath)) {
            imagettftext($image, 12, 0, 30, 25, $black, $fontPath, "Legenda Warna");
        } else {
            imagestring($image, 5, 20, 10, "Legenda Warna", $black);
        }

        if ($fontPath && file_exists($fontPath)) {
            imagettftext($image, 10, 0, 20, 45, $black, $fontPath, $label);
        } else {
            imagestring($image, 4, 20, 30, substr($label, 0, 25), $black);
        }

        // Legend items
        $y = 65;
        $colors = [
            ['color' => $red, 'label' => '< ' . $par2],
            ['color' => $yellow, 'label' => $par2 . ' - ' . number_format($par3 - 0.01, 2)],
            ['color' => $lightGreen, 'label' => $par3 . ' - ' . number_format($par4 - 0.01, 2)],
            ['color' => $darkGreen, 'label' => '>= ' . $par4]
        ];

        foreach ($colors as $item) {
            // Color box
            imagefilledrectangle($image, 15, $y, 55, $y + 25, $item['color']);
            imagerectangle($image, 15, $y, 55, $y + 25, $black);

            // Label text
            if (file_exists($fontPath)) {
                imagettftext($image, 9, 0, 65, $y + 18, $black, $fontPath, $item['label']);
            } else {
                imagestring($image, 3, 65, $y + 7, $item['label'], $black);
            }

            $y += 33;
        }

        // Save to file
        $legendFileName = "legend_{$comp}_" . time() . ".png";
        $legendPath = public_path('uploads/' . $legendFileName);
        imagepng($image, $legendPath);
        imagedestroy($image);

        // Return relative URL
        return url('uploads/' . $legendFileName);
    }

    private function colorPlacing(?float $val, $par2, $par3, $par4): string
    {
        if (is_null($val)) {
            return '7f000000';
        }
        if ($val >= $par4) {
            return '7f00a000';
        } elseif ($val >= $par3) {
            return '7f00ff00';
        } elseif ($val >= $par2) {
            return '7f00ffff';
        } else {
            return '7f0000ff';
        }
    }

    private function generatePlacemark($caption, $coordinates)
    {
        $kml = "    <Placemark>\n";
        $kml .= "      <name>" . htmlspecialchars($caption) . "</name>\n";
        $kml .= "      <Style>\n";
        $kml .= "        <LineStyle>\n";
        $kml .= "          <color>ff0000ff</color>\n";
        $kml .= "          <width>2</width>\n";
        $kml .= "        </LineStyle>\n";
        $kml .= "      </Style>\n";
        $kml .= "      <LineString>\n";
        $kml .= "        <coordinates>\n" . implode("\n", $coordinates) . "\n        </coordinates>\n";
        $kml .= "      </LineString>\n";
        $kml .= "    </Placemark>\n";

        return $kml;
    }

    private function generatePolygon($caption, $coordinates, $polyColor)
    {
        return
            "    <Placemark>
              <name>" . htmlspecialchars($caption) . "</name>
              <Style>
                <PolyStyle>
                  <color>{$polyColor}</color>
                </PolyStyle>
              </Style>
              <Polygon>
                <outerBoundaryIs>
                  <LinearRing>
                    <coordinates>\n" .
            implode("\n", $coordinates) . "
                    </coordinates>
                  </LinearRing>
                </outerBoundaryIs>
              </Polygon>
            </Placemark>\n";
    }

    private function generatePoint($caption, $coordinates, $data = null, $observe)
    {
        $centroid = $this->calculateCentroid($coordinates);

        $desc = "";
        $fields = [
            'Agronomi' => [
                'title' => 'Data Agronomi',
                'items' => [
                    'nosample' => 'Nomor Sample',
                    'per_germinasi' => ['% Germinasi', fn($v) => $v * 100 . '%'],
                    'per_gap' => ['% Gap', fn($v) => $v * 100 . '%'],
                    'populasi' => ['Populasi', fn($v) => round($v)],
                    'per_gulma' => ['% Penutupan Gulma', fn($v) => $v * 100 . '%'],
                    'ph_tanah' => ['pH Tanah', fn($v) => round($v, 1)],
                ],
            ],
            'HPT' => [
                'title' => 'Data HPT',
                'items' => [
                    'nosample' => 'Nomor Sample',
                    'per_ppt' => ['% PPT', fn($v) => $v * 100 . '%'],
                    'per_ppt_aktif' => ['% PPT Aktif', fn($v) => $v * 100 . '%'],
                    'per_pbt' => ['% PBT', fn($v) => $v * 100 . '%'],
                    'per_pbt_aktif' => ['% PBT Aktif', fn($v) => $v * 100 . '%'],
                    'int_rusak' => ['% Intensitas Kerusakan', fn($v) => $v * 100 . '%'],
                    'dh' => 'Dead Heart',
                    'dt' => 'Dead Top',
                    'kbp' => 'Kutu Bulu Putih',
                    'kbb' => 'Kutu Bulu Babi',
                    'kp' => 'Kutu Perisai',
                    'cabuk' => 'Cabuk',
                    'belalang' => 'Belalang',
                    'serang_grayak' => 'BTG Terserang Ulat Grayak',
                    'jum_grayak' => 'Jumlah Ulat Grayak',
                    'serang_smut' => 'BTG Terserang SMUT',
                    'jum_larva_ppt' => 'Jumlah Larva PPT',
                    'jum_larva_pbt' => 'Jumlah Larva PBT',
                ],
            ],
        ];

        if ($data && isset($fields[$observe])) {
            $desc .= "<b>{$fields[$observe]['title']}</b><br/>";

            foreach ($fields[$observe]['items'] as $field => $label) {
                $value = $data->$field ?? '-';

                if (is_array($label)) {
                    [$text, $formatter] = $label;
                    $value = $formatter($value);
                    $desc .= "{$text}: {$value}<br/>";
                } else {
                    $desc .= "{$label}: {$value}<br/>";
                }
            }
        }

        $kml = "    <Placemark>\n";
        $kml .= "      <name>" . htmlspecialchars($caption) . "</name>\n";

        if (!empty($desc)) {
            $kml .= "      <description><![CDATA[{$desc}]]></description>\n";
        }

        $kml .= "      <Style>\n";
        $kml .= "        <IconStyle>\n";
        $kml .= "          <scale>1.2</scale>\n";
        $kml .= "          <Icon>\n";
        $kml .= "            <href>http://maps.google.com/mapfiles/kml/paddle/red-circle.png</href>\n";
        $kml .= "          </Icon>\n";
        $kml .= "        </IconStyle>\n";
        $kml .= "      </Style>\n";

        $kml .= "      <Point>\n";
        $kml .= "        <coordinates>{$centroid['lon']},{$centroid['lat']}</coordinates>\n";
        $kml .= "      </Point>\n";
        $kml .= "    </Placemark>\n";

        return $kml;
    }

    private function calculateCentroid($coordinates)
    {
        $numCoords = count($coordinates);
        $x = 0;
        $y = 0;

        foreach ($coordinates as $coord) {
            list($lon, $lat) = explode(',', $coord);
            $x += $lon;
            $y += $lat;
        }

        return ['lon' => $x / $numCoords, 'lat' => $y / $numCoords];
    }
}