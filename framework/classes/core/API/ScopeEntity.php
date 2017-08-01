<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */
namespace CASHMusic\Core\API;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
class ScopeEntity implements ScopeEntityInterface
{
    use EntityTrait;
    public function jsonSerialize()
    {
        return $this->getIdentifier();
    }
}