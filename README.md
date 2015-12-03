# Ride: ORM Geo

This module adds a geo location model to the ORM.
This model can be used to store geographic locations like continents, countries, regions, cities, ...

## Import your data

By default all continents and countries are provided as the regions of some countries.
To add the cities, download your country archive from [http://download.geonames.org/export/zip](http://download.geonames.org/export/zip).
Extract the the country text file from the archive to _data/geo/geonames_ in your application directory (or in a module).

You can now call ``ride\service\GeoImportService->import()`` to import the continents and countries.
To import the cities, call ``ride\service\GeoImportService->importCities('country-code')``. 
This can take some time depending on the size of the country.
