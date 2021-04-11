<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $url
 * @property bool $robots_allowed
 * @property string $title
 * @property string $description
 * @property string $image_url
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class Link extends Model
{

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at',
    ];

}
