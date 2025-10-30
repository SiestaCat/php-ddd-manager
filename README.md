# DDD Manager

## Docker Usage

### Build the image
```bash
docker build -t ddd-manager .
```

### Run PHPUnit tests
```bash
docker run --rm -it ddd-manager ./vendor/bin/phpunit
```