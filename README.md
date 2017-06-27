# dl2/slim-controller: Action Controller for Slim Framework 3.8

Implement Action Controllers for use with [Slim 3.8](https://www.slimframework.com).

## Installation

It's recommended that you use [composer](https://getcomposer.org/) to
install **dl2/slim-controller**.

```bash
$ composer require dl2/slim-controller
```

For new projects, you can use our [skeleton](https://github.com/dl2tech/slim-skeleton)
to quickly setup and start working on a new Slim Framework 3 application:

> Replace `[my-app-name]` with the desired directory name for your new application.

```
$ composer create-project dl2/slim-skeleton [my-app-name]
```

After the donwload is complete, you'll want to:
  - Point your virtual host document root to the `public/` directory.
  - Ensure `data/logs/` and `data/cache/` is writeable by your webserver.

## Usage

There is a complete application inside the [example](example) folder.

## Tests

Just run `composer test`.

## Versioning

Follows the [Slim versioning](https://github.com/slimphp/Slim/releases/latest).

## License

The Slim Controller extension is licensed under the MIT license.
See [License File](LICENSE.md) for more information.

## Thanks

#### Slim Framework

Of course, this wouldn't exists.

#### Data in [Example](example/controllers/data.json)

All randomly generated data for the example were picked from
the [Random User Generator API](https://randomuser.me).
