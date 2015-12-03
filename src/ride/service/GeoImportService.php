<?php

namespace ride\service;

use ride\application\orm\geo\model\GeoLocationModel;

use ride\library\system\file\browser\FileBrowser;

use \Exception;

class GeoImportService {

    public function __construct(GeoLocationModel $geoLocationModel, FileBrowser $fileBrowser, $path) {
        $this->model = $geoLocationModel;
        $this->fileBrowser = $fileBrowser;
        $this->path = trim($path, '/');
    }

    /**
     * Imports the continents and the countries
     * @return null
     */
    public function import() {
        $continents = $this->importContinents();
        $this->importCountries($continents);
    }

    /**
     * Imports the cities in the format from the geonames directory
     * @param string $countryCode
     * @return null
     * @see http://download.geonames.org/export/zip
     */
    public function importCities($countryCode) {
        $country = $this->model->getBy(array('filter' => array('code' => strtoupper($countryCode), 'type' => GeoLocationModel::TYPE_COUNTRY)));
        if (!$country) {
            throw new Exception('Could not import cities: ' . $countryCode . ' not found');
        }
        $this->importRegions($country);

        $fileName = $this->path . '/geonames/' . strtolower($countryCode) . '.txt';

        $file = $this->fileBrowser->getFile($fileName);
        if (!$file) {
            throw new Exception('Could not import cities: ' . $fileName . ' not found');
        }

        $content = $file->read();

        $parents = array();
        $locales = $this->model->getOrmManager()->getLocales();

        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            $line = trim($line);
            if (!$line) {
                continue;
            }

            $data = explode("\t", $line);
/*
            0 country code      : iso country code, 2 characters
            1 postal code       : varchar(20)
            2 place name        : varchar(180)
            3 admin name1       : 1. order subdivision (state) varchar(100)
            4 admin code1       : 1. order subdivision (state) varchar(20)
            5 admin name2       : 2. order subdivision (county/province) varchar(100)
            6 admin code2       : 2. order subdivision (county/province) varchar(20)
            7 admin name3       : 3. order subdivision (community) varchar(100)
            8 admin code3       : 3. order subdivision (community) varchar(20)
            9 latitude          : estimated latitude (wgs84)
            10 longitude         : estimated longitude (wgs84)
            11 accuracy          : accuracy of lat/lng from 1=estimated to 6=centroid
*/

            $parentCode = $data[0];
            if ($data[4]) {
                $parentCode .= '-' . $data[4];
            }

            if (!isset($parents[$parentCode])) {
                $parent =$this->model->getBy(array('filter' => array('code' => $parentCode)));
                if (!$parent) {
                    throw new Exception('Could not convert geonames: parent ' . $parentCode . ' not found');
                }

                $parents[$parentCode] = $parent;
            }

            $code = $data[1];
            $name = $data[2];
            $latitude = $data[9];
            $longitude = $data[10];

            $location = $this->model->getBy(array('filter' => array('code' => $code, 'parent.code' => $parentCode)));
            if (!$location) {
                $location = $this->model->createEntry();
                $location->setType(GeoLocationModel::TYPE_CITY);
                $location->setCode($code);
                $location->setParent($parents[$parentCode]);
            }

            if ($latitude && $longitude) {
                $location->setLatitude($latitude);
                $location->setLongitude($longitude);
            }

            foreach ($locales as $locale) {
                $location->setLocale($locale);
                $location->setName($name);

                $this->model->save($location);
            }
        }
    }

    private function importRegions($country) {
        $parents = array($country->getCode() => $country);

        $json = $this->readFile($this->path . '/region-' . strtolower($country->getCode()) . '.json');
        var_export($json);
        if (isset($json['region'])) {
            $parents = $this->importLocations(GeoLocationModel::TYPE_REGION, $json['region'], $parents);
        }

        if (isset($json['province'])) {
            $parents = $this->importLocations(GeoLocationModel::TYPE_PROVINCE, $json['province'], $parents);
        }
    }

    private function importCountries(array $continents) {
        $json = $this->readFile($this->path . '/country.json');

        return $this->importLocations(GeoLocationModel::TYPE_COUNTRY, $json['country'], $continents);
    }

    private function importContinents() {
        $json = $this->readFile($this->path . '/continent.json');

        return $this->importLocations(GeoLocationModel::TYPE_CONTINENT, $json['continent']);
    }

    private function importLocations($type, array $dataContainer, array $parents = null) {
        $result = array();

        foreach ($dataContainer as $data) {
            $location = $this->model->getBy(array('filter' => array('code' => $data['code'])));
            if (!$location) {
                $location = $this->model->createEntry();
                $location->setType($type);
                $location->setCode($data['code']);

                if (isset($data['parent']) && isset($parents[$data['parent']])) {
                    $location->setParent($parents[$data['parent']]);
                }
            }

            foreach ($data['translations'] as $locale => $name) {
                $location->setName($name);
                $location->setLocale($locale);

                $this->model->save($location);
            }

            $result[$data['code']] = $location;
        }

        return $result;
    }

    private function readFile($fileName) {
        $file = $this->fileBrowser->getFile($fileName);
        if (!$file) {
            throw new Exception('Could not import locations: ' . $fileName . ' not found');
        }

        $content = $file->read();
        if (!$content) {
            throw new Exception('Could not import locations: ' . $fileName . ' is empty');
        }

        $json = json_decode($content, true);
        if (!$json) {
            throw new Exception('Could not import locations: ' . $fileName . ' is empty');
        }

        return $json;
    }

}
