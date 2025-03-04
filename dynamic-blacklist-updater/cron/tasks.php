<?php

namespace DynamicBlacklistUpdater\Cron;

add_action('dbu_update_blacklist_event', __NAMESPACE__ . '\dbu_fetch_blacklist');
