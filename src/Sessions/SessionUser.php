<?php
/** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\HTTP\Sessions;

use MVQN\Dynamics\AutoObject;

/**
 * Class SessionUser
 *
 * @package UCRM\Sessions
 * @final
 *
 * @method int      getUserId()
 * @method string   getUsername()
 * @method bool     getIsClient()
 * @method int|null getClientId()
 * @method string   getUserGroup()
 * @method array    getSpecialPermissions()
 * @method array    getPermissions()
 */
final class SessionUser extends AutoObject
{
    /**
     * @var int
     */
    protected $userId;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var bool
     */
    protected $isClient;

    /**
     * @var int|null
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $userGroup;

    /**
     * @var array
     */
    protected $specialPermissions;

    /***
     * @param string $permission
     * @return string|null
     */
    public function getSpecialPermission(string $permission): ?string
    {
        return $this->specialPermissions !== null && array_key_exists($permission, $this->specialPermissions) ?
            $this->specialPermissions[$permission] : null;
    }

    /**
     * @var array
     */
    protected $permissions;

    /***
     * @param string $permission
     * @return string|null
     */
    public function getPermission(string $permission): ?string
    {
        return $this->permissions !== null && array_key_exists($permission, $this->permissions) ?
            $this->permissions[$permission] : null;
    }

}
