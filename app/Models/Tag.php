<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * このタグに属するコンタクト（問い合わせ）を取得
     */
    public function contacts(): BelongsToMany
    {
        return $this->BelongsToMany(Contact::class);
    }
}
