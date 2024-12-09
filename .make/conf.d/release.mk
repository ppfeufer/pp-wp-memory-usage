# Prepare a new release
.PHONY: prepare-release
prepare-release: pot
	@echo ""
	@echo "Preparing a release"
	@echo "===================="
	@read -p "New Version Number: " new_version; \
	sed -i "/ * @version /c\ * @version $$new_version" $(plugin_file); \
	sed -i "/ * Version: /c\ * Version: $$new_version" $(plugin_file); \
	echo "Updated version in $(TEXT_BOLD)$(plugin_file)$(TEXT_BOLD_END)"; \
	sed -i "/\"Project-Id-Version: $(plugin_name) /c\\\"Project-Id-Version: $(plugin_name) $$new_version\\\n\"" $(plugin_translation_template); \
	echo "Please update the changelog in $(TEXT_BOLD)CHANGELOG.md$(TEXT_BOLD_END)"

# Create a new release archive
.PHONY: release-archive
release-archive:
	@echo "Creating a new release archive …"
	@rm -f $(plugin_slug).zip
	@rm -rf $(plugin_slug)/
	@rsync \
		-ax \
		--exclude-from=.make/rsync-exclude.lst \
		. \
		$(plugin_slug)/
	@zip \
		-r \
		$(plugin_slug).zip \
		$(plugin_slug)/
	@rm -rf $(plugin_slug)/

# Help message for the Release commands
.PHONY: help
help::
	@echo "  $(TEXT_UNDERLINE)Release:$(TEXT_UNDERLINE_END)"
	@echo "    prepare-release           Prepare a new release."
	@echo "    release-archive           Create a release archive."
	@echo "                              The release archive ($(plugin_slug).zip) will be created in the root"
	@echo "                              directory of the plugin."
	@echo ""
