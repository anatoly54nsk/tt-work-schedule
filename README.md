# TT-Work-Schedule Api

Test task api

## Requirements
* PHP 7.3+
* SQLite, SQLite_PDO extension for php

## Installation
Clone project from [github.com](https://github.com/)
```
git clone *project_repository*
```
Go to project directory
```
cd *project_directory*
```

Use the package manager [composer](https://getcomposer.org/) to install.

```
composer install
```

## Start local server

In project directory:
```
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
php bin/console doctrine:fixture:load -n
```

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://choosealicense.com/licenses/mit/)
