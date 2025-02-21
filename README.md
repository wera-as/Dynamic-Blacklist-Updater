# Dynamic Blacklist Updater

## Description

Dynamic Blacklist Updater is a WordPress plugin that automatically fetches a blacklist from a remote source and updates both the "Disallowed Comment Keys" and "Comment Moderation" settings. It also tracks how many times the blacklist stops comments and displays detailed information such as the last updated time and the total number of blacklist entries. The plugin integrates with popular form plugins (WPForms, Formidable Forms, Contact Form 7, and Gravity Forms) to validate submissions based on the blacklist.

## Features

- **Automatic Updates:** Fetches the blacklist from a primary URL with a fallback option.
- **WP Cron Scheduling:** Updates the blacklist automatically at configurable intervals.
- **Discussion Settings Sync:** Updates the "Disallowed Comment Keys" and "Comment Moderation" settings.
- **Hit Counter:** Tracks the number of times the blacklist stops comments.
- **Blacklist Entry Count:** Displays the total number of terms in the blacklist (updated only when the blacklist is refreshed).
- **Form Plugin Integration:** Provides validation for WPForms, Formidable Forms, Contact Form 7, and Gravity Forms (loaded only if their plugins are active).
- **Admin UI:** A modern, Bootstrap-inspired interface with detailed status information and customizable settings.
- **Configurable URLs:** Allows you to overwrite and reset the primary and fallback blacklist URLs.

## Requirements

- WordPress 5.0+
- PHP 7.0+
- *Optional:* WPForms, Formidable Forms, Contact Form 7, Gravity Forms

## Installation

1. **Download or Clone:**  
   Download the plugin files or clone this repository.

2. **Upload to WordPress:**  
   Upload the `dynamic-blacklist-updater` folder to your `/wp-content/plugins/` directory.

3. **Activate the Plugin:**  
   Go to the WordPress admin dashboard, navigate to **Plugins**, and activate **Dynamic Blacklist Updater**.

4. **Configure Settings:**  
   Access the settings page via the "Blacklist Updater" menu item (or under Settings if configured) and customize the update interval, menu location, and blacklist URLs as needed.

## Usage

Once activated, the plugin will automatically schedule updates to fetch the blacklist. You can also manually update the blacklist or reset the URLs through the settings page. The plugin:
- Updates the WordPress Discussion settings with the latest blacklist.
- Increments a counter each time the blacklist stops a comment.
- Displays information about the last update and the total number of terms.

For form submissions, if any content matches a blacklisted term, the submission will be blocked, and the hit counter will be incremented.

## Customization

- **Update Interval:** Choose from 15 minutes, 6 hours, daily, or weekly.
- **Menu Location:** Decide whether the plugin settings appear as a top‑level menu or under the Settings menu.
- **Blacklist URLs:** Edit the primary and fallback URLs directly on the settings page. A "Reset Blacklist URLs to Default" button is available to revert to the default values.

## Contributing

Contributions, bug reports, and feature requests are welcome. Please open an issue or submit a pull request on GitHub.

## License

This project is licensed under the [GPL2 License](LICENSE).

