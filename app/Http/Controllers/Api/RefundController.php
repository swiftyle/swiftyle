<?php

namespace App\Http\Controllers\Api;

use App\Events\RefundProcessed;
use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RefundController extends Controller
{
    public function create(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'refund_request_id' => 'required|exists:refund_requests,id',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        // Create the refund
        $refund = Refund::create([
            'refund_request_id' => $request->input('refund_request_id'),
            'amount' => $request->input('amount'),
            'status' => 'refunded', // Default status for new refunds
        ]);

        return response()->json([
            'message' => 'Refund created successfully',
            'data' => $refund
        ], 201);
    }

    public function readAll()
    {
        // Fetch all refunds
        $refunds = Refund::all();

        return response()->json([
            'message' => 'Refunds fetched successfully',
            'data' => $refunds
        ], 200);
    }

    public function read($id)
    {
        // Fetch refund by ID
        $refund = Refund::find($id);

        if (!$refund) {
            return response()->json(['message' => 'Refund not found'])->setStatusCode(404);
        }

        return response()->json([
            'message' => 'Refund fetched successfully',
            'data' => $refund
        ], 200);
    }

    public function update(Request $request, $id)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'refund_request_id' => 'exists:refund_requests,id',
            'amount' => 'numeric|min:0',
            'status' => 'in:refunded',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        // Find the refund
        $refund = Refund::find($id);

        if (!$refund) {
            return response()->json(['message' => 'Refund not found'])->setStatusCode(404);
        }

        // Update the refund
        $refund->refund_request_id = $request->input('refund_request_id', $refund->refund_request_id);
        $refund->amount = $request->input('amount', $refund->amount);
        $refund->status = $request->input('status', $refund->status);
        $refund->save();

        return response()->json([
            'message' => 'Refund updated successfully',
            'data' => $refund
        ], 200);
    }

    public function delete($id)
    {
        // Find the refund
        $refund = Refund::find($id);

        if (!$refund) {
            return response()->json(['message' => 'Refund not found'])->setStatusCode(404);
        }

        // Delete the refund
        $refund->delete();

        return response()->json(['message' => 'Refund deleted successfully'], 200);
    }

    public function process(Request $request, $refundRequestId)
    {
        // Proses logika bisnis untuk menandai bahwa refund telah diproses

        $refundRequest = RefundRequest::findOrFail($refundRequestId);

        // Set status refund menjadi 'processed'
        $refundRequest->status = 'processed';
        $refundRequest->save();

        // Panggil event RefundProcessed untuk mengirim notifikasi dan lainnya
        event(new RefundProcessed($refundRequest));

        // Response
        return response()->json(['message' => 'Refund processed successfully']);
    }
}