# sebk/swoft-voter

Voter system for Swoft

## Install

Create your Swoft project : http://swoft.io/docs/2.x/en/quick-start/install.html

Require Swoft Voter package (https://github.com/sebk69/swoft-voter) :
```
composer require sebk/swoft-voter
```

## Documentation

### Parameter

In you're 'config' folder, create a 'voter.php' file :
```
<?php

return [
    'path' => [
        __DIR__ . '/../app/Security/Voter',
        __DIR__ . '/../app/Security/ModelVoter',
    ],
];
```

You can parameter any path as you wan't.

### Create some voters

To create a voter, put a new voter class implements Sebk\SwoftVoter\VoterManager\VoterInterface in the voter folder (see Parameter section).

The interface is simple :
```
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
```

The 'support' method must return a bool value meaning that the voter is concerned or not by the vote represented by subject and attrutes.

The 'voteOnAttribute' method is the result of the vote for the user. It must return one of these interface constant :
* ACCESS_GRANTED : The voter consider that the user is allowed
* ACCCESS_DENIED : The voter consider thar the user is not allowed
* ACCCESS_ABSTAIN : The voter does not consider a response

On vote, all concerned voters (responded true on 'support' call) are queried :
* If one of the voter grant access then the vote is considered granted.
* If a voter abstain, his response is not taken account.
* If all voters repond a denied access, the vote consider that the user has denied access

Here is an example of Voter to check a controller access :
```
<?php

namespace App\Security\Voter;

use App\Http\Controller\Abstract\TokenSecuredController;
use Sebk\SwoftVoter\VoterManager\VoterInterface;

class ControllerVoter implements VoterInterface
{

    /**
     * Is voter sopported by vote ?
     * @param \stdClass $subject
     * @param array $attibutes
     * @return bool
     */
    public function support($subject, array $attibutes)
    {
        $result = false;

        // Check attributes
        foreach ($attibutes as $attibute) {
            switch ($attibute) {
                case VoterInterface::ATTRIBUTE_READ:
                case VoterInterface::ATTRIBUTE_UPDATE:
                    $result = true;
            }
        }

        // If attributes checked, check subject
        if ($result) {
            if (!$subject instanceof TokenSecuredController) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Vote
     * @param $user
     * @param $subject
     * @param array $attributes
     * @return int
     */
    public function voteOnAttribute($user, $subject, array $attributes)
    {
        if ($user->getLogin() == "KS" && in_array(VoterInterface::ATTRIBUTE_READ, $attributes)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

}
```

### Voting

Instanciate VoterManager
```
use Sebk\SwoftVoter\VoterManager\VoterManagerInterface;

$this->voterManager = bean(VoterManagerInterface::class);
```

And vote on object you wan't to vote
```
use Sebk\SwoftVoter\VoterManager\VoterInterface;

$subject = $this->objectToVote;
$attributes = [
    VoterInterface::ATTRIBUTE_READ,
    VoterInterface::ATTRIBUTE_WRITE,
];
$voteResult = $this->voterManager->vote($this->getUser(), $subject, $attributes);
if ($voteResult != VoterInterface::ACCESS_GRANTED) {
    // And deny access if not granted
    throw new AccessDeniedException("Forbidden access");
}
```
