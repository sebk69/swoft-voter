<?php

namespace Sebk\SwoftVoter\VoterManager;

use Swoft\Bean\Annotation\Mapping\Bean;

interface VoterManagerInterface
{
    /**
     * Vote
     * @param $user
     * @param $subject
     * @param $attributes
     * @return int
     */
    public function vote($user, $subject, $attributes);

}
