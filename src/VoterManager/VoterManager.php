<?php

/**
 * This file is a part of sebk/swoft-voter
 * Copyright 2021 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SwoftVoter\VoterManager;

use Swoft\Bean\Annotation\Mapping\Bean;

class VoterManager
{

    // List possible attributes, public in order app to add attribues
    public static $POSSIBLE_ATTRIBUTES = [
        VoterInterface::ATTRIBUTE_READ,
        VoterInterface::ATTRIBUTE_WRITE,
        VoterInterface::ATTRIBUTE_UPDATE,
        VoterInterface::ATTRIBUTE_DELETE,
    ];

    protected $voters;

    /**
     * Vote
     * @param $user
     * @param $subject
     * @param $attributes
     * @return int
     */
    public function vote($user, $subject, $attributes)
    {
        // Check attributes
        foreach ($attributes as $attribute) {
            if (!in_array($attribute, self::$POSSIBLE_ATTRIBUTES)) {
                return VoterInterface::ACCESS_DENIED;
            }
        }

        // Initialize voters if not
        if ($this->voters == null) {
            $this->voters = [];

            // Get all voter classes
            $classes = [];

            // Add voters
            foreach ($classes as $class) {
                $voter = new $class;
                if ($voter instanceof VoterInterface) {
                    $this->voters[] = $voter;
                }
            }
        }

        // By default, abstain
        $response = VoterInterface::ACCESS_ABSTAIN;
        // For each voters
        foreach ($this->voters as $voter) {
            // Get voter reponse
            switch($voter->voteOnAttribute($user, $subject, $attributes)) {
                case VoterInterface::ACCESS_ABSTAIN:
                    // Abstain : do nothing
                    break;
                case VoterInterface::ACCESS_GRANTED:
                    // Granted, return granted
                    return VoterInterface::ACCESS_GRANTED;
                case VoterInterface::ACCESS_DENIED:
                    // Denied, keep response but scan others voter for eventually grant access
                    $response = VoterInterface::ACCESS_DENIED;
                    break;
                default:
                    // Anormal response, deny access
                    return VoterInterface::ACCESS_DENIED;
            }
        }

        return $response;
    }

}
