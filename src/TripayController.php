<?php

namespace Hanoivip\PaymentMethodTripay;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Hanoivip\Events\Payment\TransactionUpdated;

class TripayController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private function getSetting()
    {
        if (App::environment() == 'production')
        {
            return config('payment.methods.tripay');
        }
        else 
        {
            return config('payment.methods.tripay_sandbox');
        }
    }
    
    public function callback(Request $request)
    {
        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $json = $request->getContent();
        $privateKey = $this->getSetting()['setting']['private_key'];
        $signature = hash_hmac('sha256', $json, $privateKey);
        
        if ($signature !== (string) $callbackSignature) {
            return Response::json([
                'success' => false,
                'message' => 'Invalid signature',
            ]);
        }
        
        if ('payment_status' !== (string) $request->server('HTTP_X_CALLBACK_EVENT')) {
            return Response::json([
                'success' => false,
                'message' => 'Unrecognized callback event, no action was taken',
            ]);
        }
        
        $data = json_decode($json);
        
        if (JSON_ERROR_NONE !== json_last_error()) {
            return Response::json([
                'success' => false,
                'message' => 'Invalid data sent by tripay',
            ]);
        }
        
        $uniqueRef = $data->merchant_ref;
        $status = strtoupper((string) $data->status);
        
        if ($data->is_closed_payment === 1) {
            event(new TransactionUpdated($uniqueRef));
            return Response::json(['success' => true]);
        }
    }
}