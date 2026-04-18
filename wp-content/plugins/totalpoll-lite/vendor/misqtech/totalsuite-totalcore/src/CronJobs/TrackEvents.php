<?php
/** @noinspection PhpUnused */

namespace TotalPollVendors\TotalCore\CronJobs;


use TotalPollVendors\TotalCore\Application;
use TotalPollVendors\TotalCore\Http\TrackingRequest;
use TotalPollVendors\TotalCore\Scheduler;
use TotalPollVendors\TotalCore\Scheduler\CronJob;

class TrackEvents extends CronJob
{

    public function execute()
    {
        $url = Application::getInstance()->env('api.tracking.events');
        $key = Application::getInstance()->env('tracking-key');

        $options = get_option($key);

        TrackingRequest::send($url, $options);

        update_option($key, [
            'screens' => [],
            'features' => []
        ]);

    }

    public function getRecurrence()
    {
        return Scheduler::SCHEDUL_DAILY;
    }

    public function getStartTime()
    {
        return time();
    }
}