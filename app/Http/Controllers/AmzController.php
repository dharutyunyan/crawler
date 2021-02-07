<?php

namespace App\Http\Controllers;

use App\Services\AmazonService;
use App\Jobs\CrawlAmazon;
use App\Models\Recommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Throwable;

class AmzController extends Controller
{
    public function index()
    {
        return response()->view('welcome');
    }

    public function crawl()
    {
        $keywordsSet = [
            "Ice Cream Scoop",
            "Insulated Tumbler",
            "First Aid Kit",
            "Fire Starter",
            "Dry Erase Markers",
            "Digital Pianos",
            "Digital Cameras",
            "DJ Headphones",
            "Compression Socks",
            "Ceiling fan",
            "Camping Lantern",
            "Bluetooth Speaker",
        ];
        $batches = [];
        foreach($keywordsSet as $keyword){
            $batches[] = new CrawlAmazon($keyword);
        }

        Recommendation::truncate();
        $batch = Bus::batch($batches)->dispatch();
        return redirect()->action([AmzController::class, 'index']);
    }

    public function export()
    {
        $data = Recommendation::all();

        $filename = "KEYWORDWINNER_" . date("Y_m_d") . ".csv";

        $content = "";
        foreach($data as $row) {
            $published_at = str_replace(',', '', $row->published_at);
            $published_at = explode('-', $published_at);
            $csvRow = [
                $row->author,
                $published_at[0],
                str_replace(' ', '', strtoupper($row->article)),
                $row->article_url,
                $row->created_at
            ];
            $content .= implode(',', $csvRow) . "\n";
        }
        Storage::put($filename, $content);
        $headers = array(
            'Content-Type' => 'text/csv',
        );

        return Storage::download($filename);
    }


}
