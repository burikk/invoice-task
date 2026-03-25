<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceProductLineModel extends Model
{
    use HasFactory;

    protected $table = 'invoice_product_lines';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'invoice_id',
        'name',
        'price',
        'quantity',
    ];
    public $incrementing = false;

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceModel::class, 'invoice_id');
    }
}
