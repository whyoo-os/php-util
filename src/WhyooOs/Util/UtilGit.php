<?php

/**
 * util class for fetching information about git repo
 */

namespace WhyooOs\Util;


class UtilGit
{

    /**
     * @param $path
     * @param $cmd
     * @param null $output
     * @return string
     */
    private static function _git($path, $cmd, &$output=null)
    {
        $paramPath = $path ? " -C $path " : '';
        $fullCommand = "git $paramPath --no-pager $cmd";

        return exec($fullCommand, $output);
    }


    /**
     * legacy - where is this used?
     */
    public static function version(string $path = null)
    {
        self::_git($path, " describe --always", $version_mini_hash);
        self::_git($path, " rev-list HEAD | wc -l", $version_number);
        self::_git($path, " log -1", $line);
        $version['short'] = "v1." . trim($version_number[0]) . "." . $version_mini_hash[0]; // ?? v1 ?? remove?
        $version['full'] = "v1." . trim($version_number[0]) . ".$version_mini_hash[0] (" . str_replace('commit ', '', $line[0]) . ")";; // ?? v1 ?? remove?
        // TODO: 2 functions for short and full hash (used for cache busting)
        $version['shortHash'] = self::_git($path, " rev-parse --short HEAD");
        $version['fullHash'] = self::_git($path, " rev-parse HEAD");

        return $version;
    }

    /**
     * used in shipping for "version number"
     */
    public static function getDate(string $path = null)
    {
        return self::_git($path, " log -1 --format=%cd --date=short");
    }

    /**
     * used in shipping for "version number"
     */
    public static function getDateRelative(string $path = null)
    {
        return self::_git($path, " log -1 --format=%cd --date=relative");
    }

    /**
     * used by marketer
     * ideal for cache-buster
     * @return string
     */
    public static function getShortHash(string $path = null)
    {
        return self::_git($path, " rev-parse --short HEAD");
    }

    /**
     * 08/2018 used by marketer for maintenance script
     *
     * @param string|null $path
     * @return array
     */
    public static function getRevisionSummary(string $path = null)
    {
//    {
//        "id": "2cd36d9",
//        "author": "Marc Christenfeldt",
//        "message": "settings.timezone fix",
//        "date": "Mon Jan 2 02:49:56 2017 +0100",
//        "branch": "develop"
//    }


//git_commit_id=$( git --git-dir $GIT_DIR log --oneline --no-merges -1 | cut -d ' ' -f1 )
//git_commit_author=$( git --git-dir $GIT_DIR   show -s --format='%an' $git_commit_id )
//git_commit_date=$( git --git-dir $GIT_DIR   show -s --format='%ad' $git_commit_id )
//git_commit_message=$( git --git-dir $GIT_DIR  )
//git_branch=$( git --git-dir $GIT_DIR rev-parse --abbrev-ref HEAD )

        return [
            "dateTime" => self::getDateTime($path),
            "author" => self::getAuthor($path),
            "message" => self::getMessage($path),
            "branch" => self::getBranch($path),
            "id" => self::getShortHash($path),
        ];
    }

    /**
     * 08/2018
     *
     * @param string|null $path
     * @return string
     */
    public static function getAuthor(string $path = null)
    {
        return self::_git($path, "   show -s --format='%an' ");
    }

    /**
     * 08/2018
     *
     * @param string|null $path
     * @return string
     */
    private static function getMessage(string $path = null)
    {
        return self::_git($path, 'log --oneline --no-merges -1 | cut -d " " -f2- | sed "s/\'//g"  ');
    }

    /**
     * 08/2018
     *
     * @param string|null $path
     * @return string
     */
    private static function getBranch(string $path = null)
    {
        return self::_git($path, 'rev-parse --abbrev-ref HEAD');
    }

    /**
     * 08/2018
     *
     * @param string|null $path
     * @return string
     */
    private static function getDateTime(string $path = null)
    {
        return self::_git($path, ' show -s --format=%ad ');
    }
}

