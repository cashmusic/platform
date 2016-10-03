#### releasinator config ####
configatron.product_name = "PayPal-PHP-SDK"

# List of items to confirm from the person releasing.  Required, but empty list is ok.
configatron.prerelease_checklist_items = [
  "Sanity check the master branch."
]

def validate_version_match()
  if constant_version() != @current_release.version
    Printer.fail("lib/PayPal/Core/PayPalConstants.php version #{constant_version} does not match changelog version #{@current_release.version}.")
    abort()
  end
  Printer.success("Plugin.xml version #{constant_version} matches latest changelog version.")
end

def validate_tests()
   CommandProcessor.command("vendor/bin/phpunit", live_output=true)
end

configatron.custom_validation_methods = [
  method(:validate_version_match),
  method(:validate_tests)
]

# there are no separate build steps for PayPal-PHP-SDK, so it is just empty method
def build_method
end

# The command that builds the sdk.  Required.
configatron.build_method = method(:build_method)

# Creating and pushing a tag will automatically create a release, so it is just empty method
def publish_to_package_manager(version)
end

# The method that publishes the sdk to the package manager.  Required.
configatron.publish_to_package_manager_method = method(:publish_to_package_manager)

def create_downloadable_zip(version)
    sleep(30)
    CommandProcessor.command("rm -rf temp; mkdir temp; cd temp; composer clear-cache; composer require 'paypal/rest-api-sdk-php:#{version}'", live_output=true)
    CommandProcessor.command("cd temp; mv vendor PayPal-PHP-SDK", live_output=true)
    CommandProcessor.command("cd temp; zip -r PayPal-PHP-SDK-#{version}.zip PayPal-PHP-SDK", live_output=true)
end

def add_to_release(version)
    sleep(5)
    Publisher.new(@releasinator_config).upload_asset(GitUtil.repo_url, @current_release, "temp/PayPal-PHP-SDK-#{version}.zip", "application/zip")
end

configatron.post_push_methods = [
    method(:create_downloadable_zip),
    method(:add_to_release)
]

def wait_for_package_manager(version)
end

# The method that waits for the package manager to be done.  Required
configatron.wait_for_package_manager_method = method(:wait_for_package_manager)

# Whether to publish the root repo to GitHub.  Required.
configatron.release_to_github = true

def constant_version()
  File.open("lib/PayPal/Core/PayPalConstants.php", 'r') do |f|
    f.each_line do |line|
      if line.match (/SDK_VERSION = \'\d+\.\d+\.\d+\'/)
        return line.strip.split('= ')[1].strip.split('\'')[1]
      end
    end
  end
end
