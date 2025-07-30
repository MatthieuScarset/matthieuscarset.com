# Makefile for matthieuscarset.com

# Load .env file if it exists
ifneq (,$(wildcard .env))
		include .env
		export
endif

.DEFAULT_GOAL := default

DRUSH := ./vendor/bin/drush
GRUMPHP := ./vendor/bin/grumphp

## Global commands
## -----------------
default:
	@make lint

help:	## Show this help.
	@sed -ne '/@sed/!s/## //p' $(MAKEFILE_LIST)

clean:	## Delete temporary files, cache, and build artifacts
	@rm -rf vendor
	@rm -rf web/core
	@rm -rf web/modules/contrib
	@rm -rf web/themes/contrib
	@rm -rf dist/*
	@rm -rf dist/**

lint:	## Run GrumPHP tasks
	@make grumphp ARGS="run"

## Drupal
## ---------

start:	## Init local server
	@make drush ARGS="runserver"

install: ## Init Drupal
	@composer install -o
	@make drush ARGS="si --existing-config -y"
	@make drush ARGS="tome:import -y"
	@make drush ARGS="upwd admin admin"
	@make drush ARGS="uli"

export:  ## Export static site
	@make dev_off
	@make drush ARGS="cr"
	@make drush ARGS="tome:export -y"
	@make drush ARGS="tome:static -y"
	@make dev_on
	@make drush ARGS="cr"

dev_on:	## Enable development mode
	@bash -c "sed -i.bak 's|^ENV=.*|ENV=dev|' .env"
	@bash -c "sed -i.bak 's|^DRUSH_OPTIONS_URI=.*|DRUSH_OPTIONS_URI=http://localhost:8888|' .env"

dev_off:	## Disable development mode
	@bash -c "sed -i.bak 's|^ENV=.*|ENV=prod|' .env"
	@bash -c "sed -i.bak 's|^DRUSH_OPTIONS_URI=.*|DRUSH_OPTIONS_URI=https://matthieuscarset.com|' .env"

## Aliases
## ---------

.PHONY: drush
drush:  ## Run a Drush command with env vars (e.g. make drush ARGS="cr" ENV=prod)
	@bash -c 'ENV=$(ENV) . ./.env && $(DRUSH) $(ARGS)'

.PHONY: grumphp
grumphp:  ## Run a GrumPHP command (e.g. make grumphp ARGS="run --tasks=phpcs")
	@bash -c '$(GRUMPHP) $(ARGS)'
