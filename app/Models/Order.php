<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Product
 *
 * @property int $id
 * @property string $product_name
 * @property string $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 *
 * @property int $customer_id
 * @property bool $is_paid
 * @property-read \App\Models\Customer $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property float $paymentTotal
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereIsPaid($value)
 *
 * @mixin \Eloquent
 */
class Order extends Model
{
    use HasFactory;

    protected $casts = [
        'is_paid' => 'boolean',
    ];

    protected $fillable = [
        'customer_id',
        'is_paid',
    ];

    public $timestamps = true;

    /**
     * @return BelongsTo<Customer, Order>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsToMany<Product>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function paymentTotal(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->products->sum('price'),
        );
    }
}
