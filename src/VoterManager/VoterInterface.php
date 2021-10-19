<?php

/**
 * This file is a part of sebk/swoft-voter
 * Copyright 2021 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SwoftVoter\VoterManager;

interface VoterInterface
{
    // Responses
    const ACCESS_GRANTED = 1;
    const ACCESS_ABSTAIN = 0;
    const ACCESS_DENIED = -1;

    // Attributes
    const ATTRIBUTE_READ = "READ";
    const ATTRIBUTE_WRITE = "WRITE";
    const ATTRIBUTE_UPDATE = "UPDATE";
    const ATTRIBUTE_DELETE = "DELETE";

    /**
     * Is voter sopported by vote ?
     * @param $subject
     * @param $attibutes
     * @return bool
     */
    function support($subject, array $attibutes);

    /**
     * Vote
     * @param $user
     * @param $subject
     * @param array $attributes
     * @return int
     */
    function voteOnAttribute($user, $subject, array $attributes);
}
