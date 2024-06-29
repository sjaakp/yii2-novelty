yii2-novelty
============

## Property indicating novelty of Yii2 ActiveRecord ##

[![Latest Stable Version](https://poser.pugx.org/sjaakp/yii2-novelty/v/stable)](https://packagist.org/packages/sjaakp/yii2-novelty)
[![Total Downloads](https://poser.pugx.org/sjaakp/yii2-novelty/downloads)](https://packagist.org/packages/sjaakp/yii2-novelty)
[![License](https://poser.pugx.org/sjaakp/yii2-novelty/license)](https://packagist.org/packages/sjaakp/yii2-novelty)

**yii2-novelty** is a [behavior](https://www.yiiframework.com/doc/guide/2.0/en/concept-behaviors#behaviors "Yii2")
that adds a property with the name `'novelty'` to an [ActiveRecord](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record#active-record "Yii2")
in the [Yii2](https://www.yiiframework.com/ "Yii2") PHP framework. This property has one of
the following three values:

 - `'new'` in case the record is *created* since the user visited the site previously;
 - `'updated'` in case the record is *updated* since the user visited the site previously;
 - `null` in other cases.
 
The class **NoveltyBehavior** extends om Yii's [TimestampBehavior](https://www.yiiframework.com/doc/api/2.0/yii-behaviors-timestampbehavior "Yii2").
The value of `novelty` is based on the attribute values of TimestampBehavior, usually called
`'created_at'` and `'updated_at'`, and on the time the user visited the site previously.
The previous visit time is stored in two cookies.

## Installation ##

Install **yii2-novelty** in the usual way with [Composer](https://getcomposer.org/). 
Add the following to the require section of your `composer.json` file:

`"sjaakp/yii2-novelty": "*"` 

or run:

`composer require sjaakp/yii2-novelty` 

You can manually install **yii2-novelty** by [downloading the source in ZIP-format](https://github.com/sjaakp/yii2-novelty/archive/master.zip).

## Using NoveltyBehavior ##

Add **NoveltyBehavior** to your ActiveRecord like this:

	<?php
	
	use sjaakp\novelty\NoveltyBehavior;
	
	class MyRecord extends ActiveRecord
	{
	    public function behaviors( ) {
    	    return [
    	        [
    	            'class' => NoveltyBehavior::class,
    	            // ... options ...
    	        ],
    	        // ... more behaviors ...
    	    ];
    	}
		...
	}

**NoveltyBehavior** extends on `yii\behaviors\TimestampBehavior`, so you shouldn't use them
together.

After adding **NoveltyBehavior** the ActiveRecord has an extra property `'novelty'`, which can be read
like any other property, f.i. with:

    $novelty = $record->novelty;
    
It is a read-only property, so it cannot be written to.

## Options ##

In most cases, **NoveltyBehavior** will work out of the box. The following options are available 
for finetuning. All are optional.

 - **noveltyAttribute** `string` Name of the read-only attribute. Default: `'novelty'`.
 - **visitCookie** `string` Name of the cookie storing the previous visit time. Default: `'visit'`.
 - **visitStamina** `integer` Expiration time of the visit-cookie in seconds. Default: `31536000` (one year).
 - **cacheCookie** `string` Name of the cookie caching the previous visit time. Default: `'visit-cache'`.
 - **cacheStamina** `integer` Expiration time of the cache-cookie in seconds. Default: `1800` (30 minutes).
 - **format** `null|string` PHP `date()` format of the TimestampBehavior attributes. If `null`,
   this will be set with the right values for `value` is null, or for `value` set to
   `new Expression('NOW()')`, i.e. virtually all use cases. Only in really exotic cases might this be set
   to anything else than `null`. Default: `null`.
 - **noveltyValues** `array` Possible values assigned to `novelty`. Default: see source.
 
**NoveltyBehavior** also inherits all the options of 
[yii\behaviors\TimestampBehavior](https://www.yiiframework.com/doc/api/2.0/yii-behaviors-timestampbehavior "Yii2").
