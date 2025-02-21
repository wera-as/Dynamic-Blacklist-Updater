# Dynamic Blacklist Updater

## Description

Dynamic Blacklist Updater is a WordPress plugin that automatically fetches a blacklist from a remote source and updates the "Disallowed Comment Keys" and "Comment Moderation" settings. It tracks how many times the blacklist stops comments and displays detailed status information, including the last updated time and the total number of blacklist entries. The plugin integrates with popular form plugins like WPForms, Formidable Forms, Contact Form 7, and Gravity Forms.

## Badges

[![License: GPL v2](https://img.shields.io/badge/License-GPL_v2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)  
[![WordPress Plugin](https://img.shields.io/wordpress/plugin/v/dynamic-blacklist-updater.svg?style=flat)](https://wordpress.org/plugins/dynamic-blacklist-updater/)  
[![Build Status](https://img.shields.io/travis/yourusername/dynamic-blacklist-updater.svg?style=flat)](https://travis-ci.org/yourusername/dynamic-blacklist-updater)

## Features

- **Automatic Updates:** The plugin fetches the blacklist automatically at configurable intervals.
- **Discussion Settings Sync:** Updates both "Disallowed Comment Keys" and "Comment Moderation" settings.
- **Hit Counter:** Tracks the number of times the blacklist stops comments.
- **Blacklist Entry Count:** Displays the total number of terms in the blacklist (updated only when refreshed).
- **Form Plugin Integration:** Supports WPForms, Formidable Forms, Contact Form 7, and Gravity Forms.
- **Modern Admin UI:** Offers a clean, Bootstrap-inspired interface with detailed status information.
- **Configurable URLs:** Allows you to overwrite and reset the primary and fallback blacklist URLs.
- **Customizable Scheduling:** Choose from 15 minutes, 6 hours, daily, or weekly updates.

## Requirements

- WordPress 5.0+
- PHP 7.0+
- *Optional:* WPForms, Formidable Forms, Contact Form 7, Gravity Forms

## Installation

1. **Download or Clone the Repository:**  
   Clone or download the repository from GitHub.

2. **Upload to WordPress:**  
   Upload the `dynamic-blacklist-updater` folder to your `/wp-content/plugins/` directory.

3. **Activate the Plugin:**  
   In your WordPress admin dashboard, navigate to **Plugins** and activate **Dynamic Blacklist Updater**.

4. **Configure Settings:**  
   Access the settings page via the "Blacklist Updater" menu (or under Settings if configured) and customize the update interval, menu location, and blacklist URLs as needed.

## Usage

Once activated, the plugin will automatically schedule and fetch the latest blacklist. It:
- Updates the discussion settings with the current blacklist.
- Increments a counter each time a comment is blocked.
- Displays detailed status information (last update time, hit count, and total number of blacklist entries).
- Validates submissions from popular form plugins when active.

## Contributing

Contributions, bug reports, and feature requests are welcome!  
Please open an issue or submit a pull request on [GitHub](https://github.com/yourusername/dynamic-blacklist-updater).

## License

This project is licensed under the [GPL2 License](LICENSE).
