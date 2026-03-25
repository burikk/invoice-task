<?php

namespace Modules\Invoices\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'invoice_product_lines' => 'present|array',
            'invoice_product_lines.*.name' => 'required|string',
            'invoice_product_lines.*.quantity' => 'required|integer|gt:0',
            'invoice_product_lines.*.price' => 'required|integer|gt:0',
        ];
    }
}
