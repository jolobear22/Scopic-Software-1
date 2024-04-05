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
        // Fetch the list of players
        $players = Player::all();

        // Return the list of players as JSON response
        return response()->json($players);

        //return response("Failed", 500);
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
                'playerSkills.*.value' => 'required|integer|min:0|max:100',
            ]);

            // Create the player
            $player = Player::create([
                'name' => $request->name,
                'position' => $request->position,
            ]);

            // Ensure $request->skills is not null and is an array
            if (!is_array($request->playerSkills)) {
                return response()->json(['error' => 'Skills must be provided as an array'], 400);
            }


            // Attach skills to the player
            foreach ($request->playerSkills as $skillData) { 

                $skill = PlayerSkill::updateOrCreate([
                    'player_id' => $player->id,
                    'skill' => $skillData['skill'],
                ], [
                    'value' => $skillData['value'],

                ]);
                
            }

            // Fetch the player with their skills
            $playerWithSkills = Player::with('skill')->find($player->id);

                // Transform the data structure
            $responseData = [
                'id' => $playerWithSkills->id,
                'name' => $playerWithSkills->name,
                'position' => $playerWithSkills->position,
                'playerSkills' => $playerWithSkills->skill->map(function ($skill) {
                    return [
                        'id' => $skill->id,
                        'skill' => $skill->skill,
                        'value' => $skill->value,
                        'playerId' => $skill->player_id,
                    ];
                }),
            ];

            // Return the response
            return response()->json($responseData, 201);


    }

    public function update(Request $request, $playerId)
    {
            // Validate the request data
            $request->validate([
                'name' => 'required|string',
                'position' => 'required|string|in:defender,midfielder,forward',
                'playerSkills' => 'required|array|min:1',
                'playerSkills.*.skill' => 'required|string|in:defense,attack,speed,strength,stamina',
                'playerSkills.*.value' => 'required|integer|min:0|max:100',
            ]);

            // Find the player by ID
            $player = Player::find($playerId);

            // If player not found, return error response
            if (!$player) {
                return response()->json(['error' => 'Player not found'], 404);
            }

            // Update the player's information
            $player->update([
                'name' => $request->name,
                'position' => $request->position,
            ]);

           // Ensure $request->skills is not null and is an array
           if (!is_array($request->playerSkills)) {
            return response()->json(['error' => 'Skills must be provided as an array'], 400);
        }


        // Attach skills to the player
        foreach ($request->playerSkills as $skillData) { 

            $skill = PlayerSkill::updateOrCreate([
                'player_id' => $player->id,
                'skill' => $skillData['skill'],
            ], [
                'value' => $skillData['value'],

            ]);
            
        }

        // Fetch the player with their skills
        $playerWithSkills = Player::with('skill')->find($player->id);

            // Transform the data structure
        $responseData = [
            'id' => $playerWithSkills->id,
            'name' => $playerWithSkills->name,
            'position' => $playerWithSkills->position,
            'playerSkills' => $playerWithSkills->skill->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'skill' => $skill->skill,
                    'value' => $skill->value,
                    'playerId' => $skill->player_id,
                ];
            }),
        ];

        // Return the response
        return response()->json($responseData, 201);


        // return response("Failed", 500);
    }

    public function destroy(Request $request, $playerId)
    {   
        $requestToken = $request->header('Authorization');
        $validTokens = config('app.api_tokens.player');

        // Check if $validToken matches the token provided in the request
        if ($requestToken !== 'Bearer ' . $validTokens) {
            // Invalid token
            return response()->json(['error' => 'Unauthorized'], 401);
        }

            // Find the player by ID
        $player = Player::find($playerId);

        // If player not found, return error response
        if (!$player) {
            return response()->json(['error' => 'Player not found'], 404);
        }


        // Fetch the player with their skills
        $playerWithSkills = Player::with('skill')->find($player->id);

        // Delete the player
        $playerWithSkills->delete();

        // Return success response
        return response()->json(['message' => 'Player and associated skills deleted successfully'], 200);
        // return response("Failed", 500);
    }
}



