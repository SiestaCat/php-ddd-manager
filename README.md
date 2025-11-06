# DDD Manager

This is a personal and experimental project. Use with caution.

## Definition

### BoundedContext

Folder that have .dddcontext file

BoundedContext must be in `%project_dir%/src/BoundedContexts`

For example:

- `src/BoundedContexts/Auth/User/.dddcontext`
- `src/BoundedContexts/Auth/UserHistory/.dddcontext`
- `src/BoundedContexts/Post/.dddcontext`

## Doctrine ORM Structure Example

**Mapping**

`src/BoundedContexts/Auth/User/Infrastructure/Framework/Doctrine/Orm/Mapping/User/User.orm.xml`

`User.php` entity must exists in `src/BoundedContexts/Auth/User/Domain/Entity`

**Migrations**

Executing `php bin/console doctrine:migrations:diff:ddd` all migrations related to the bounded contexts entities will be generated on:

`src/BoundedContexts/Auth/User/Infrastructure/Framework/Doctrine/Orm/Migrations`

## Symfony Structure Example

**Bundles**

`src/BoundedContexts/Auth/User/Infrastructure/Framework/Symfony/config/bundles.php`

**Services and packages**

`src/BoundedContexts/Auth/User/Infrastructure/Framework/Symfony/config/services.yaml`
`src/BoundedContexts/Auth/User/Infrastructure/Framework/Symfony/config/services.php`
`src/BoundedContexts/Auth/User/Infrastructure/Framework/Symfony/config/services_dev.yaml`
`src/BoundedContexts/Auth/User/Infrastructure/Framework/Symfony/config/services_prod.yaml`
`src/BoundedContexts/Auth/User/Infrastructure/Framework/Symfony/config/services_dev.php`
`src/BoundedContexts/Auth/User/Infrastructure/Framework/Symfony/config/services_prod.php`
`src/BoundedContexts/Auth/User/Infrastructure/Framework/Symfony/config/services/my_services.yaml`
`src/BoundedContexts/Auth/User/Infrastructure/Framework/Symfony/config/services/my_services.php`
`src/BoundedContexts/Auth/User/Infrastructure/Framework/Symfony/config/services/packages/package_name.yaml`
`src/BoundedContexts/Auth/User/Infrastructure/Framework/Symfony/config/services/packages/package_name.php`
`src/BoundedContexts/Auth/User/Infrastructure/Framework/Symfony/config/services/packages/dev/package_name.yaml`
`src/BoundedContexts/Auth/User/Infrastructure/Framework/Symfony/config/services/packages/dev/package_name.php`
`src/BoundedContexts/Auth/User/Infrastructure/Framework/Symfony/config/services/packages/prod/package_name.yaml`
`src/BoundedContexts/Auth/User/Infrastructure/Framework/Symfony/config/services/packages/prod/package_name.php`

**Twig templates**

Templates can be imported with `@` as a prefix with snake case separated by dot of bounded context name, for example: `@auth.user/file.html.twig`

`src/BoundedContexts/Auth/User/Infrastructure/Framework/Symfony/templates`

**Translations**

*Only 1 messages translation domain can exists across all bounded contexts*

`src/BoundedContexts/Auth/User/Infrastructure/Framework/Symfony/translations/messages.yaml`

*If the domain is not message, must be snake case separated by dot of bounded context name, see command debug:ddd:bounded-contexts*

`src/BoundedContexts/Auth/User/Infrastructure/Framework/Symfony/translations/auth.user.yaml`

Allowed files extensions, otherwise the file will be ignored

- php
- yml
- yaml
- xlf
- xliff
- po
- mo
- ts
- csv
- res
- dat
- ini
- json

## Usage

```bash
composer require siestacat/ddd-manager
```

Copy files from `recipes` to project dir

## Docker Usage

### Build the image
```bash
docker build -t ddd-manager .
```

### Run PHPUnit tests
```bash
docker run --rm -it ddd-manager ./vendor/bin/phpunit
```