<?php
/**
 * User: Fabian Widmann
 * Date: 30.11.17
 * Time: 17:49
 */

namespace FWidm\DWDHourlyCrawler\Traits;


use FWidm\DWDHourlyCrawler\Exceptions\DWDLibException;
use FWidm\DWDHourlyCrawler\Transformer\ParameterTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceAbstract;
use League\Fractal\Serializer\ArraySerializer;

trait TransformableTrait
{

    public function toResource($customTransformer): ResourceAbstract
    {
        try {
            $resource = new Item($this, new $customTransformer());
            return $resource;

        } catch (\Error $e) {
            throw new DWDLibException("Specified transformer is not a class. Got transformer with the class=" . get_class($customTransformer));
        }
    }

    /** Uses the given serializer and transformer to transform $this into an array of the expected format.
     * @param ResourceAbstract $resource
     * @param string $transformer
     * @return array
     */
    function toArray(ResourceAbstract $resource, $serializer = ArraySerializer::class)
    {
        $manager = new Manager();
        $manager->setSerializer(new $serializer());
        return $manager->createData($resource)->toArray();
    }

    /** Uses the given serializer and transformer to transform $this into an array of the expected format.
     * @param ResourceAbstract $resource
     * @param string $serializer
     * @param string $transformer
     * @return string
     */
    function toJson(ResourceAbstract $resource, $options = 0, $serializer = ArraySerializer::class, $transformer = ParameterTransformer::class)
    {
        $manager = new Manager();
        $manager->setSerializer(new $serializer());
        return $manager->createData($resource)->toJson($options);
    }
}