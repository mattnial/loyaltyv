<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domains\Billing\Models\PaymentReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentReportController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validar lo que envía el cliente
        $request->validate([
            'invoice_number' => 'required|string',
            'amount' => 'required',
            'proof_image' => 'required|image|max:5120', // Máximo 5MB
        ]);

        // 2. Guardar la imagen en disco
        // Se guardará en storage/app/public/payments
        $path = $request->file('proof_image')->store('payments', 'public');

        // 3. Guardar el registro en la Base de Datos
        PaymentReport::create([
            'customer_id' => Auth::guard('customer')->id(),
            'invoice_number' => $request->invoice_number,
            'amount' => $request->amount,
            'payment_method' => 'transferencia',
            'proof_image_path' => $path,
            'status' => 'pending'
        ]);

        return back()->with('success', '¡Comprobante enviado! Validaremos tu pago en breve.');
    }
}