<?php

namespace ride\application\orm\geo\model;

use ride\application\orm\entry\SurveyEntry;

use ride\library\orm\model\GenericModel;

/**
 * Model for the geo location
 */
class GeoLocationModel extends GenericModel {

    /**
     * Separator between the path tokens
     * @var string
     */
    const PATH_SEPARATOR = '~';

    /**
     * Type of a continent
     * @var string
     */
    const TYPE_CONTINENT = 'continent';

    /**
     * Type of a country
     * @var string
     */
    const TYPE_COUNTRY = 'country';

    /**
     * Type of a region
     * @var string
     */
    const TYPE_REGION = 'region';

    /**
     * Type of a province
     * @var string
     */
    const TYPE_PROVINCE = 'province';

    /**
     * Type of a city
     * @var string
     */
    const TYPE_CITY = 'city';

    /**
     * Saves the entry
     * @param mixed $entry
     * @return null
     */
    public function saveEntry($entry) {
        $entry->setPath($entry->generatePath());

        return parent::saveEntry($entry);
    }

}
