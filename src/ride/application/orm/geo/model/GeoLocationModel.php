<?php

namespace ride\application\orm\geo\model;

use ride\application\orm\entry\SurveyEntry;

use ride\library\i18n\translator\Translator;
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
     * Gets the options for the type field
     * @param \ride\library\i18n\translator\Translator $translator
     * @return array
     */
    public function getTypeOptions(Translator $translator) {
        return array(
            self::TYPE_CITY => $translator->translate('label.city'),
            self::TYPE_PROVINCE => $translator->translate('label.province'),
            self::TYPE_REGION => $translator->translate('label.region'),
            self::TYPE_COUNTRY => $translator->translate('label.country'),
            self::TYPE_CONTINENT => $translator->translate('label.continent'),
        );
    }

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
