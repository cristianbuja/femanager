<?php
declare(strict_types=1);
namespace In2code\Femanager\Utility;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Model\UserGroup;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\RequestBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class FrontendUtility
 */
class FrontendUtility extends AbstractUtility
{

    /**
     * @var array
     */
    protected static $pluginNames = [
        'tx_femanager_pi1',
        'tx_femanager_pi2'
    ];

    /**
     * Get current pid
     *
     * @return int
     */
    public static function getCurrentPid(): int
    {
        return (int)self::getTypoScriptFrontendController()->id;
    }

    /**
     * Get frontend language uid
     *
     * @return int
     */
    public static function getFrontendLanguageUid(): int
    {
        $languageUid = 0;
        if (!empty(self::getTypoScriptFrontendController()->tmpl->setup['config.']['sys_language_uid'])) {
            $languageUid = (int)self::getTypoScriptFrontendController()->tmpl->setup['config.']['sys_language_uid'];
        }
        return $languageUid;
    }

    /**
     * @return string
     */
    public static function getCharset(): string
    {
        return self::getTypoScriptFrontendController()->metaCharset;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public static function getUriToCurrentPage(): string
    {
        $contentObject = ObjectUtility::getContentObject();
        $configuration = [
            'parameter' => self::getCurrentPid()
        ];
        return $contentObject->typoLink_URL($configuration);
    }

    /**
     * Set object properties from forceValues in TypoScript
     *
     * @param User $user
     * @param array $settings
     * @return User $object
     * @codeCoverageIgnore
     */
    public static function forceValues(User $user, array $settings)
    {
        foreach ((array)$settings as $field => $config) {
            $config = null;
            if (stristr($field, '.')) {
                continue;
            }
            // value to set
            $value = self::getContentObject()->cObjGetSingle($settings[$field], $settings[$field . '.']);
            if ($field === 'usergroup') {
                // need objectstorage for usergroup field
                $user->removeAllUsergroups();
                $values = GeneralUtility::trimExplode(',', $value, true);
                $userGroupRepository = self::getUserGroupRepository();

                foreach ($values as $usergroupUid) {
                    /** @var UserGroup $usergroup */
                    $usergroup = $userGroupRepository->findByUid($usergroupUid);
                    $user->addUsergroup($usergroup);
                }
            } else {
                // set value
                if (method_exists($user, 'set' . ucfirst($field))) {
                    $user->{'set' . ucfirst($field)}($value);
                }
            }
        }
        return $user;
    }

    /**
     * @return string
     */
    public static function getControllerName(): string
    {
        return GeneralUtility::makeInstance(ObjectManager::class)
            ->get(RequestBuilder::class)->build()
            ->getControllerName();
    }

    /**
     * @return string
     */
    public static function getActionName(): string
    {
        return GeneralUtility::makeInstance(ObjectManager::class)
            ->get(RequestBuilder::class)->build()
            ->getControllerActionName();
    }
}
