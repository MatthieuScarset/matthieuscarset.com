# Makefile for matthieuscarset.com

# Load .env file if it exists
ifneq (,$(wildcard .env))
		include .env
		export
endif

.DEFAULT_GOAL := default

DRUSH := ./vendor/bin/drush
GRUMPHP := ./vendor/bin/grumphp

## # Global commands
## -----------------
default:
	@make lint

help:	## Show this help.
	@sed -ne '/@sed/!s/## //p' $(MAKEFILE_LIST)

init:	## Install composer dependencies
	@composer install -o

.PHONY: drush
drush:  ## Run a Drush command (e.g. make drush ARGS="uli")
	@bash -c '. ./.env && $(DRUSH) $(ARGS)'

install:  ## Install the site
	@make drush ARGS="si minimal -y"

clean:	## Delete temporary files, cache, and build artifacts
	@rm -rf vendor
	@rm -rf web/core
	@rm -rf web/modules/contrib
	@rm -rf web/themes/contrib
	@rm -rf dist/*
	@rm -rf dist/**

.PHONY: grumphp
grumphp:  ## Run a GrumPHP command (e.g. make grumphp ARGS="run --tasks=phpcs")
	@bash -c '$(GRUMPHP) $(ARGS)'

lint:	## Run GrumPHP tasks
	@make grumphp ARGS="run"
