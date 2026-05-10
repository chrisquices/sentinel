<?php

namespace Chrisquices\Sentinel\Http\Controllers;

use Chrisquices\Sentinel\Services\QueueService;
use Illuminate\Routing\Controller;

class QueueController extends Controller
{
    public function show()
    {
        return response()->json(QueueService::get());
    }

    public function showJob(string $id)
    {
        if (! preg_match('/^[a-zA-Z0-9-]+$/', $id) || strlen($id) > 255) {
            return response()->json(['error' => 'Invalid job ID.'], 400);
        }

        $payload = QueueService::getJobPayload($id);

        if ($payload === null) {
            return response()->json(['error' => 'Job not found.'], 404);
        }

        return response()->json($payload);
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
