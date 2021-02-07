<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\AmazonService;
use App\Models\Recommendation;

class CrawlAmazon implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $keyword;

    public $tries = 10;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($keyword)
    {
        $this->keyword = $keyword;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(AmazonService $amazonService)
    {
        $recommendation = $amazonService->getRecommendation($this->keyword);
        if($recommendation && $recommendation['author']){
            Recommendation::create($recommendation);
        }else{
            Throw new Exception("HTTP request failed");
        }

    }
}
