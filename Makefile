# Description: Makefile for the WordPress plugin WordPress Memory Usage

wp_cli = ./vendor/bin/wp
wp_path = /mnt/sda1/Development/PHP/Sources/WordPress

plugin_name = WordPress Memory Usage
plugin_slug = pp-wp-memory-usage

help:
	@echo "Usage: make [command]"
	@echo ""
	@echo "Commands:"
	@echo "  activate           Activate the plugin"
	@echo "  deactivate         Deactivate the plugin"
	@echo "  make-pot           Create the plugin .pot file"
	@echo "  clear-transient    Clear all transient caches"

activate:
	$(wp_cli) plugin activate \
		$(plugin_slug) \
		--path=$(wp_path)

deactivate:
	$(wp_cli) plugin deactivate \
		$(plugin_slug) \
		--path=$(wp_path)


pot:
	$(wp_cli) i18n make-pot \
		. \
		l10n/$(plugin_slug).pot \
		--slug=$(plugin_slug) \
		--domain=$(plugin_slug) \
		--include="/"

clear-transient:
	$(wp_cli) transient delete \
		--all \
		--path=$(wp_path)
