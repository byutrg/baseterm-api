# BaseTerm API

A Symfony project created on April 4, 2016.

BaseTerm API is an open-source Symfony based API intended to be used by [BaseTerm](https://github.com/byutrg/baseterm). BaseTerm API stores all termbase (terminology database) related information used by BaseTerm. The API only supports TBX-Basic files for import/export.

This API uses the same routes as the Python based [CRITI API](https://github.com/LexTerm/CRITI/tree/master/server) and either API can be used by BaseTerm.


## Installation

BaseTerm API uses Apache, PHP5, and MySQL (it has been tested using Linux, Windows (Wampserver), and Mac(MAMP)).

### Clone Repository

Clone this git repository to the desired directory on your Apache webserver setup.

```
git clone https://github.com/byutrg/baseterm-api.git
```

Enter the directory:

```
cd baseterm-api
```

### Setup MySQL Database and Install Symfony

You will need to create a database for BaseTerm API in MySQL along with a user and a password.

Now you must install [Composer](https://getcomposer.org/download/).  You can download it from the website or do the following:

```
curl -sS https://getcomposer.org/installer | php -- --filename=composer
```

Once you have composer in the main directory, you must run it:

```
php composer install
```

This should install Symfony and all other requirements for BaseTerm API.  You will need to provide information about your MySQL instance.  If you do not know yet, these settings can changed later in the app/config/parameters.yml file.

### Prepare the site

From the main directory run the following:

#### Update the database
```
php app/console doctrine:schema:update --force
```

#### Publish

##### Publish to the Development environment
```
php app/console assetic:dump --env=dev
```

##### Publish to the Production environment
```
php app/console assetic:dump --env=prod
```

#### Clear Cache

Note, you may need to fix permissions for the app/cache and app/logs folders after doing this:

```
php app/console cache:clear
```

## Usage

A TBX file may be manually imported into the BaseTerm API by visiting /import.html.  For example, if BaseTerm API is installed at localhost/basterm-api/web, then you can go to localhost/baseterm-api/web/import.html to manually import a file.

### Routes

Routes can be seen by using:
```
php app/console debug:router
```

Routes (Edited for clarity):

```
 termbase_collection      GET           /termbases
 termbase_post            POST          /termbases
 termbase_get             GET           /termbases/{termbase_id}
 termbase_put             PUT           /termbases/{termbase_id}
 termbase_delete          DELETE        /termbases/{termbase_id}
 termbase_export          GET           /termbases/{termbase_id}/export
 termbase_import          POST          /import
 termbase_import_form     GET           /import.html
 entry_collection         GET           /termbases/{termbase_id}/entries
 entry_post               POST          /termbases/{termbase_id}/entries
 entry_get                GET           /termbases/{termbase_id}/entries/{entry_id}
 entry_put                PUT           /termbases/{termbase_id}/entries/{entry_id}
 entry_delete             DELETE        /termbases/{termbase_id}/entries/{entry_id}
 person_collection        GET           /termbases/{termbase_id}/people
 person_post              POST          /termbases/{termbase_id}/people
 person_get               GET           /termbases/{termbase_id}/people/{person_id}
 person_put               PUT           /termbases/{termbase_id}/people/{person_id}
 person_delete            DELETE        /termbases/{termbase_id}/people/{person_id}
 ```

## Credits

Special thanks to the Symfony team and all of the developers who have created libraries for it!

## License

Copyright © (2015-2017) BYU TRG & LTAC Global & CRITI

This software is distributed under the Eclipse Public License.  See the LICENSE file that accompanies this source code for the full license information.