<?php

namespace Tests\Unit\Route;

use App\Models\PlayedGame;
use App\Models\User;
use App\Repositories\PlayedGameRepo;
use Tests\TestCase;
use Laravel\Passport\Passport;


class PlayedRouteTest extends TestCase
{
    protected $testData;
    protected $recordCount;
    protected $countPlayedGamesInGroup;

    protected $repo;
    protected $loggedInUser;

    public function setUp() : void
    {
        parent::setUp();
        $this->seed();

        $this->repo = new PlayedGameRepo();
        $this->testData = PlayedGame::all();
        $this->recordCount = count($this->testData);
        $this->loggedInUser = User::first();

        foreach($this->testData AS $playedGame){
            if($this->testData[0]->group_id == $playedGame->group_id){
                $this->countPlayedGamesInGroup++;
            }
        }
    }

    /**
     *  Get authenticated user
     */
    protected function authenticatedUser($role = "Admin"){
        $user = Passport::actingAs(
            $this->loggedInUser,
            ['create-servers']
        );
        $user->role = $role;
        return $user;
    }

    /**
     * Default data test
     */
    protected function dataTests($data, $testData) : void
    {
        $this->assertEquals($data['group_id'], $testData->group_id);
        $this->assertEquals($data['winner_id'], $testData->winner_id);
        $this->assertEquals($data['game_id'], $testData->game_id);
        $this->assertEquals($data['date'], $testData->date);
        $this->assertEquals($data['time_played'], $testData->time_played);
        $this->assertEquals($data['remarks'], $testData->remarks);
    }

    /**
     * reusable functions
     */
    protected function createGroup(){
        //create a new group where the logged in user is admin of
        $data = [
            'name' => "A random created Group",
            'description' => "This is a group that is created for php unit testing",
            'admin_id' => $this->loggedInUser->id
        ];

        $response = $this->postJson('/api/group/', $data);
        $this->assertEquals(200, $response->status());
        return $response->getData();
    }

    protected function createSetOfGroupUsers($groupId, $amountOfUsers = 4){
        $dataSet = array('One', 'Two', 'Three', 'Four', 'Five', 'Six');
        for($x = 0; $x < $amountOfUsers; $x++){
            $data = [
                'firstname' => "Person",
                'name' => "Number ".$dataSet[$x],
                'group_id' => $groupId,
            ];
            $this->postJson('/api/group/'.$groupId.'/user', $data);
        }
    }

    protected function createPlayedGameRequest($group){
        $data['group_id'] = $group->id;
        $data['game_id'] = $this->testData[0]->game_id;
        $data['date'] = '2021-04-26';
        $data['time_played'] = '01:01:00';
        $data['remarks'] = 'AAA';
        //$scoreData['expansion'] = null;

        $groupUserId = $group->group_users[1]->id;
        $data['player'][$groupUserId]['group_user_id'] = $groupUserId;
        $data['player'][$groupUserId]['place'] = "";
        $data['player'][$groupUserId]['score'] = 101;
        $data['player'][$groupUserId]['remarks'] = "Group user id: ".$group->group_users[1]->fullName;

        $groupUserId = $group->group_users[2]->id;
        $data['player'][$groupUserId]['group_user_id'] = $groupUserId;
        $data['player'][$groupUserId]['place'] = "";
        $data['player'][$groupUserId]['score'] = 130;
        $data['player'][$groupUserId]['remarks'] = "Group user id: ".$group->group_users[2]->fullName;

        $groupUserId = $group->group_users[3]->id;
        $data['player'][$groupUserId]['group_user_id'] = $groupUserId;
        $data['player'][$groupUserId]['place'] = "";
        $data['player'][$groupUserId]['score'] = 114;
        $data['player'][$groupUserId]['remarks'] = "Group user id: ".$group->group_users[2]->fullName;

        $groupUserId = $group->group_users[4]->id;
        $data['player'][$groupUserId]['group_user_id'] = $groupUserId;
        $data['player'][$groupUserId]['place'] = "";
        $data['player'][$groupUserId]['score'] = 99;
        $data['player'][$groupUserId]['remarks'] = "Group user id: ".$group->group_users[2]->fullName;
        return $data;
    }

