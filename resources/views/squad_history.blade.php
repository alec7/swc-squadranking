@extends('web')

@section('title'){!! $squad->renderNamePlain() !!}@endsection
@section('heading'){!! $squad->renderName() !!}@endsection


@section('content')
    <div class="row">
        <div class="col-lg-2">
            <table class="table table-bordered table-condensed table-hover bg-{{$squad->faction}}">
                <caption>Totals</caption>
                <tr>
                    <td>Faction</td>
                    <td class="rank text-{{$squad->faction}}">{{ucfirst($squad->faction)}}</td>
                </tr>
                <tr>
                    <td>Rank</td>
                    <td class="rank">{{$squad->rank}}</td>
                </tr>
                <tr>
                    <td>Wars</td>
                    <td class="rank">{{$squad->wars}}</td>
                </tr>
                <tr>
                    <td>Wins</td>
                    <td class="rank">{{$squad->wins}}</td>
                </tr>
                <tr>
                    <td>Draws</td>
                    <td class="rank">{{$squad->draws}}</td>
                </tr>
                <tr>
                    <td>Losses</td>
                    <td class="rank">{{$squad->losses}}</td>
                </tr>
                <tr>
                    <td>Uplinks captured</td>
                    <td class="rank">{{$squad->uplinks_captured}}</td>
                </tr>
                <tr>
                    <td>Uplinks saved</td>
                    <td class="rank">{{$squad->uplinks_saved}}</td>
                </tr>
                <tr>
                    <td>TrueSkill™</td>
                    <td class="rank">{{$squad->skill}}</td>
                </tr>
            </table>

        </div>
        <div class="col-lg-8">
            <table class="table table-striped table-bordered table-hover">
                <caption class="strong">Battle history</caption>
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Opponent</th>
                    <th>Result</th>
                    <th class="rank">Score</th>
                    <th class="rank">Opponent score</th>
                </tr>
                </thead>
                <tbody>
                @foreach($battles as $date => $battle)
                    <tr>
                        <td>{{$date}}</td>
                        <td><a href="{{$battle['opponent']->id}}">{!! $battle['opponent']->renderName() !!}</a>
                        </td>
                        <td>{{\App\Battle::result($battle['score'], $battle['opponent_score'])}}</td>
                        <td class="rank">{{$battle['score']}}</td>
                        <td class="rank">{{$battle['opponent_score']}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <small>*Note: <em>Wars from before June 22th 2016 are not included.</em></small>
        </div>
    </div>
@endsection