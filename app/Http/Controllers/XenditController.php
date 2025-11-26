<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;

class XenditController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/payment/invoice",
     *     summary="Buat invoice pembayaran via Xendit",
     *     tags={"Payment"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"package_id"},
     *             @OA\Property(property="package_id", type="integer", example=1),
     *             @OA\Property(property="success_redirect_url", type="string", example="https://example.com/payment/success"),
     *             @OA\Property(property="failure_redirect_url", type="string", example="https://example.com/payment/failed")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Invoice berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="invoice_url", type="string", example="https://checkout.xendit.co/..."),
     *             @OA\Property(property="order", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Validasi gagal"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */

    private $invoiceApi;

    public function __construct()
    {
        Configuration::setXenditKey(config('services.xendit.secret_key'));
        $this->invoiceApi = new InvoiceApi;
    }

    public function createInvoice(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'success_redirect_url' => 'nullable',
            'failure_redirect_url' => 'nullable',
        ]);
        $user = Auth::user();
        $package = Package::find($request->package_id);

        // create order
        $order = Order::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'external_id' => 'order-'.uniqid(),
            'amount' => $package->price,
            'status' => 'pending',
        ]);


        $invoice = $this->invoiceApi->createInvoice(new CreateInvoiceRequest([
            'external_id' => $order->external_id,
            'amount' => $order->amount,
            'payer_email' => $user->email,
            'description' => "Purchase {$package->name} Package",
            'invoice_duration' => 3600,
            'success_redirect_url' => $request->success_redirect_url ?? null,
            'failure_redirect_url' => $request->failure_redirect_url ?? null,
        ]));


        $order->update([
            'invoice_url' => $invoice['invoice_url'],
            'xendit_invoice_id' => $invoice['id'],
            'payload' => $invoice,
        ]);

        return response()->json([
            'invoice_url' => $invoice['invoice_url'],
            'order' => $order,
        ]);
    }

    public function webhook(Request $request)
    {
        $callbackToken = $request->header('x-callback-token');
        $validToken = config('services.xendit.webhook_secret');

        if ($callbackToken !== $validToken) {
            return response()->json(['message' => 'Invalid callback token'], 401);
        }

        Log::info('Xendit Webhook Received', $request->all());

        $externalId = $request->external_id;
        $status = $request->status;
        $xenditInvoiceId = $request->id;

        $order = Order::where('external_id', $externalId)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($status === 'PAID') {
            $order->status = 'paid';
            $order->paid_at = now();
            // $order->paid_amount = $request->paid_amount;
        } elseif ($status === 'FAILED') {
            $order->status = 'failed';
        }

        $order->xendit_invoice_id = $xenditInvoiceId;
        $order->payload = $request->all();
        $order->save();

        if ($status === 'PAID') {
            $user = User::find($order->user_id);

            if ($user) {
                $user->package_id = $order->package_id;
                $user->expiration_date = now()->addDays(30); // contoh: aktif 30 hari
                $user->save();
            }
        }

        return response()->json(['message' => 'Webhook processed']);
    }
}
