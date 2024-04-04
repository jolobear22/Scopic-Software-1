<?php

// /////////////////////////////////////////////////////////////////////////////
// PLEASE DO NOT RENAME OR REMOVE ANY OF THE CODE BELOW. 
// YOU CAN ADD YOUR CODE TO THIS FILE TO EXTEND THE FEATURES TO USE THEM IN YOUR WORK.
// /////////////////////////////////////////////////////////////////////////////
namespace App\Enums;
namespace App\Models;

use App\Enums\PlayerPosition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer $id
 * @property string $name
 * @property PlayerPosition $position
 * @property PlayerSkill $skill
 */
class Player extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'position'
    ];




    protected $casts = [
        'position' => 'string', // Ensure the position is cast to string
    ];

    /**
     * Mutator to set the position attribute.
     *
     * @param  string  $value
     * @return void
     */
    public function setPositionAttribute($value)
    {
        // Convert the value to lowercase to ensure consistency
        $value = strtolower($value);
        
        // Ensure that the value is one of the enum values
        if (!in_array($value, [PlayerPosition::defender, PlayerPosition::midfielder, PlayerPosition::forward])) {
            throw new \InvalidArgumentException("Invalid player position: {$value}");
        }
        
        // Set the position attribute
        $this->attributes['position'] = $value;
    }

    /**
     * Accessor to get the position attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function getPositionAttribute($value)
    {
        // Convert the stored attribute value to the corresponding enum value
        return strtolower($value);
    }




    protected $with = ['skill'];

    public function skill(): HasMany
    {
        return $this->hasMany(PlayerSkill::class);
    }
}
