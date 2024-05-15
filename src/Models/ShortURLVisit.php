<?php

declare(strict_types=1);

namespace AshAllenDesign\ShortURL\Models;

use App\Models\Senegal\SenegalModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ShortURLVisit.
 *
 * @property int id
 * @property int short_url_id
 * @property string ip_address
 * @property string operating_system
 * @property string operating_system_version
 * @property string browser
 * @property string browser_version
 * @property string device_type
 * @property Carbon visited_at
 * @property Carbon referer_url
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class ShortURLVisit extends SenegalModel
{
    const DEVICE_TYPE_MOBILE = 'mobile';

    public const DEVICE_TYPE_DESKTOP = 'desktop';

    public const DEVICE_TYPE_TABLET = 'tablet';

    public const DEVICE_TYPE_ROBOT = 'robot';

    public $incrementing = true;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'data_global_short_url_visits';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'short_url_id',
        'ip_address',
        'operating_system',
        'operating_system_version',
        'browser',
        'browser_version',
        'visited_at',
        'referer_url',
        'device_type',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'visited_at',
        'date_created',
        'date_modified',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'short_url_id' => 'integer',
        'visited_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (config('short-url.connection')) {
            $this->setConnection(config('short-url.connection'));
        }
    }

    /**
     * @return Factory<ShortURLVisit>
     */
    protected static function newFactory()
    {
        $factoryConfig = config('short-url.factories');

        $modelFactory = app($factoryConfig[__CLASS__]);

        return $modelFactory::new();
    }

    /**
     * A URL visit belongs to one specific shortened URL.
     *
     * @return BelongsTo<ShortURL, ShortURLVisit>
     */
    public function shortURL(): BelongsTo
    {
        return $this->belongsTo(ShortURL::class);
    }
}
