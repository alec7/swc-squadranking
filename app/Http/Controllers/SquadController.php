<?php

namespace App\Http\Controllers;

use App\Battle;
use App\GameClient;
use App\Squad;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Log;

class SquadController extends Controller
{

    private $client;

    /**
     * SquadController constructor.
     * @param GameClient $client
     */
    public function __construct(GameClient $client)
    {
        $this->client = $client;
    }


    public function viewSquad(Request $request)
    {
        if ($request->has('squadId')) {
            $squadId = $request->input('squadId');
            $squad = $this->client->guildGetPublic($squadId);
            if ($squad === null) {
                return view('squad_not_found');
            }
            $warRecord = [];
            if (!empty($squad->warHistory)) {
                foreach ($squad->warHistory as $battle) {
                    $row = [];
                    $row['endDate'] = Carbon::createFromTimestampUTC($battle->endDate);
                    $row['result'] = ($battle->score > $battle->opponentScore) ? 'WIN' : (($battle->score < $battle->opponentScore) ? 'LOSS' : 'DRAW');
                    $row['score'] = $battle->score;
                    $row['opponentScore'] = $battle->opponentScore;
                    $row['opponent'] = empty($battle->opponentName) ? '<i>Unknown</i>' : urldecode($battle->opponentName);
                    $row['opponentId'] = $battle->opponentGuildId;
                    $warRecord[$battle->endDate] = $row;
                }
            }
            ksort($warRecord);
            $squadName = urldecode($squad->name);
            $squad = \GuzzleHttp\json_encode($squad, JSON_PRETTY_PRINT);
            return view('squad', compact('squadName', 'squad', 'warRecord'));
        }
        return redirect('/');
    }

    public function squadSearch(Request $request)
    {
        if ($request->has('name')) {
            $results = $this->client->guildSearchByName($request->input('name'));
            foreach ($results as $result) {
                if (Squad::firstOrNew(['id' => $result->_id])->queueIfNeeded()) {
                    Log::info('Added squad ' . $result->_id . ' to queue from search.');
                }
            }
        }
        return view('squadsearch', compact('results'));
    }

    public function squadHistory($id)
    {
        $squad = Squad::findOrFail($id);

        $offensiveBattles = Battle::whereSquadId($squad->id)->get();
        $defensiveBattles = Battle::whereOpponentId($squad->id)->get();

        $battles = [];
        foreach ($offensiveBattles as $offensiveBattle) {
            $battles[$offensiveBattle->end_date] = [
                'score' => $offensiveBattle->score,
                'opponent_score' => $offensiveBattle->opponent_score,
                'opponent' => Squad::findOrNew($offensiveBattle->opponent_id),
            ];
        }
        foreach ($defensiveBattles as $defensiveBattle) {
            $battles[$defensiveBattle->end_date] = [
                'score' => $defensiveBattle->opponent_score,
                'opponent_score' => $defensiveBattle->score,
                'opponent' => Squad::findOrNew($defensiveBattle->squad_id),
            ];
        }

        ksort($battles);

        $totals = [
            'squad' => $squad,
            'rank' => $squad->getRank(),
            'skill' => round($squad->mu * 1000),
            'uplinksCaptured' => 0,
            'uplinksSaved' => 0,
            'wins' => 0,
            'losses' => 0,
            'draws' => 0,
        ];

        foreach ($battles as $endDate => $battle) {
            $totals['uplinksCaptured'] += $battle['score'];
            $totals['uplinksSaved'] += 45 - $battle['opponent_score'];

            if ($battle['score'] > $battle['opponent_score']) {
                $battles[$endDate]['result'] = 'WIN';
                $totals['wins']++;
             }
             elseif ($battle['score'] < $battle['opponent_score']) {
                 $battles[$endDate]['result'] = 'LOSS';
                 $totals['losses']++;
             }
             else {
                 $battles[$endDate]['result'] = 'DRAW';
                 $totals['draws']++;
             }
        }

        $totals['wars'] = count($battles);

        return view('squad_history', compact(['battles', 'totals']));
    }
}
