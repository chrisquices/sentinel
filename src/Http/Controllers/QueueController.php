<?php

namespace Chrisquices\Sentinel\Http\Controllers;

use Chrisquices\Sentinel\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class QueueController extends Controller
{
    public function show(Request $request)
    {
        $v = Validator::make($request->query(), [
            'page'   => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:pending,processing,completed,failed',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        return response()->json(QueueService::get());
    }

    public function showJob(string $id)
    {
        $v = Validator::make(['id' => $id], [
            'id' => 'required|string|regex:/^[a-zA-Z0-9-]+$/|max:255',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
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
        $v = Validator::make(['id' => $id], [
            'id' => 'required|string|regex:/^[a-zA-Z0-9-]+$/|max:255',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        return response()->json(['success' => QueueService::deleteCompletedJob($id)]);
    }

    public function clearCompletedJobs()
    {
        return response()->json(['success' => QueueService::clearCompletedJobs()]);
    }

    // Failed Jobs
    public function retryFailedJob(string $id)
    {
        $v = Validator::make(['id' => $id], [
            'id' => 'required|string|regex:/^[a-zA-Z0-9-]+$/|max:255',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        return response()->json(['success' => QueueService::retryFailedJob($id)]);
    }

    public function deleteFailedJob(string $id)
    {
        $v = Validator::make(['id' => $id], [
            'id' => 'required|string|regex:/^[a-zA-Z0-9-]+$/|max:255',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        return response()->json(['success' => QueueService::deleteFailedJob($id)]);
    }

    public function clearFailedJobs()
    {
        return response()->json(['success' => QueueService::clearFailedJobs()]);
    }
}
