composer-bower
==============

Proof of concept for a repository to install bower packages using composer


The idea
--------

Create a composer repository like packagist which provides package information of bower packages in composer format.

This repository will be filled with information from the [bower registry][].

What has to be done for this to work is, to write a script that converts bower.json syntax and semantics into a composer.json equivalent
so that composer can read it and install the package accordingly. The package that is avilable via bower does not have to be adjusted for
this.

The same thing can be implemented for other package managers like `npm` as well.

[bower registry]: http://bower.io/search/


Using the repository in you application
---------------------------------------

> **Warning: This is an experiment! Current code is not fully working and URLs will change. Do not use it yet, this is just to describe how
> it will work when finally implemented.**

Add the repository to your composer.json:

```json
"repositories": [
        {
            "type": "composer",
            "url": "https://raw.githubusercontent.com/cebe/composer-bower/master"
        }
]
```

The ugly URL will later be replaced with a hostname like for example `bower.packagist.org`.

You can then require:

```php
"require": {
    "bower/angular": "*",
    "bower/jquery": "1.9.*",
    "bower/bootstrap": "~3.2.0",
    ...
},
```


Usage for the conversion script
-----

```
./cli convert > packages.json
```

or

```
./cli convert --verbose > packages.json
```

Note: bower has about 15000 packages registered. This command will make `15000 * (1 + v)` request against github. (v = number of versions of a package). So it currently has a limit param on the versions and packages to fetch.

