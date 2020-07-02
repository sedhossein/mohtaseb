install:
	docker run \
	--rm --interactive --tty \
	--volume $(PWD)/karim:/app/karim \
	--volume $(PWD)/jib:/app/jib \
	composer:2 \
		sh -c \
			"composer install --ignore-platform-reqs --working-dir=/app/karim && \
			 composer install --ignore-platform-reqs --working-dir=/app/jib"

up: install
	docker-compose up

test: test-karim test-jib

test-karim:
	docker-compose run --no-deps karim php /app/vendor/bin/phpunit

test-jib:
	docker-compose run --no-deps jib php /app/vendor/bin/phpunit
