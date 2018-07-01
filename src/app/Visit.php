<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Visit
 * @package App
 */
class Visit extends Model
{
    /**
     * Sets the database table
     * @var string
     */
    protected $table = 'visits';

    /**
     * Sets the attributes available for mass-assignment
     * @var array
     */
    protected $fillable = [
        'visitor_id',
        'ip_address',
        'url',
        'visited_at'
    ];

    /**
     * Indicates if the model should be timestamped
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indicates which attributes should be casted as Carbon object
     * @var bool
     */
    public $dates = ['visited_at'];


    /**
     * Get the visitor who made this visit
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

}
