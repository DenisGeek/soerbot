<?php

namespace SoerBot\Commands\Leaderboard\Implementations;

use SoerBot\Commands\Leaderboard\Traits\ArrayServiceMethods;
use SoerBot\Commands\Leaderboard\Interfaces\UserModelInterface;
use SoerBot\Commands\Leaderboard\Interfaces\LeaderBoardStoreInterface;

class UserModel implements UserModelInterface
{
    /**
     * @var User[]
     */
    protected $users;
    protected $store;
    protected $linesDelimiter;

    use ArrayServiceMethods;

    public function __construct(LeaderBoardStoreInterface $store, $linesDelimiter = PHP_EOL)
    {
        $this->linesDelimiter = $linesDelimiter;
        $this->store = $store;

        $this->store->load();

        foreach ($this->store->toArray() as $user) {
            $this->users[] = new User($user['username'], $user['rewards']);
        }
    }

    public function incrementReward($username, $rewardName)
    {
        if (!$user = $this->get($username)) {
            $this->users[] = $user = new User($username, []);
        }

        $user->incrementReward($rewardName);

        $this->store->add([$user->getName(), $user->getRewards()]);
        $this->store->save();
    }

    public function getLeaderBoardAsString()
    {
        $str = '';

        foreach ($this->users as $index => $user) {
            if (array_key_exists($index, $places = [':one: ', ':two: ', ':three: '])) {
                $user->addPrefix($places[$index]);
            }

            $str .= $user . $this->linesDelimiter;
        }

        return $str;
    }

    /**
     * @param $username
     * @return User
     */
    protected function get($username)
    {
        return $this->first($this->users, function ($user) use ($username) {
            return $user->getName() === $username;
        });
    }
}
