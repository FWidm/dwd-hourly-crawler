<?php
/**
 * User: Fabian Widmann
 * Date: 30.11.17
 * Time: 17:49
 */

namespace FWidm\DWDHourlyCrawler\Util;


use FWidm\DWDHourlyCrawler\Exceptions\DWDLibException;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\Resource\ResourceAbstract;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\DataArraySerializer;

/**
 * Class FractalWrapper
 * @package FWidm\DWDHourlyCrawler\Util
 * @author Fabian Widmann <fabian.widmann@gmail.com>
 *
 * Provides a wrapper for often used functions in Fractal.
 */
class FractalWrapper
{

    /** Transform the given object or array with the given transformer to a Resource.
     * @param $obj
     * @param $transformer
     * @return ResourceAbstract
     * @throws DWDLibException
     */
    public static function toResource($obj, $transformer): ResourceAbstract
    {
        if (!$obj)
            return new NullResource();
        $resource = null;
        try {
            if (is_array($obj)) {
                $resource = new Collection($obj, new $transformer(), get_class($obj[0]));
            } else {
                $resource = new Item($obj, new $transformer(), get_class($obj));
            }
        } catch (\Error $e) {
            throw new DWDLibException("Specified transformer is not a class. Got transformer with the class=" . get_class($transformer));
        }
        return $resource;
    }

    /** Uses the given serializer and transformer to transform $this into an array of the expected format.
     * @param ResourceInterface $resource
     * @param string $transformer
     * @return array
     */
    public static function toArray(ResourceAbstract $resource, $serializer = DataArraySerializer::class)
    {
        $manager = new Manager();
        $manager->setSerializer(new $serializer());
        return $manager->createData($resource)->toArray();
    }

    /** Uses the given serializer and transformer to transform $this into an array of the expected format.
     * @param ResourceInterface $resource
     * @param string $serializer
     * @return string
     */
    public static function toJson(ResourceAbstract $resource, $options = 0, $serializer = DataArraySerializer::class)
    {
        $manager = new Manager();
        $manager->setSerializer(new $serializer());
        return $manager->createData($resource)->toJson($options);
    }
}