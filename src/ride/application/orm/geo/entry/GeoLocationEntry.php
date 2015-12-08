<?php

namespace ride\application\orm\geo\entry;

use ride\application\orm\entry\GeoLocationEntry as OrmGeoLocationEntry;
use ride\application\orm\geo\model\GeoLocationModel;

use ride\library\geocode\coordinate\GeocodeCoordinate;

class GeoLocationEntry extends OrmGeoLocationEntry implements GeocodeCoordinate {

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
