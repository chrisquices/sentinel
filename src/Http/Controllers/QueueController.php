<?php

namespace Chrisquices\VulcanSentinel\Http\Controllers;

use Chrisquices\VulcanSentinel\Services\QueueService;
use Illuminate\Routing\Controller;

class QueueController extends Controller
{
    public function show()
    {
        return response()->json(QueueService::get());
    }

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
