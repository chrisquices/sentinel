<?php

namespace Chrisquices\Sentinel\Http\Controllers;

use Chrisquices\Sentinel\Services\QueueService;
use Illuminate\Routing\Controller;

class QueueController extends Controller
{
    public function show()
    {
        $queueData = QueueService::get();

        return response()->json($queueData);
    }

    // Completed Jobs
    public function deleteCompletedJob(string $id)
    {
        $success = QueueService::deleteCompletedJob($id);

        return response()->json(['success' => $success]);
    }

    public function clearCompletedJobs()
    {
        $success = QueueService::clearCompletedJobs();

        return response()->json(['success' => $success]);
    }

    // Failed Jobs
    public function retryFailedJob(string $id)
    {
        $success = QueueService::retryFailedJob($id);

        return response()->json(['success' => $success]);
    }

    public function deleteFailedJob(string $id)
    {
        $success = QueueService::deleteFailedJob($id);

        return response()->json(['success' => $success]);
    }

    public function clearFailedJobs()
    {
        $success = QueueService::clearFailedJobs();

        return response()->json(['success' => $success]);
    }
}
