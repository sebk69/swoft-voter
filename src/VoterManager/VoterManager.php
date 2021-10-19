<?php

/**
 * This file is a part of sebk/swoft-voter
 * Copyright 2021 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SwoftVoter\VoterManager;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;

/**
 * @Bean()
 */
class VoterManager implements VoterManagerInterface
{
    /**
     * List of voters path
     * @var array
     */
    // List possible attributes, public in order app to add attribues
    public static $POSSIBLE_ATTRIBUTES = [
        VoterInterface::ATTRIBUTE_READ,
        VoterInterface::ATTRIBUTE_WRITE,
        VoterInterface::ATTRIBUTE_UPDATE,
        VoterInterface::ATTRIBUTE_DELETE,
    ];

    /**
     * List of voters
     * @var VoterInterface[]
     */
    protected $voters;

    public function __construct()
    {
        // Initialize voters list
        $this->voters = [];

        // Get all voter classes
        $classes = [];
        foreach (config('voter.path', []) as $path) {
            $dir = scandir($path);
            foreach ($dir as $filename) {
                $filepath = $path . "/" . $filename;
                if (is_file($filepath) && substr($filename, strlen($filename) - 4) == '.php') {
                    $classes[] = $this->parseForClass($filepath);
                }
            }
        }

        // Add voters
        foreach ($classes as $class) {
            $voter = new $class;
            if ($voter instanceof VoterInterface) {
                $this->voters[] = $voter;
            }
        }
    }

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

        // By default, abstain
        $response = VoterInterface::ACCESS_ABSTAIN;
        // For each voters
        foreach ($this->voters as $voter) {
            if ($voter->support($subject, $attributes)) {
                // Get voter reponse
                switch ($voter->voteOnAttribute($user, $subject, $attributes)) {
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
        }

        return $response;
    }

    /**
     * Parse file to extract class with namespace
     * @param $filepath
     * @return string
     */
    private function parseForClass($filepath)
    {
        // Get contents
        $content = file_get_contents($filepath);

        // Get lines of content
        $lines = explode("\n", $content);

        // For each lines
        $namespace = '';
        $classname = '';
        foreach ($lines as $line) {
            // Extract namespace
            if (strstr($line, 'namespace')) {
                $namespace = trim(str_replace('namespace', '', str_replace(';', '', $line)));
            }

            // Extract class name
            if (!empty($namespace) && strstr($line, 'class')) {
                $withoutClass = trim(str_replace('class', '', $line));
                for ($i = 0; $i < strlen($line); $i++) {
                    if (substr($withoutClass, $i, 1) == ' ') {
                        $classname = substr($withoutClass, 0, $i);
                        break;
                    }
                }
            }
        }

        // Return concat of namespace and class name
        return '\\' . $namespace . '\\' . $classname;
    }

}
