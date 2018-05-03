<?php

class Converdo_Magento_Helpers_LocationList
{
    /**
     * The available server cluster locations.
     *
     * @var array
     */
    protected static $clusters = [
        'eu-west-1',
        'eu-west-2',
    ];

    /**
     * Populate an options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $clusters = [
            '' => '',
        ];

        foreach (static::$clusters as $cluster) {
            $clusters[$cluster] = $cluster;
        }

        return $clusters;
    }
}
