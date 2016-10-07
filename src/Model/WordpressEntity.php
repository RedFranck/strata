<?php

namespace Strata\Model;

use Strata\Strata;
use Strata\Utility\Inflector;
use Strata\Core\StrataConfigurableTrait;
use Strata\Model\Taxonomy\Taxonomy;

/**
 * A class that wraps around Wordpress' object's common concepts.
 */
class WordpressEntity extends Model
{
    use StrataConfigurableTrait;

    /**
     * Returns the internal custom post type slug
     * @return string
     */
    public static function wordpressKey()
    {
        $app = Strata::app();

        $classHash = md5(get_called_class());
        $cacheKey = "runtime.static.$classHash.wpkey";

        if (!$app->hasConfig($cacheKey)) {
            $obj = self::staticFactory();
            $app->setConfig($cacheKey, $obj->getWordpressKey());
        }

        return $app->getConfig($cacheKey);
    }

    public static function factoryFromKey($wordpressKey)
    {
        if (preg_match('/_?cpt_(\w+)/', $wordpressKey, $matches)) {
            return self::factory($matches[1]);
        } elseif (preg_match('/_?tax_(\w+)/', $wordpressKey, $matches)) {
            return Taxonomy::factory($matches[1]);
        } elseif (preg_match('/_?(post|page)/', $wordpressKey, $matches)) {
            return self::factory($matches[1]);
        }
    }

    /**
     * The permission level required for editing by the model
     * @var string
     */
    public $permissionLevel = 'edit_posts';

    /**
     * Returns the internal custom post type slug
     * @return string
     */
    public function getWordpressKey()
    {
        $name = $this->getShortName();
        $name = Inflector::underscore($name);

        return $this->wpPrefix . $name;
    }

    public function getQueryVar()
    {
        return $this->getWordpressKey();
    }
}
