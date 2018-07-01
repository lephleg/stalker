<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Visitor
 * @package App
 */
class Visitor extends Model
{
    /**
     * Sets the database table
     * @var string
     */
    protected $table = 'visitors';

    /**
     * Sets the attributes available for mass-assignment
     * @var array
     */
    protected $fillable = [
        'site_id',
        'vid',
        'agent'
    ];

    /**
     * Indicates if the model should be timestamped
     * @var bool
     */
    public $timestamps = false;


    /**
     * Get the site this guys was tracked on
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get all the visits made by this visitor
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function visits()
    {
        return $this->hasMany(Visit::class);
    }


}
