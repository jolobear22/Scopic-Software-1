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


    public function processTeam(Request $request)
{
    // Validate the request parameters
    $request->validate([
        '*.position' => 'required|string|in:defender,midfielder,forward',
        '*.mainSkill' => 'required|string|in:defense,attack,speed,strength,stamina',
        '*.numberOfPlayers' => 'required|integer|min:1',
    ]);

    // Initialize an array to store the selected players
    $selectedPlayers = [];

    // Iterate over each requirement
    foreach ($request->json() as $requirement) {
        $position = $requirement['position'];
        $mainSkill = $requirement['mainSkill'];
        $numberOfPlayers = $requirement['numberOfPlayers'];

        // Query the database to retrieve players based on the position and skill
        $players = Player::where('position', $position)->get();

        // If no players found for the given position, return an error
        if ($players->isEmpty()) {
            return response()->json(['error' => "Insufficient number of players for position: $position"], 400);
        }

        // Sort players by the main skill in descending order
        $players = $players->sortByDesc("skills.$mainSkill");

        // Select the required number of players
        $selectedPlayers[$position] = $players->take($numberOfPlayers);
    }

    // Format the selected players for the response
    $formattedPlayers = [];

    foreach ($selectedPlayers as $position => $players) {
        foreach ($players as $player) {
            $formattedPlayers[] = [
                'id' => $player->id,
                'name' => $player->name,
                'position' => $player->position,
                'playerSkills' => $player->skill, // Adjust this according to your database structure
            ];
        }
    }

    // Return the selected players as the response
    return response()->json($formattedPlayers);
}

}



