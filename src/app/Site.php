<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use \Config;

/**
 * Class Site
 * @package App
 */
class Site extends Model
{

    /**
     * Sets the database table
     * @var string
     */
    protected $table = 'sites';

    /**
     * Sets the attributes available for mass-assignment
     * @var array
     */
    protected $fillable = ['name', 'url'];

    /**
     * Indicates if the model should be timestamped
     * @var bool
     */
    public $timestamps = false;

    /**
     * Boot function for using with model events
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // on every new site creation generate a key
        static::creating(function (Site $site)
        {
            $site->generateKey();
        });
    }

    /**
     * Get all the visitors of the site
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function visitors()
    {
        return $this->hasMany(Visitor::class);
    }


    /**
     * Get all the visits on this site through their visitors
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function visits()
    {
        return $this->hasManyThrough(Visit::class, Visitor::class);
    }


    /**
     * Generates a site's key by hashing its url providing the application key as the key
     * and truncating it to 16 chars length
     */
    protected function generateKey()
    {
        if (!$this->key) {
            $hmac = hash_hmac('md5', $this->url, Config::get('app.key'));
            $this->key = substr($hmac, 0, 16);
        }
    }

}
