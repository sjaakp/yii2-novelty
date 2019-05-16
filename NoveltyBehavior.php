<?php
/**
 * yii2-novelty
 * ----------
 * Property indicating novelty of Yii2 ActiveRecord
 * Version 1.0.0
 * Copyright (c) 2019
 * Sjaak Priester, Amsterdam
 * MIT License
 * https://github.com/sjaakp/yii2-novelty
 * https://sjaakpriester.nl
 */

namespace sjaakp\novelty;

use Yii;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use yii\web\Cookie;

/**
 * Class NoveltyBehavior
 * @package sjaakp\novelty
 * TimestampBehavior with one extra: owner model gets one extra virtual attribute (default name 'novelty').
 * It has one of the following values: null, 'new', or 'updated', based on the values of 'created_at' and 'updated_at',
 * and on the time of the previous visit of the site.
 * The previous visit time is stored in two cookies.
 */
class NoveltyBehavior extends TimestampBehavior
{
    /**
     * @var string name of the virtual attribute that holds the novelty
     */
    public $noveltyAttribute = 'novelty';

    /**
     * @var string name of the cookie that holds the visit time as a Unix-timestamp
     */
    public $visitCookie = 'visit';
    /**
     * @var int expiration time of visit cookie in seconds
     */
    public $visitStamina = 31536000;   // 365 * 24 * 3600, one year

    /**
     * @var string name for the caokie that caches the previous visit time
     */
    public $cacheCookie = 'visit-cache';
    /**
     * @var int expiration time of cache cookie in seconds
     */
    public $cacheStamina = 1800;   // 30 minutes

    /**
     * @var string PHP date() format of the TimestampBehavior attributes
     * if null (default) this is set to 'U' (if $this->>value is null) or 'Y-m-d H:i:s' (MySQL timestamp)
     */
    public $format;

    /**
     * @var array return values for distinctive states
     */
    public $noveltyValues = [
        'standard' => null,
        'new' => 'new',
        'updated' => 'updated'
    ];

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        if (is_null($this->format)) $this->format = is_null($this->value) ? 'U' : 'Y-m-d H:i:s';
    }

    /**
     * Magic function to handle novelty attribute.
     * @param string $name
     * @return mixed
     * @throws \yii\base\UnknownPropertyException
     */
    public function __get($name)
    {
        if ($name == $this->noveltyAttribute) {
            /* @var $owner Model; */
            $owner = $this->owner;

            $prevVisit = $this->getPrevVisit();

            if ($owner->{$this->updatedAtAttribute} >= $prevVisit) {
                if ($owner->{$this->createdAtAttribute} >= $prevVisit) {
                    return $this->noveltyValues['new'];
                }
                return $this->noveltyValues['updated'];
            }
            return $this->noveltyValues['standard'];
        }
        return parent::__get($name);
    }

    public function canGetProperty($name, $checkVars = true)
    {
        if ($name == $this->noveltyAttribute) return true;
        return parent::canGetProperty($name, $checkVars);
    }

    /**
     * @var null previous visit time
     */
    protected static $_prevVisit;

    /**
     * @return string
     */
    protected function getPrevVisit()
    {
        if (is_null(self::$_prevVisit)) {   // if null, we have to fetch previous visit from cookie
            $reqCookies = Yii::$app->request->cookies;

            $now = time();

            if ($reqCookies->has($this->cacheCookie)) {  // if previous time is cached, get it
                self::$_prevVisit = $reqCookies->getValue($this->cacheCookie);
            } else {   // there's no cache cookie, so get visit cookie
                self::$_prevVisit = $reqCookies->getValue($this->visitCookie, $now);

                $respCookies = Yii::$app->response->cookies;
                // set new visit cookie
                $respCookies->add(new Cookie([
                    'name' => $this->visitCookie,
                    'value' => $now,
                    'expire' => $now + $this->visitStamina
                ]));

                // and cache found value
                $respCookies->add(new Cookie([
                    'name' => $this->cacheCookie,
                    'value' => self::$_prevVisit,
                    'expire' => $now + $this->cacheStamina
                ]));
            }
        }
        return date($this->format, self::$_prevVisit);
    }
}
