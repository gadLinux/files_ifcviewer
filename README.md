# Nextcloud app files_ifcviewer

This is a viewer based on the fantastic BIMData viewer. We adapt it to fit to 
Nextcloud and provide the information it requires to work via API.

## Current status

BETA: We are still doing changes. But mainly works. 
We use for IFC model viewing. 

## Installation

Place this app in **nextcloud/apps/**

This app might require an additional manual step to work. 3D files may lack a proper mimetype and thus not recognized by this app. To fix the mimetypes, setup [mimetype mapping](https://docs.nextcloud.com/server/stable/admin_manual/configuration_mimetypes/index.html#mimetype-mapping) for your instance and add these lines to the array in `config/mimetypemapping.json`:

```bash
"ifc": ["application/x-step"],
"dae": ["model/vnd.collada+xml"],
"gltf": ["model/gltf-binary", "model/gltf+json"],
"xkt": ["model/xkt-binary"]
```

**Please note**: If you do not already have a `confog/mimetypemapping.json`, make sure to wrap these lines in an JSON Object (`{}`).
```bash
{
"ifc": ["application/x-step"],
"dae": ["model/vnd.collada+xml"],
"gltf": ["model/gltf-binary", "model/gltf+json"],
"xkt": ["model/xkt-binary"]
}
```

Run the mimetype update:
```bash
sudo -u www-data ./occ maintenance:mimetype:update-js
sudo -u www-data ./occ maintenance:mimetype:update-db
```

command and (re-)upload your 3d files.

## Install converters

The application looks for converters at /usr/local/bin

Currently this is a three step process, as described here [https://xeokit.github.io/xeokit-sdk/examples/models/gltf/schependomlaan/]

### Install IFC converter
We use to convert from gltf to xkt internal model. 

Install Ifc converter from: [http://ifcopenshell.org/ifcconvert]

Basically get the zip and decompress.

### Install COLLADA converter

Install COLLADA converter from khronos group: [https://github.com/KhronosGroup/COLLADA2GLTF]

In this case you can go to releases and grab latest one.

### Install XKT converter
Install from: [https://github.com/xeokit/xeokit-gltf-to-xkt]

But basically you can run:
```bash
npm i @xeokit/xeokit-gltf-to-xkt -g
```
It will install the tool globally just at the correct place.


At the end you will have something like this:
```
-rwxr-xr-x 1 root root 14378464 Mar 26 23:45 COLLADA2GLTF-bin
-rwxr-xr-x 1 root root 36519216 Mar 26 23:46 IfcConvert
-rwxr-xr-x 1 root root  1969526 Mar 24 11:27 composer
lrwxrwxrwx 1 root root       58 Mar 26 23:42 gltf2xkt -> ../lib/node_modules/@xeokit/xeokit-gltf-to-xkt/gltf2xkt.js
```


## Building the app

**Please note**: Currently, all required files are bundled, no need to build. But this is for testing stage only. In a later stage I plan to support the `make` build process below.

The app can be built by using the provided Makefile by running:

    make

This requires the following things to be present:
* make
* which
* tar: for building the archive
* curl: used if phpunit and composer are not installed to fetch them from the web
* npm: for building and testing everything JS, only required if a package.json is placed inside the **js/** folder

The make command will install or update Composer dependencies if a composer.json is present and also **npm run build** if a package.json is present in the **js/** folder. The npm **build** script should use local paths for build systems and package managers, so people that simply want to build the app won't need to install npm libraries globally, e.g.:

**package.json**:
```json
"scripts": {
    "test": "node node_modules/gulp-cli/bin/gulp.js karma",
    "prebuild": "npm install && node_modules/bower/bin/bower install && node_modules/bower/bin/bower update",
    "build": "node node_modules/gulp-cli/bin/gulp.js"
}
```

## Publish to App Store

First get an account for the [App Store](http://apps.nextcloud.com/) then run:

    make && make appstore

The archive is located in build/artifacts/appstore and can then be uploaded to the App Store.

## Running tests
You can use the provided Makefile to run all tests by using:

    make test

This will run the PHP unit and integration tests and if a package.json is present in the **js/** folder will execute **npm run test**

Of course you can also install [PHPUnit](http://phpunit.de/getting-started.html) and use the configurations directly:

    phpunit -c phpunit.xml

or:

    phpunit -c phpunit.integration.xml

for integration tests