    public function test_PlayedGamesController_index()
    {
        echo PHP_EOL.PHP_EOL.'[44m Played api Test:   [0m';
        echo PHP_EOL.'[46m Records:   [0m'.$this->recordCount;

        $this->be($this->authenticatedUser('Admin'));

        $response = $this->get('/api/group/'.$this->testData[0]->group_id.'/played');
        $response_data = $response->getData();


        $response->assertStatus(200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals(($this->countPlayedGamesInGroup), count($response_data->data));

        echo PHP_EOL.'[42m OK  [0m PlayedGamesController: test index';
    }

    public function test_PlayedGamesController_store()
    {
        $this->be($this->authenticatedUser('Admin'));

        $newGroup = $this->createGroup();
        $this->createSetOfGroupUsers($newGroup->id);

        $response = $this->get('/api/group/'.$newGroup->id);
        $group = $response->getData();

        $data = $this->createPlayedGameRequest($group);

        $response = $this->postJson('/api/group/'.$group->id.'/played', $data);
        $response_data = $response->getData();

        $response->assertStatus(200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals($group->group_users[2]->id, $response_data->winner_id);

        echo PHP_EOL.'[42m OK  [0m PlayedGamesController: test store';
    }

    public function test_PlayedGamesController_update()
    {
        $this->be($this->authenticatedUser('Admin'));

        $newGroup = $this->createGroup();
        $this->createSetOfGroupUsers($newGroup->id);

        $response = $this->get('/api/group/'.$newGroup->id);
        $group = $response->getData();

        $data = $this->createPlayedGameRequest($group);

        $response = $this->postJson('/api/group/'.$group->id.'/played', $data);
        $response_data = $response->getData();

        $response->assertStatus(200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals($group->group_users[2]->id, $response_data->winner_id);

        //write update test
        $nextWinner = $group->group_users[3]->id;

        $playedGame = $this->repo->getPlayedGame($response_data->id);

        //dd($playedGame->scores);

        $dataUpdate['group_id'] = $playedGame->group_id;
        $dataUpdate['game_id'] = $playedGame->game_id;
        $dataUpdate['date'] = $playedGame->date;
        $dataUpdate['time_played'] = '01:05:00';
        $dataUpdate['remarks'] = 'BBBB';
        //$dataUpdate['expansion'] = null;

        foreach($playedGame->scores AS $playerScore){
            $groupUserId = $playerScore->group_user_id;
            $dataUpdate['player'][$groupUserId]['id'] = $playerScore->id;
            $dataUpdate['player'][$groupUserId]['group_user_id'] = $groupUserId;
           // $dataUpdate['player'][$groupUserId]['place'] = $playerScore->place;
           $dataUpdate['player'][$groupUserId]['place'] = 0;
            $dataUpdate['player'][$groupUserId]['score'] = $playerScore->score;
            $dataUpdate['player'][$groupUserId]['remarks'] = $playerScore->remarks;
        }

        $dataUpdate['player'][$nextWinner]['score']  = 302;

        $response = $this->postJson('/api/group/'.$group->id.'/played/'.$playedGame->id, $dataUpdate);
        $response_data = $response->getData();

        $response->assertStatus(200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals($nextWinner, $response_data->winner_id);

        echo PHP_EOL.'[42m OK  [0m PlayedGamesController: test update';
    }

    public function test_PlayedGamesController_delete()
    {
        $this->be($this->authenticatedUser('Admin'));

        $newGroup = $this->createGroup();
        $this->createSetOfGroupUsers($newGroup->id);

        $response = $this->get('/api/group/'.$newGroup->id);
        $group = $response->getData();

        $data = $this->createPlayedGameRequest($group);

        $response = $this->postJson('/api/group/'.$group->id.'/played', $data);
        $response_data = $response->getData();

        $response->assertStatus(200);
        $this->assertEquals(200, $response->status());

        $response = $this->postJson('/api/group/'.$group->id.'/played/'.$response_data->id.'/delete');
        $response_data = $response->getData();

        $response->assertStatus(204);
        $this->assertEquals(204, $response->status());
        $this->assertEquals('The played game is removed' , $response_data);

        echo PHP_EOL.'[42m OK  [0m PlayedGamesController: test delete';
    }
}
