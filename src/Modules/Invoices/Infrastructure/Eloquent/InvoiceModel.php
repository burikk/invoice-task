<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Invoices\Domain\Enums\StatusEnum;

class InvoiceModel extends Model
{
    use HasFactory;

    protected $table = 'invoices';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'customer_name',
        'customer_email',
        'status',
    ];
    protected $casts = [
        'status' => StatusEnum::class,
    ];
    public $incrementing = false;

    public function productLines(): HasMany
    {
        return $this->hasMany(InvoiceProductLineModel::class, 'invoice_id');
    }
}