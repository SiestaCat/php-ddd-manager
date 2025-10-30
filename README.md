# DDD Manager

## Docker Usage

### Build the image
```bash
docker build -t ddd-manager81 .
```

### Run PHPUnit tests
```bash
docker run --rm -it ddd-manager81 ./vendor/bin/phpunit
```