<?php

// /////////////////////////////////////////////////////////////////////////////
// PLEASE DO NOT RENAME OR REMOVE ANY OF THE CODE BELOW. 
// YOU CAN ADD YOUR CODE TO THIS FILE TO EXTEND THE FEATURES TO USE THEM IN YOUR WORK.
// /////////////////////////////////////////////////////////////////////////////


namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\PlayerSkill;

class PlayerController extends Controller
{
    public function index()
    {
        return response("Failed", 500);
    }

    public function show()
    {
        return response("Failed", 500);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string',
            'position' => 'required|string|in:defender,midfielder,forward',
            'playerSkills' => 'required|array|min:1',
            'playerSkills.*.skill' => 'required|string|in:defense,attack,speed,strength,stamina',
            'playerSkills.*.value' => 'required|integer|min:0|max:100'
        ]);

        // Create the player
        $player = Player::create([
            'name' => $request->name,
            'position' => $request->position
        ]);

        // Attach skills to the player
        foreach ($request->skills as $skillData) {
            // Ensure each skill data is an array
            if (!is_array($skillData)) {
                return response()->json(['error' => 'Each skill must be provided as an array'], 400);
            }

            $skill = new PlayerSkill([
                'skill' => $skillData['skill'],
                'value' => $skillData['value']
            ]);
            $player->skills()->save($skill);
        }

        // Fetch the player with their skills
        $playerWithSkills = Player::with('skill')->find($player->id);

        return response()->json($playerWithSkills);


        //$users = json_decode($request->all());
        // $player = Player::create([
        //     'name' => $request['name'],
        //     'position' => $request['position']
        //     ]);

        // return response()->json($player);
        // return response("", 200);
    }

    public function update()
    {
        return response("Failed", 500);
    }

    public function destroy()
    {
        return response("Failed", 500);
    }
}
