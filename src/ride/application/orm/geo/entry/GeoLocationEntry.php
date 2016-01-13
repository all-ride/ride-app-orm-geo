<?php

namespace ride\application\orm\geo\entry;

use ride\application\orm\entry\GeoLocationEntry as OrmGeoLocationEntry;
use ride\application\orm\geo\model\GeoLocationModel;

use ride\library\geocode\coordinate\GeocodeCoordinate;

/**
 * Data container for a geo location
 */
class GeoLocationEntry extends OrmGeoLocationEntry implements GeocodeCoordinate {

    /**
     * Gets a string representation of this location
     * @return string
     */
    public function __toString() {
        return $this->getName();
    }

    /**
     * Checks whether this location is a country
     * @return boolean
     */
    public function isCountry() {
        return $this->getType() == GeoLocationModel::TYPE_COUNTY;
    }

    /**
     * Checks whether this location is a city
     * @return boolean
     */
    public function isCity() {
        return $this->getType() == GeoLocationModel::TYPE_CITY;
    }

    /**
     * Gets the country of this location
     * @return GeoLocationEntry|null
     */
    public function getCountry() {
        if ($this->getType() == GeoLocationModel::TYPE_COUNTRY) {
            return $this;
        }

        $parent = $this->getParent();
        while ($parent) {
            if ($parent->getType() == GeoLocationModel::TYPE_COUNTRY) {
                return $parent;
            }

            $parent = $parent->getParent();
        }

        return null;
    }

    /**
     * Gets the address to lookup the coordinates for
     * @return string
     */
    public function getGeocodeAddress() {
        $address = $this->getName();

        $country = $this->getCountry();
        if ($country && $country->getId() != $this->getId()) {
            $address .= ', ' . $country;
        }

        return $address;
    }

    /**
     * Generates the materialized path of this geo location
     * @return string
     */
    public function generatePath() {
        $tokens = array();

        $parent = $this;
        do {
            $tokens[] = $parent->getCode();
        } while ($parent = $parent->getParent());

        $tokens = array_reverse($tokens);

        return implode(GeoLocationModel::PATH_SEPARATOR, $tokens);
    }

}
